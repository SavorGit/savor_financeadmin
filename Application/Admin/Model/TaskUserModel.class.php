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

    public function finishStockCheckTask($openid,$stockcheck_id,$task_user_id){
        $now_integral = 0;
        $task_id = 0;
        $where = array('a.id'=>$task_user_id,'a.openid'=>$openid,'a.status'=>1);
        $fields = "a.id as task_user_id,task.id task_id,task.task_info,task.integral";
        $res_utask = $this->alias('a')
            ->field($fields)
            ->join('savor_integral_task task on a.task_id=task.id','left')
            ->where($where)
            ->order('a.id desc')
            ->find();
        if(!empty($res_utask)){
            $task_id = $res_utask['task_id'];
            $now_integral = intval($res_utask['integral']);
        }

        $where = array('a.openid'=>$openid,'a.status'=>1,'merchant.status'=>1);
        $m_staff = new \Admin\Model\StaffModel();
        $res_staff = $m_staff->getMerchantStaff('a.level,merchant.id as merchant_id,merchant.is_integral,merchant.hotel_id,merchant.is_shareprofit,merchant.shareprofit_config',$where);
        if(!empty($res_staff) && $now_integral>0){
            $admin_integral = 0;
            if($res_staff[0]['is_integral']==1){
                $integralrecord_openid = $openid;
                if($task_user_id>0){
                    $this->where(array('id'=>$task_user_id))->setInc('integral',$now_integral);
                }
                if($res_staff[0]['is_shareprofit']==1 && $res_staff[0]['level']==2){
                    $shareprofit_config = json_decode($res_staff[0]['shareprofit_config'],true);
                    if(!empty($shareprofit_config['jspd'])){
                        $staff_integral = ($shareprofit_config['jspd'][1]/100)*$now_integral;
                        if($staff_integral>1){
                            $staff_integral = round($staff_integral);
                        }else{
                            $staff_integral = 1;
                        }
                        $admin_integral = $now_integral - $staff_integral;
                        $now_integral = $staff_integral;
                    }
                }
            }else{
                $integralrecord_openid = $res_staff[0]['hotel_id'];
            }

            $m_hotel = new \Admin\Model\HotelModel();
            $field = 'hotel.id as hotel_id,hotel.name as hotel_name,hotel.hotel_box_type,area.id as area_id,area.region_name as area_name';
            $where = array('hotel.id'=>$res_staff[0]['hotel_id']);
            $res_hotel = $m_hotel->getHotelById($field,$where);

            $m_integralrecord = new \Admin\Model\UserIntegralrecordModel();
            if($admin_integral>0){
                $adminwhere = array('merchant_id'=>$res_staff[0]['merchant_id'],'level'=>1,'status'=>1);
                $res_admin_staff = $m_staff->getAll('id,openid',$adminwhere,0,1,'id desc');
                if(!empty($res_admin_staff)){
                    $admin_openid = $res_admin_staff[0]['openid'];
                    $integralrecord_data = array('openid'=>$admin_openid,'area_id'=>$res_hotel['area_id'],'area_name'=>$res_hotel['area_name'],
                        'hotel_id'=>$res_staff[0]['hotel_id'],'hotel_name'=>$res_hotel['hotel_name'],'hotel_box_type'=>$res_hotel['hotel_box_type'],
                        'task_id'=>$task_id,'integral'=>$admin_integral,'content'=>1,'jdorder_id'=>$stockcheck_id,'status'=>1,'type'=>24,'source'=>4,'integral_time'=>date('Y-m-d H:i:s'));
                    $m_integralrecord->add($integralrecord_data);
                }
            }

            $integralrecord_data = array('openid'=>$integralrecord_openid,'area_id'=>$res_hotel['area_id'],'area_name'=>$res_hotel['area_name'],
                'hotel_id'=>$res_staff[0]['hotel_id'],'hotel_name'=>$res_hotel['hotel_name'],'hotel_box_type'=>$res_hotel['hotel_box_type'],
                'task_id'=>$task_id,'integral'=>$now_integral,'content'=>1,'jdorder_id'=>$stockcheck_id,'status'=>1,'type'=>24,'integral_time'=>date('Y-m-d H:i:s'));
            $m_integralrecord->add($integralrecord_data);
        }
        return $now_integral;
    }
}