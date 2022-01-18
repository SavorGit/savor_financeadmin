<?php
namespace Admin\Model;
use Common\Lib\Page;
use Think\Model;
class ContracthistoryModel extends BaseModel{
	protected $tableName='finance_contract_history';

    public function getList($fields,$where, $order='a.id desc', $start=0,$size=5){
		$list = $this->alias('a')
			->join('savor_finance_signuser b on a.sign_user_id= b.id','left')
			->join('savor_finance_sysuser c on a.sysuser_id=c.id','left')
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