<?php
namespace Admin\Model;
use Common\Lib\Page;

class ActivityPolicyHotelModel extends BaseModel{
	protected $tableName='finance_activity_policy_hotel';

    public function getHotelDatas($fields,$where,$order,$group,$start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_hotel h on a.hotel_id=h.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->group($group)
            ->select();

        $count = $this->alias('a')
            ->join('savor_hotel h on a.hotel_id=h.id','left')
            ->field('a.id')
            ->where($where)
            ->group($group)
            ->select();
        $count = count($count);
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }
}