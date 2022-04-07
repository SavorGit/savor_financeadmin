<?php
namespace Admin\Model;
use Common\Lib\Page;
use Think\Model;
class PurchaseDetailModel extends BaseModel{
	protected $tableName='finance_purchase_detail';

	public function getList($fields,$where, $order='a.id desc', $start=0,$size=0){
	    $list = $this->alias('a')
	    ->join('savor_finance_goods g on a.goods_id = g.id','left')
	    ->join('savor_finance_category c on g.category_id=c.id','left')
	    ->join('savor_finance_unit  u on a.unit_id  = u.id','left')
	    ->field($fields)
	    ->where($where)
	    ->order($order)
	    ->limit($start,$size)
	    ->select();
	    if($start>=0 && $size>0){
            $count = $this->alias('a')
                ->where($where)
                ->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show);
        }else{
            $data = $list;
        }
	    return $data;
	}

}