<?php
namespace Dataexport\Controller;

class AwardhoteldataController extends BaseController {

    public function datalist(){
        $area_id = I('area_id',0,'intval');
        $status = I('status',0,'intval');
        $stat_date = I('stat_date','');

        if(empty($stat_date)){
            $now_stat_date = date('Ym');
        }else{
            $now_stat_date = date('Ym',strtotime($stat_date));
        }
        $all_status = C('ACTIVITY_AWARD_STATUS');
        $where = array('a.static_date'=>$now_stat_date);
        $where['a.hotel_id'] = array('not in',C('TEST_HOTEL'));
        if(!empty($hotel_name)){
            $where['a.hotel_name'] = array('like',"%$hotel_name%");
        }
        if($area_id){
            $where['a.area_id'] = $area_id;
        }
        if($status){
            $where['a.status'] = $status;
        }
        $fileds = 'a.*,user.nickName,user.name,user.idnumber,user.mobile';
        $m_awardhoteldata = new \Admin\Model\AwardHoteldataModel();
        $res_list = $m_awardhoteldata->getHotelDatas($fileds,$where,'',0,0);
        $data_list = array();
        if(!empty($res_list)){
            foreach ($res_list as $v){
                $user_name = !empty($v['name'])?$v['name']:$v['nickname'];
                $v['user_name'] = $user_name;
                $v['status_str'] = $all_status[$v['status']];
                $data_list[]=$v;
            }
        }

        $cell = array(
            array('area_name','城市'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('bdm_name','BDM'),
            array('bd_name','BD'),
            array('user_name','激励人姓名'),
            array('mobile','激励人手机号'),
            array('idnumber','激励人身份证号'),
            array('num','单瓶激励瓶数'),
            array('integral','单瓶激励积分'),
            array('step_num','阶梯激励瓶数'),
            array('step_integral','阶梯激励积分'),
            array('bill_day','账期'),
            array('overdue_money','超期欠款金额'),
            array('status_str','积分发放状态'),
            array('static_date','统计月份'),
        );
        $filename = '激励酒楼明细';
        $this->exportToExcel($cell,$data_list,$filename,1);
    }
}