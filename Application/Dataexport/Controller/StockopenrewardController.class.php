<?php
namespace Dataexport\Controller;

class StockopenrewardController extends BaseController {

    public function datalist(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');

        $stime = strtotime($start_date);
        $etime = strtotime($end_date);
        $where = array('a.type'=>7,'a.wo_status'=>2,'a.wo_reason_type'=>1,'a.recycle_status'=>5);
        $now_start_time = date('Y-m-d 00:00:00',$stime);
        $now_end_time = date('Y-m-d 23:59:59',$etime);
        $where['a.add_time'] = array(array('egt',$now_start_time),array('elt',$now_end_time));

        $fields = 'a.id,a.idcode,a.vintner_code,a.out_time,a.recycle_img,a.add_time,
        hotel.id as hotel_id,hotel.name as hotel_name,su.remark as residenter_name,user.nickName as username,user.mobile';
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $data_list = $m_stock_record->getRecordSaleList($fields,$where, 'a.id desc');
        $oss_host = get_oss_host();
        foreach ($data_list as $k=>$v){
            if(!empty($v['vintner_code'])){
                $data_list[$k]['vintner_code'] = "'{$v['vintner_code']}";
            }
            $data_list[$k]['audit_status'] = '';
            $data_list[$k]['audit_reason'] = '';
            $recycle_img1 = $recycle_img2 = $recycle_img3 = '';
            if(!empty($v['recycle_img'])){
                $recycle_img_arr = array();
                $arr_recycle_img = explode(',',$v['recycle_img']);
                foreach ($arr_recycle_img as $aiv){
                    $recycle_img_arr[]=$oss_host.$aiv;
                }
                if(!empty($recycle_img_arr[0])) $recycle_img1 = $recycle_img_arr[0];
                if(!empty($recycle_img_arr[1])) $recycle_img2 = $recycle_img_arr[1];
                if(!empty($recycle_img_arr[2])) $recycle_img3 = $recycle_img_arr[2];
            }
            $data_list[$k]['recycle_img1'] = $recycle_img1;
            $data_list[$k]['recycle_img2'] = $recycle_img2;
            $data_list[$k]['recycle_img3'] = $recycle_img3;
        }

        $cell = array(
            array('id','核销ID'),
            array('idcode','唯一码'),
            array('audit_status','审核状态(审核通过/审核不通过)'),
            array('audit_reason','不通过原因'),
            array('vintner_code','物流码'),
            array('hotel_name','酒楼名称'),
            array('out_time','出库时间'),
            array('residenter_name','驻店人'),
            array('username','核销人'),
            array('recycle_img1','物料图片1'),
            array('recycle_img2','物料图片2'),
            array('recycle_img3','物料图片3'),
            array('add_time','核销时间'),
        );
        $filename = '开瓶奖励审核表';
        $this->exportToExcel($cell,$data_list,$filename,1);
    }

    public function dataimportscript(){
        $file_name = I('fname','');
        $sysuser_id = I('auid',0,'intval');
        if(empty($file_name)){
            echo 'file_name error';
            exit;
        }
        $file_name = urldecode($file_name);
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $m_userintegral = new \Admin\Model\UserIntegralModel();
        $m_integralrecord = new \Admin\Model\UserIntegralrecordModel();
        $m_merchant = new \Admin\Model\MerchantModel();

        $file_path = SITE_TP_PATH.'/Public/uploads/'.$file_name;
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");
        $inputFileType = \PHPExcel_IOFactory::identify($file_path);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($file_path);
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        for ($row = 2; $row <= $highestRow; $row++){
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);

            $stock_record_id = intval($rowData[0][0]);
            $audit_status_str = trim($rowData[0][2]);
            if(empty($audit_status_str)){
                continue;
            }
            $reason = $rowData[0][3];
            $up_record = array('recycle_audit_user_id'=>$sysuser_id,'recycle_audit_time'=>date('Y-m-d H:i:s'));
            if($audit_status_str=='审核通过'){
                //发放解冻积分 增加用户积分
                $rwhere = array('jdorder_id'=>$stock_record_id,'type'=>25,'status'=>2);
                $res_recordinfo = $m_integralrecord->getAll('id,openid,integral,hotel_id',$rwhere,0,1,'id desc');
                if(!empty($res_recordinfo[0]['id'])){
                    $where = array('hotel_id'=>$res_recordinfo[0]['hotel_id'],'status'=>1);
                    $field_merchant = 'id as merchant_id,is_integral,is_shareprofit,shareprofit_config';
                    $res_merchant = $m_merchant->getRow($field_merchant,$where,'id desc');
                    $is_integral = $res_merchant['is_integral'];

                    $m_integralrecord->updateData(array('id'=>$res_recordinfo[0]['id']),array('status'=>1,'integral_time'=>date('Y-m-d H:i:s')));
                    $now_integral = $res_recordinfo[0]['integral'];
                    if($is_integral==1){
                        $res_integral = $m_userintegral->getInfo(array('openid'=>$res_recordinfo[0]['openid']));
                        if(!empty($res_integral)){
                            $userintegral = $res_integral['integral']+$now_integral;
                            $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                        }else{
                            $m_userintegral->add(array('openid'=>$res_recordinfo[0]['openid'],'integral'=>$now_integral));
                        }
                    }else{
                        $where = array('id'=>$res_merchant['merchant_id']);
                        $m_merchant->where($where)->setInc('integral',$now_integral);
                    }
                    $up_record['recycle_status']=2;
                }
            }elseif($audit_status_str=='审核不通过'){
                $rwhere = array('jdorder_id'=>$stock_record_id,'type'=>25,'status'=>2);
                $res_recordinfo = $m_integralrecord->getAll('id,openid,integral,hotel_id',$rwhere,0,1,'id desc');
                if(!empty($res_recordinfo[0]['id'])){
                    $m_integralrecord->delData(array('id'=>$res_recordinfo[0]['id']));
                }
                $up_record['recycle_status']=6;
                if(!empty($reason)){
                    $up_record['reason']=$reason;
                }
            }
            $m_stock_record->updateData(array('id'=>$stock_record_id),$up_record);
        }
        $cache_key = 'cronscript:finance:openrewardexcel';
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $redis->remove($cache_key);
    }
}