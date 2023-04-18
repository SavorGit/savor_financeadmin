<?php
namespace Admin\Model;
use Common\Lib\Page;

class StockRecordModel extends BaseModel{
	protected $tableName='finance_stock_record';

    public function getStockRecordList($fileds,$where,$order,$limit,$group=''){
        $res = $this->alias('a')
            ->field($fileds)
            ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
            ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
            ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
            ->join('savor_finance_category cate on goods.category_id=cate.id','left')
            ->join('savor_finance_specification spec on goods.specification_id=spec.id','left')
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->group($group)
            ->select();
        return $res;
    }

    public function getAllStock($fileds,$where,$order,$group='',$limit=''){
        $res = $this->alias('a')
            ->field($fileds)
            ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->group($group)
            ->select();
        return $res;
    }

    public function getList($fields,$where, $order='a.id desc', $start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
            ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
            ->join('savor_finance_category cate on goods.category_id=cate.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $count = $this->alias('a')
            ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
            ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
            ->join('savor_finance_category cate on goods.category_id=cate.id','left')
            ->where($where)
            ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

    public function getChangeList($fields,$where, $order='a.id desc',$group, $start=0,$size=5){
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

    public function getRecordList($fields,$where, $order='a.id desc', $start=0,$size=5){
        if($start>=0 && $size>0){
            $list = $this->alias('a')
                ->join('savor_finance_sale sale on a.id=sale.stock_record_id','left')
                ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                ->join('savor_hotel hotel on stock.hotel_id=hotel.id','left')
                ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
                ->field($fields)
                ->where($where)
                ->order($order)
                ->limit($start,$size)
                ->select();
            $count = $this->alias('a')
                ->join('savor_finance_sale sale on a.id=sale.stock_record_id','left')
                ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                ->join('savor_hotel hotel on stock.hotel_id=hotel.id','left')
                ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
                ->where($where)
                ->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show);
        }else{
            $data = $this->alias('a')
                ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                ->join('savor_hotel hotel on stock.hotel_id=hotel.id','left')
                ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
                ->field($fields)
                ->where($where)
                ->order($order)
                ->select();
        }
        return $data;
    }


}