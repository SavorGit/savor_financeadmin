<?php
namespace Admin\Model;
use Think\Model;
use Common\Lib\Page;
class SaleModel extends BaseModel{
    
    protected $tableName='finance_sale';
    
    public function getList($fileds,$where, $orders, $start,$size){
        $list = $this->alias('a')
        ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
        ->join('savor_finance_goods goods on a.goods_id   = goods.id','left')
        ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
        ->field($fileds)
        ->where($where)
        ->order($orders)
        ->limit($start,$size)
        ->select();
        
        $count = $this->alias('a')
        ->where($where)
        ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }
}