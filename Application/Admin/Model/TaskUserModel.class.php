<?php
namespace Admin\Model;

class TaskUserModel extends BaseModel{
	protected $tableName='integral_task_user';

	public function finishTastewine($idcode){
        $fileds = 'a.id,a.openid,a.hotel_id,task.integral';
        $where = array('a.idcode'=>$idcode,'a.status'=>1,'task.status'=>1,'task.flag'=>1,'task.task_type'=>28);
        $where['task.end_time'] = array('EGT',date('Y-m-d H:i:s'));
        $res = $this->alias('a')
            ->field($fileds)
            ->join('savor_integral_task task on a.task_id=task.id','left')
            ->where($where)
            ->find();
        if(!empty($res)){
            $task_user_id = $res['id'];
            $this->updateData(array('id'=>$task_user_id),array('status'=>3));
            $now_integral = $res['integral'];
            $openid = $res['openid'];

            $where = array('hotel_id'=>$res['hotel_id'],'status'=>1);
            $field_merchant = 'id as merchant_id,is_integral,is_shareprofit,shareprofit_config';
            $m_merchant = new \Admin\Model\MerchantModel();
            $res_merchant = $m_merchant->getRow($field_merchant,$where,'id desc');
            if($res_merchant['is_integral']==1){
                $integralrecord_openid = $openid;
                $m_userintegral = new \Admin\Model\UserIntegralModel();
                $res_integral = $m_userintegral->getInfo(array('openid'=>$openid));
                if(!empty($res_integral)){
                    $userintegral = $res_integral['integral']+$now_integral;
                    $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                }else{
                    $m_userintegral->add(array('openid'=>$openid,'integral'=>$now_integral));
                }
            }else{
                $integralrecord_openid = $res['hotel_id'];
                $where = array('id'=>$res_merchant['merchant_id']);
                $m_merchant->where($where)->setInc('integral',$now_integral);
            }
            $m_hotel = new \Admin\Model\HotelModel();
            $field = 'hotel.id as hotel_id,hotel.name as hotel_name,hotel.hotel_box_type,area.id as area_id,area.region_name as area_name';
            $where = array('hotel.id'=>$res['hotel_id']);
            $res_hotel = $m_hotel->getHotelById($field,$where);

            $m_integralrecord = new \Admin\Model\UserIntegralrecordModel();
            $integralrecord_data = array('openid'=>$integralrecord_openid,'area_id'=>$res_hotel['area_id'],'area_name'=>$res_hotel['area_name'],
                'hotel_id'=>$res_hotel['hotel_id'],'hotel_name'=>$res_hotel['hotel_name'],'hotel_box_type'=>$res_hotel['hotel_box_type'],
                'task_id'=>$task_user_id,'integral'=>$now_integral,'jdorder_id'=>$idcode,'content'=>1,'type'=>23,'integral_time'=>date('Y-m-d H:i:s'));
            $m_integralrecord->add($integralrecord_data);
        }
        return true;
    }
	
}