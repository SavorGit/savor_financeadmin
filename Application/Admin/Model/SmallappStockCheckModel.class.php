<?php
namespace Admin\Model;
use Common\Lib\Page;
class SmallappStockCheckModel extends BaseModel{
	protected $tableName='smallapp_stockcheck';

    public function getCheckDataList($fields,$where,$orderby,$start=0,$size=0){
        if($start >= 0 && $size){
            $list = $this->alias('a')
                ->field($fields)
                ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
                ->join('savor_hotel_ext ext on hotel.id=ext.hotel_id','left')
                ->join('savor_sysuser sysuser on ext.maintainer_id=sysuser.id','left')
                ->join('savor_integral_merchant_staff staff on a.staff_id=staff.id','left')
                ->join('savor_smallapp_user user on staff.openid=user.openid','left')
                ->where($where)
                ->order($orderby)
                ->limit($start,$size)
                ->select();
            $count = $this->alias('a')
                ->field($fields)
                ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
                ->join('savor_hotel_ext ext on hotel.id=ext.hotel_id','left')
                ->join('savor_sysuser sysuser on ext.maintainer_id=sysuser.id','left')
                ->join('savor_integral_merchant_staff staff on a.staff_id=staff.id','left')
                ->join('savor_smallapp_user user on staff.openid=user.openid','left')
                ->where($where)->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        }else{
            $data = $this->field($fields)->where($where)->order($orderby)->select();
        }
        return $data;
    }
}