<?php
namespace Admin\Model;
use Common\Lib\Page;
use Think\Model;
class PurchaseModel extends BaseModel{
	protected $tableName='finance_purchase';

	public function getList($fields,$where, $order='a.id desc', $start=0,$size=5){
	    $list = $this->alias('a')
	    ->join('savor_finance_contract c on a.contract_id=c.id','left')
	    ->join('savor_finance_department d on a.department_id = d.id','left')
	    ->join('savor_finance_supplier   s on a.supplier_id   = s.id','left')
	    ->field($fields)
	    ->where($where)
	    ->order($order)
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