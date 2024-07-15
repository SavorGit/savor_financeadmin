<?php
namespace Admin\Model;
use Common\Lib\Page;

class GoodsPolicyHotelModel extends BaseModel{
	protected $tableName='finance_goods_policy_hotel';

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

    public function getGoodsPolicyHotels($fields,$where,$order,$limit=''){
        $list = $this->alias('a')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->join('savor_finance_goods_policy gp on a.policy_id=gp.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->select();
        return $list;
    }

    public function getGoodsPolicy($goods_id,$area_id,$hotel_id){
        $fileds = 'p.id as policy_id,p.integral,p.open_integral,p.media_id as open_media_id';
        $where = array('p.goods_id'=>$goods_id,'p.status'=>1,'a.area_id'=>$area_id,'a.hotel_id'=>array('in',array($hotel_id,0)));
        $res = $this->alias('a')
            ->field($fileds)
            ->join('savor_finance_goods_policy p on a.policy_id=p.id','left')
            ->where($where)
            ->order('p.id desc')
            ->limit(0,1)
            ->select();
        $policy_data = array();
        if(!empty($res[0]['policy_id'])){
            $policy_data = $res[0];
        }
        return $policy_data;
    }
}