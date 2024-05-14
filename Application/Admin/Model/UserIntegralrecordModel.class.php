<?php
namespace Admin\Model;
class UserIntegralrecordModel extends BaseModel{
	protected $tableName='smallapp_user_integralrecord';

    public function finishRecycle($stock_record_info,$integral_status=2){
        $stock_record_id = $stock_record_info['id'];

        $m_goodsconfig = new \Admin\Model\GoodsConfigModel();
        $res_goodsintegral = $m_goodsconfig->getInfo(array('goods_id'=>$stock_record_info['goods_id'],'type'=>10));
        if(empty($res_goodsintegral) || $res_goodsintegral['open_integral']==0){
            $msg = "stock_record_id:{$stock_record_id},goods_id:{$stock_record_info['goods_id']},open_integral:0";
            return $msg;
        }
        if($stock_record_info['wo_reason_type']!=1){
            $msg = "stock_record_id:{$stock_record_id},wo_reason_type:{$stock_record_info['wo_reason_type']} error";
            return $msg;
        }
        $open_area_ids = explode(',',$res_goodsintegral['open_area_ids']);
        if(!in_array($stock_record_info['area_id'],$open_area_ids)){
            $msg = "stock_record_id:{$stock_record_id},area_ids:{$stock_record_info['area_id']} error";
            return $msg;
        }

        $now_integral = $res_goodsintegral['open_integral'];
        $m_unit = new \Admin\Model\UnitModel();
        $res_unit = $m_unit->getInfo(array('id'=>$stock_record_info['unit_id']));
        $unit_num = intval($res_unit['convert_type']);
        $now_integral = $now_integral*$unit_num;

        $where = array('a.openid'=>$stock_record_info['op_openid'],'a.status'=>1,'merchant.status'=>1);
        $field_staff = 'a.openid,a.level,merchant.type,merchant.id as merchant_id,merchant.is_integral,merchant.is_shareprofit,merchant.shareprofit_config';
        $m_staff = new \Admin\Model\StaffModel();
        $res_staff = $m_staff->getMerchantStaff($field_staff,$where);
        $admin_integral = 0;
        $adminwhere = array('merchant_id'=>$res_staff[0]['merchant_id'],'level'=>1,'status'=>1);
        $res_admin_staff = $m_staff->getAll('id,openid',$adminwhere,0,1,'id desc');
        $admin_openid = $res_admin_staff[0]['openid'];
        $m_userintegral = new \Admin\Model\UserIntegralModel();
        $m_merchant = new \Admin\Model\MerchantModel();
        if($res_staff[0]['is_integral']==1){
            //开瓶费积分 增加分润
            if($res_staff[0]['is_shareprofit']==1 && $res_staff[0]['level']==2){
                $shareprofit_config = json_decode($res_staff[0]['shareprofit_config'],true);
                if(!empty($shareprofit_config['kpjl'])){
                    $staff_integral = ($shareprofit_config['kpjl'][1]/100)*$now_integral;
                    if($staff_integral>1){
                        $staff_integral = round($staff_integral);
                    }else{
                        $staff_integral = 1;
                    }
                    $admin_integral = $now_integral - $staff_integral;
                    $now_integral = $staff_integral;
                }
            }
            $integralrecord_openid = $stock_record_info['op_openid'];

            if($integral_status==1){
                if($admin_integral>0){
                    if(!empty($admin_openid)){
                        $res_integral = $m_userintegral->getInfo(array('openid'=>$admin_openid));
                        if(!empty($res_integral)){
                            $userintegral = $res_integral['integral']+$admin_integral;
                            $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                        }else{
                            $m_userintegral->add(array('openid'=>$admin_openid,'integral'=>$admin_integral));
                        }
                    }
                }
                $res_integral = $m_userintegral->getInfo(array('openid'=>$stock_record_info['op_openid']));
                if(!empty($res_integral)){
                    $userintegral = $res_integral['integral']+$now_integral;
                    $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                }else{
                    $m_userintegral->add(array('openid'=>$stock_record_info['op_openid'],'integral'=>$now_integral));
                }
            }
        }else{
            $integralrecord_openid = $stock_record_info['hotel_id'];

            if($integral_status==1){
                $where = array('id'=>$res_staff[0]['merchant_id']);
                $m_merchant->where($where)->setInc('integral',$now_integral);
            }
        }

        $m_hotel = new \Admin\Model\HotelModel();
        $field = 'hotel.area_id,area.region_name as area_name,hotel.name as hotel_name,hotel.hotel_box_type';
        $res_hotel = $m_hotel->getHotelById($field,array('hotel.id'=>$stock_record_info['hotel_id']));
        if($admin_integral>0 && !empty($admin_openid)){
            $integralrecord_data = array('openid'=>$admin_openid,'area_id'=>$res_hotel['area_id'],'area_name'=>$res_hotel['area_name'],
                'hotel_id'=>$stock_record_info['hotel_id'],'hotel_name'=>$res_hotel['hotel_name'],'hotel_box_type'=>$res_hotel['hotel_box_type'],
                'integral'=>$admin_integral,'jdorder_id'=>$stock_record_id,'content'=>1,'status'=>$integral_status,
                'type'=>25,'source'=>4);
            $this->add($integralrecord_data);
        }
        $integralrecord_data = array('openid'=>$integralrecord_openid,'area_id'=>$res_hotel['area_id'],'area_name'=>$res_hotel['area_name'],
            'hotel_id'=>$stock_record_info['hotel_id'],'hotel_name'=>$res_hotel['hotel_name'],'hotel_box_type'=>$res_hotel['hotel_box_type'],
            'integral'=>$now_integral,'jdorder_id'=>$stock_record_id,'content'=>1,'status'=>$integral_status,'type'=>25);
        $this->add($integralrecord_data);
        return $stock_record_id;
    }
}