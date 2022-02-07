<?php
namespace Dataexport\Controller;

class HotelController extends BaseController {
    
    public function datalist() {
    	$sign_start_time = I('sign_start_time','');
    	$sign_end_time = I('sign_end_time','');
    	$area_id = I('area_id',0,'intval');
    	$status = I('status',0,'intval');
    	$renew_templateid = I('renew_templateid',0,'intval');
    	$pay_templateid = I('pay_templateid',0,'intval');
    	$sign_user_id = I('sign_user_id',0,'intval');
    	$is_expire60day = I('is_expire60day',0,'intval');
        $where = array('type'=>10);
        if(!empty($sign_start_time) && !empty($sign_end_time)){
            $where['sign_time'] = array(array('egt',$sign_start_time),array('elt',$sign_end_time));
        }
        if($area_id){
            $where['area_id'] = $area_id;
        }
        if($status){
            $where['status'] = $status;
        }
        if($renew_templateid){
            $where['renew_templateid'] = $renew_templateid;
        }
        if($pay_templateid){
            $where['pay_templateids'] = array('like',"%,$pay_templateid,%");
        }
        if($sign_user_id){
            $where['sign_user_id'] = $sign_user_id;
        }
        if($is_expire60day){
            $expire_etime = date('Y-m-d',strtotime("+60 day"));
            $where['contract_etime'] = array('elt',$expire_etime);
        }
        $m_signuser = new \Admin\Model\SignuserModel();
        $result_signuser = $m_signuser->getDataList('id,uname as name',array('status'=>1),'id asc');
        $sign_users = array();
        foreach ($result_signuser as $v){
            $sign_users[$v['id']] = $v;
        }
        $m_contract  = new \Admin\Model\ContractModel();
        $fields = 'id,serial_number,name,sign_user_id,self_type,sign_time,contract_stime,contract_etime,status,oss_addr,type';
        $datalist = $m_contract->getDataList($fields,$where,'id desc');
        if(!empty($datalist)){
            $config_contract = C('FINACE_CONTRACT');
            $status_arr = $config_contract['contract_status'];
            $m_contracthotel = new \Admin\Model\ContracthotelModel();
            foreach ($datalist as $k=>$v){
                $datalist[$k]['sign_user'] = $sign_users[$v['sign_user_id']]['name'];
                $self_type_str = '主合同';
                if($v['self_type']==0){
                    $self_type_str='副合同';
                }
                $res_hotel_num = $m_contracthotel->getRow('count(id) as num',array('contract_id'=>$v['id']));
                $hotel_num = 0;
                if(!empty($res_hotel_num)){
                    $hotel_num = $res_hotel_num['num'];
                }
                $sign_time = '';
                if($v['sign_time']!='0000-00-00'){
                    $sign_time = $v['sign_time'];
                }
                $expire_time = '';
                if($v['contract_stime']!='0000-00-00' && $v['contract_etime']!='0000-00-00'){
                    $expire_time = $v['contract_stime'].'~'.$v['contract_etime'];
                }

                $datalist[$k]['self_type_str'] = $self_type_str;
                $datalist[$k]['status_str'] = $status_arr[$v['status']]['name'];
                $datalist[$k]['hotel_num'] = $hotel_num;
                $datalist[$k]['expire_time'] = $expire_time;
                $datalist[$k]['sign_time'] = $sign_time;
            }
        }

        $cell = array(
            array('id','ID'),
            array('serial_number','合同编号'),
            array('name','合同名称'),
            array('sign_user','签约人'),
            array('self_type_str','合同类型'),
            array('sign_time','签约时间'),
            array('expire_time','合同有效期'),
            array('status_str','合作状态'),
            array('hotel_num','关联酒楼'),
        );
        $filename = '酒楼合同列表';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }


}