<?php
namespace Admin\Model;
use Common\Lib\Page;

class StockModel extends BaseModel{
	protected $tableName='finance_stock';

    public function getList($fields,$where, $order='a.id desc', $start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_finance_purchase p on a.purchase_id=p.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();

        $count = $this->alias('a')
            ->join('savor_finance_purchase p on a.purchase_id=p.id','left')
            ->where($where)
            ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

}