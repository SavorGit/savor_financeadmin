<?php
namespace Admin\Model;
use Common\Lib\Page;

class StockPaymentRecordModel extends BaseModel{

    protected $tableName='finance_stock_payment_record';

    public function getList($fields,$where,$orderby,$start=0,$size=0){
        if($start >= 0 && $size){
            $list = $this->alias('a')
                ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                ->field($fields)
                ->where($where)
                ->order($orderby)
                ->limit($start,$size)
                ->select();
            $count = $this->alias('a')
                ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                ->where($where)
                ->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        }else{
            $data = $this->alias('a')
                ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                ->field($fields)
                ->where($where)
                ->order($orderby)
                ->select();
        }
        return $data;
    }

    public function getPaymentRecords($fields,$where,$orderby,$limit=''){
        $data = $this->alias('a')
            ->join('savor_finance_stock_payment p on a.stock_payment_id=p.id','left')
            ->join('savor_finance_pushu8_record pushu8 on a.id=pushu8.payment_record_id','left')
            ->field($fields)
            ->where($where)
            ->order($orderby)
            ->limit($limit)
            ->select();
        return $data;
    }
}
