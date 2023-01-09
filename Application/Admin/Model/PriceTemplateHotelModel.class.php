<?php
namespace Admin\Model;
use Common\Lib\Page;

class PriceTemplateHotelModel extends BaseModel{
	protected $tableName='finance_price_template_hotel';

	public function getHotelPriceTemplate($hotel_id){
	    $fields = 't.id,t.name,t.type';
	    $where = array('a.hotel_id'=>$hotel_id,'t.status'=>1);
        $result = $this->alias('a')
            ->join('savor_finance_price_template t on a.template_id=t.id','left')
            ->field($fields)
            ->where($where)
            ->order('t.id desc')
            ->limit(0,1)
            ->find();
        return $result;
    }

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

    public function getHotelGoodsPrice($hotel_id,$goods_id,$is_cache=1){
        $settlement_price = 0;
        if($is_cache==1){
            $redis = new \Common\Lib\SavorRedis();
            $redis->select(9);
            $cache_key = C('FINANCE_HOTELGOODS_PRICE');
            $res_cache = $redis->get($cache_key.":$hotel_id");
            if(!empty($res_cache)){
                $hotel_price = json_decode($res_cache,true);
                if(isset($hotel_price[$goods_id])){
                    $settlement_price = $hotel_price[$goods_id];
                }
            }
        }else{
            $where = array('a.hotel_id'=>array('in',"$hotel_id,0"),'a.goods_id'=>$goods_id,'t.status'=>1);
            $result = $this->alias('a')
                ->join('savor_finance_price_template t on a.template_id=t.id','left')
                ->field('a.template_id')
                ->where($where)
                ->order('t.id desc')
                ->limit(0,1)
                ->find();
            if(!empty($result)){
                $template_id = $result['template_id'];
                $m_pricegoods = new \Admin\Model\PriceTemplateGoodsModel();
                $field = 'settlement_price';
                $res_pgoods = $m_pricegoods->getAll($field,array('template_id'=>$template_id,'goods_id'=>$goods_id),0,1,'id desc');
                if(!empty($res_pgoods[0]['settlement_price'])){
                    $settlement_price = $res_pgoods[0]['settlement_price'];
                }
            }
        }
        return $settlement_price;
    }
}