<?php
namespace Admin\Controller;
class StockopenrewardController extends BaseController {

    public function datalist(){
        $goods_name = I('goods_name','','trim');
        $hotel_name = I('hotel_name','','trim');
        $idcode = I('idcode','','trim');
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $area_id = I('area_id',0,'intval');
        $recycle_status = I('recycle_status',0,'intval');

        if(empty($start_date) || empty($end_date)){
            $start_date = date('Y-m-d',strtotime("-14 day"));
            $end_date = date('Y-m-d');
        }
        $open_date = '2024-01-03';
        if($start_date<$open_date){
            $start_date = $open_date;
        }
        $stime = strtotime($start_date);
        $etime = strtotime($end_date);
        if($etime-$stime>31*86400){
            $this->output('请选择31天内的时间段', 'stockopenreward/datalist',2,0);
        }
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        $area_arr = array();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }

        $all_recycle_status = C('STOCK_RECYLE_STATUS');
        $where = array('a.type'=>7,'a.wo_status'=>2,'a.wo_reason_type'=>1);
        if($recycle_status){
            $where['a.recycle_status'] = $recycle_status;
        }
        if($area_id){
            $where['sale.area_id'] = $area_id;
        }
        if(!empty($goods_name)){
            $where['goods.name'] = array("like","%$goods_name%");
        }
        if(!empty($hotel_name)){
            $where['hotel.name'] = array("like","%$hotel_name%");
        }
        if(!empty($idcode)){
            $where['a.idcode'] = $idcode;
        }
        $now_start_time = date('Y-m-d 00:00:00',$stime);
        $now_end_time = date('Y-m-d 23:59:59',$etime);
        $where['a.add_time'] = array(array('egt',$now_start_time),array('elt',$now_end_time));

        $start = ($pageNum-1)*$size;
        $fields = 'a.id,a.idcode,a.vintner_code,a.out_time,a.recycle_img,a.recycle_status,a.reason,a.add_time,a.recycle_time,a.recycle_audit_time,goods.name as goods_name,
        a.is_open_reward,sale.area_id,hotel.id as hotel_id,hotel.name as hotel_name,su.remark as residenter_name,user.nickName as username,user.mobile';
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_list = $m_stock_record->getRecordSaleList($fields,$where, 'a.id desc', $start,$size);
        $data_list = $res_list['list'];
        $oss_host = get_oss_host();
        foreach ($data_list as $k=>$v){
            $recycle_img_arr = array();
            if(!empty($v['recycle_img'])){
                $arr_recycle_img = explode(',',$v['recycle_img']);
                foreach ($arr_recycle_img as $aiv){
                    $recycle_img_arr[]=$oss_host.$aiv;
                }
                $data_list[$k]['recycle_img_arr'] = $recycle_img_arr;
            }
            $data_list[$k]['recycle_status_str'] = $all_recycle_status[$v['recycle_status']];
            if($v['recycle_status']==3){
                $data_list[$k]['reason'] = '未上传开瓶资料';
            }
            $is_open_reward_str = '否';
            if($v['is_open_reward_str']==1){
                $is_open_reward_str = '是';
            }
            $data_list[$k]['is_open_reward_str'] = $is_open_reward_str;
            $data_list[$k]['area_name'] = $area_arr[$v['area_id']]['region_name'];
        }

        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('area',$area_arr);
        $this->assign('area_id',$area_id);
        $this->assign('idcode',$idcode);
        $this->assign('goods_name',$goods_name);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('datalist',$data_list);
        $this->assign('recycle_status',$recycle_status);
        $this->assign('all_recycle_status',$all_recycle_status);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function dataimport(){
        $cache_key = 'cronscript:finance:openrewardexcel';
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $res_data = $redis->get($cache_key);
        if(!empty($res_data)){
            $now_time = time();
            $diff_time = $now_time - $res_data;
            $errMsg = "你上传的文件正在处理中，处理时间{$diff_time}秒，请稍后。";
            $this->output($errMsg, 'stockopenreward/datalist', 3,0);
        }
        if(IS_POST){
            $upload = new \Think\Upload();
            $upload->exts = array('xls','xlsx','csv');
            $upload->maxSize = 2097152;
            $upload->rootPath = $this->imgup_path();
            $upload->savePath = '';
            $upload->saveName = time().mt_rand();
            $info = $upload->upload();
            if(!$info){
                $errMsg = $upload->getError();
                $this->output($errMsg, 'stockopenreward/dataimport', 2,0);
            }else{
                $userinfo = session('sysUserInfo');
                $sysuser_id = $userinfo['id'];
                $file_name = urlencode($info['fileup']['savepath'].$info['fileup']['savename']);
                $shell = "/opt/install/php/bin/php /application_data/web/php/savor_financeadmin/cli.php dataexport/stockopenreward/dataimportscript/fname/$file_name/auid/$sysuser_id > /tmp/null &";
                system($shell);
                $now_time = time();
                $redis->set($cache_key,$now_time,3600);
                $this->output('导入成功,开始处理审核数据', 'stockopenreward/datalist');
            }
        }else{
            $this->display();
        }

    }

    public function resetrecyclestatus(){
        $stock_record_id = I('get.stock_record_id',0,'intval');

        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_record = $m_stock_record->getInfo(array('id'=>$stock_record_id));
        if(!in_array($res_record['recycle_status'],array(3,5))){
            $this->output('状态错误,不能重置', 'stockopenreward/datalist',2,0);
        }
        $userinfo = session('sysUserInfo');
        $sysuser_id = $userinfo['id'];
        $up_record = array('recycle_status'=>1,'recycle_time'=>'0000-00-00 00:00:00','recycle_img'=>'',
            'recycle_audit_user_id'=>$sysuser_id,'recycle_audit_time'=>date('Y-m-d H:i:s'));
        $m_stock_record->updateData(array('id'=>$stock_record_id),$up_record);

        $rwhere = array('jdorder_id'=>$stock_record_id,'type'=>25,'status'=>2);
        $m_integralrecord = new \Admin\Model\UserIntegralrecordModel();
        $res_recordinfo = $m_integralrecord->getAll('id,openid,integral,hotel_id',$rwhere,0,2,'id desc');
        if(!empty($res_recordinfo[0]['id'])){
            $del_record_ids = array();
            foreach ($res_recordinfo as $rv){
                $del_record_ids[] = $rv['id'];
            }
            $m_integralrecord->delData(array('id'=>array('in',$del_record_ids)));
        }

        $this->output('操作成功!', 'stockopenreward/datalist',2);
    }

    public function editrecycle(){
        $stock_record_id = I('stock_record_id',0,'intval');
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_record = $m_stock_record->getInfo(array('id'=>$stock_record_id));
        $m_integralrecord = new \Admin\Model\UserIntegralrecordModel();
        if(IS_POST){
            $recyclemedia_id = I('post.recyclemedia_id','');
            $total_integral = I('post.total_integral',0,'intval');
            $recycle_status = I('post.recycle_status',0,'intval');

            if($res_record['recycle_status']!=5){
                $this->output('状态错误,不能修改资料', 'stockopenreward/datalist',2,0);
            }
            $rwhere = array('jdorder_id'=>$stock_record_id,'type'=>25,'status'=>2);
            $res_recordinfo = $m_integralrecord->getAll('id,openid,integral,hotel_id',$rwhere,0,2,'id asc');
            $all_integral = 0;
            foreach ($res_recordinfo as $v){
                $all_integral+=intval($v['integral']);
            }
            if($all_integral==0){
                $this->output("开瓶奖励积分已发放,请勿重复操作", 'stockopenreward/datalist',2,0);
            }
            if($total_integral>$all_integral){
                $this->output("开瓶奖励积分不能超过{$all_integral}", 'stockopenreward/datalist',2,0);
            }
            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            $up_record = array('recycle_audit_user_id'=>$sysuser_id,'recycle_audit_time'=>date('Y-m-d H:i:s'),'recycle_status'=>$recycle_status);
            $m_integralrecord = new \Admin\Model\UserIntegralrecordModel();
            $m_merchant = new \Admin\Model\MerchantModel();
            $m_userintegral = new \Admin\Model\UserIntegralModel();
            if($recycle_status==2){
                if(count($res_recordinfo)==1){
                    $res_recordinfo[0]['integral'] = $total_integral;
                }else{
                    $percent_1 = round($res_recordinfo[0]['integral']/$all_integral,2);
                    $integral_1 = round($total_integral*$percent_1);
                    $integral_2 = $total_integral-$integral_1>0?$total_integral-$integral_1:1;

                    $res_recordinfo[0]['integral'] = $integral_1;
                    $res_recordinfo[1]['integral'] = $integral_2;
                }

                $where = array('hotel_id'=>$res_recordinfo[0]['hotel_id'],'status'=>1);
                $field_merchant = 'id as merchant_id,is_integral,is_shareprofit,shareprofit_config';
                $res_merchant = $m_merchant->getRow($field_merchant,$where,'id desc');
                $is_integral = $res_merchant['is_integral'];
                foreach ($res_recordinfo as $rv){
                    $record_id = $rv['id'];

                    $m_integralrecord->updateData(array('id'=>$record_id),array('status'=>1,'integral_time'=>date('Y-m-d H:i:s')));
                    $now_integral = $rv['integral'];
                    if($is_integral==1){
                        $res_integral = $m_userintegral->getInfo(array('openid'=>$rv['openid']));
                        if(!empty($res_integral)){
                            $userintegral = $res_integral['integral']+$now_integral;
                            $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                        }else{
                            $m_userintegral->add(array('openid'=>$rv['openid'],'integral'=>$now_integral));
                        }
                    }else{
                        $where = array('id'=>$res_merchant['merchant_id']);
                        $m_merchant->where($where)->setInc('integral',$now_integral);
                    }
                }

                $recycle_img = '';
                if(!empty($recyclemedia_id)){
                    $m_media = new \Admin\Model\MediaModel();
                    $recycle_imgs = array();
                    foreach ($recyclemedia_id as $v){
                        if(!empty($v)){
                            if(is_numeric($v)){
                                $res_m = $m_media->getMediaInfoById($v);
                                $img = $res_m['oss_path'];
                            }else{
                                $img = $v;
                            }
                            $recycle_imgs[]=$img;
                        }
                    }
                    $recycle_img = join(',',$recycle_imgs);
                    $up_record['edit_recycle_user_id'] = $sysuser_id;
                }
                $up_record['recycle_img'] = $recycle_img;
            }else{
                if(!empty($res_recordinfo[0]['id'])){
                    $del_record_ids = array();
                    foreach ($res_recordinfo as $rv){
                        $del_record_ids[] = $rv['id'];
                    }
                    $m_integralrecord->delData(array('id'=>array('in',$del_record_ids)));
                }
            }

            $m_stock_record->updateData(array('id'=>$stock_record_id),$up_record);
            $this->output('操作成功!', 'stockopenreward/datalist',2);
        }else{
            $recycle_imgs = array();
            $recycle_img_num = 3;
            if(!empty($res_record['recycle_img'])){
                $data_recycle_img = explode(',',$res_record['recycle_img']);
                $oss_host = get_oss_host();
                $img_addr = array();
                foreach ($data_recycle_img as $k=>$v){
                    if(!empty($v)){
                        $img_addr[$k+1] = array('media_id'=>$v,'oss_addr'=>$oss_host.$v);
                    }
                }
                for($i=1;$i<=$recycle_img_num;$i++){
                    $img_info = array('id'=>$i,'imgid'=>'recycleimg_id'.$i,'media_id'=>0);
                    if(isset($img_addr[$i])){
                        $img_info['media_id'] = $img_addr[$i]['media_id'];
                        $img_info['oss_addr'] = $img_addr[$i]['oss_addr'];
                    }
                    $recycle_imgs[] = $img_info;
                }
            }

            $rwhere = array('jdorder_id'=>$stock_record_id,'type'=>25,'status'=>2);
            $res_recordinfo = $m_integralrecord->getAll('sum(integral) as total_integral',$rwhere,0,1,'id desc');
            $total_integral = intval($res_recordinfo[0]['total_integral']);

            $this->assign('total_integral',$total_integral);
            $this->assign('recycle_imgs',$recycle_imgs);
            $this->assign('vinfo',$res_record);
            $this->display();
        }
    }
}