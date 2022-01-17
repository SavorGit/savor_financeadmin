<?php
namespace Admin\Model;
use Common\Lib\Page;
use Think\Model;
class ContractModel extends BaseModel{
	protected $tableName='finance_contract';

    public function getList($fields,$where, $order='id desc', $start=0,$size=5){
		$list = $this->field($fields)
			->where($where)
			->order($order)
			->limit($start,$size)
			->select();
			
			$count = $this->alias('ct')
			->where($where)
			->count();
		$objPage = new Page($count,$size);
		$show = $objPage->admin_page();
		$data = array('list'=>$list,'page'=>$show);
		return $data;
	}

}