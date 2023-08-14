<?php
namespace Admin\Model;
use Think\Model;
use Common\Lib\Page;
class SaleModel extends BaseModel{
    
    protected $tableName='finance_sale';
    
    public function getList($fileds,$where, $orders, $start,$size){
        if($start>=0 && $size>0){
            $list = $this->alias('a')
                ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
                ->join('savor_finance_goods goods on a.goods_id   = goods.id','left')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->field($fileds)
                ->where($where)
                ->order($orders)
                ->limit($start,$size)
                ->select();

            $count = $this->alias('a')->where($where)->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show);
        }else{
            $data = $this->alias('a')
                ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
                ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->field($fileds)
                ->where($where)
                ->order($orders)
                ->select();
        }
        return $data;
    }

    public function getJdDataList($fileds,$where){
        $data = $this->alias('a')
            ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->join('savor_hotel_ext ext on hotel.id=ext.hotel_id','left')
            ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
            ->field($fileds)
            ->where($where)
            ->select();
        return $data;
    }
    public function getAllList($fileds,$where, $orders, $start,$size){
        $list = $this->alias('a')
        //->join('savor_finance_sale_payment_record spr on a.id=spr.sale_id','left')
        ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
        ->join('savor_finance_goods goods on a.goods_id   = goods.id','left')
        
        ->join('savor_finance_specification spe on goods.specification_id= spe.id','left')
        ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
        ->join('savor_finance_unit unit on unit.id = record.unit_id','left')
        ->join('savor_sysuser sysuser on a.maintainer_id= sysuser.id ','left')
        ->join('savor_area_info area on  area.id=hotel.area_id','left')
        ->join('savor_smallapp_user user on a.sale_openid=user.openid','left')
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