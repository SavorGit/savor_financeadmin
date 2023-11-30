<?php
namespace Admin\Model;
use Common\Lib\Page;

class StockDetailModel extends BaseModel{
	protected $tableName='finance_stock_detail';

    public function getHotelStockGoods($fileds,$where,$group='',$start=0,$size=5){
        if($start>=0 && $size>0){
            $list = $this->alias('a')
                ->field($fileds)
                ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                ->join('savor_hotel hotel on stock.hotel_id=hotel.id','left')
                ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
                ->join('savor_finance_category cate on goods.category_id=cate.id','left')
                ->join('savor_finance_specification spec on goods.specification_id=spec.id','left')
                ->where($where)
                ->limit($start,$size)
                ->group($group)
                ->select();
            $count = $this->alias('a')
                ->field($fileds)
                ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                ->join('savor_hotel hotel on stock.hotel_id=hotel.id','left')
                ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
                ->join('savor_finance_category cate on goods.category_id=cate.id','left')
                ->join('savor_finance_specification spec on goods.specification_id=spec.id','left')
                ->where($where)
                ->group($group)
                ->select();
            $count = count($count);
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show);
        }else{
            $data = $this->alias('a')
                ->field($fileds)
                ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                ->join('savor_hotel hotel on stock.hotel_id=hotel.id','left')
                ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
                ->join('savor_finance_category cate on goods.category_id=cate.id','left')
                ->join('savor_finance_specification spec on goods.specification_id=spec.id','left')
                ->where($where)
                ->group($group)
                ->select();
        }
        return $data;
    }

    public function getList($fields,$where, $order='a.id desc', $start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
            ->join('savor_finance_category cate on goods.category_id=cate.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $count = $this->alias('a')
            ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
            ->join('savor_finance_category cate on goods.category_id=cate.id','left')
            ->where($where)
            ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

    public function getStockGoods(){
//        $sql_goods = "select id,name from savor_finance_goods where id in(
//        select goods_stock.goods_id from (select goods_id,sum(total_amount) as amount from savor_finance_stock_detail group by goods_id) as goods_stock
//        where goods_stock.amount>0) and status=1 order by brand_id asc,id asc";
        $sql_goods = "select id,name from savor_finance_goods where status=1 order by brand_id asc,id asc";
        $res = $this->query($sql_goods);
        return $res;
    }

    public function getChangeList($fields,$where, $order='a.id desc', $group,$start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
            ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->group($group)
            ->limit($start,$size)
            ->select();
        $count = $this->alias('a')
            ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
            ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
            ->where($where)
            ->group($group)
            ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }
    public function getAllStockGoods($fields,$where, $order='a.id desc',$start=0,$size=5,$group = ''){
        $list = $this->alias('a')
        ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
        ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
        ->join('savor_finance_purchase p on stock.purchase_id=p.id','left')
        ->join('savor_area_info area on stock.area_id=area.id','left')
        ->join('savor_finance_supplier s on p.supplier_id= s.id','left')
        ->join('savor_finance_specification sp on sp.id=goods.specification_id','left')
        ->join('savor_finance_brand brand on goods.brand_id=brand.id','left')
        ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
        ->field($fields)
        ->where($where)
        ->order($order)
        ->group($group)
        ->limit($start,$size)
        ->select();
        $count = $this->alias('a')
        ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
        ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
        ->join('savor_finance_supplier s on goods.supplier_id= s.id','left')
        ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
        ->where($where)
        ->group($group)
        ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

}