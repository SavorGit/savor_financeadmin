<?php
namespace Admin\Model;
use Common\Lib\Page;
class GoodsModel extends BaseModel{
	protected $tableName='finance_goods';

    public function getList($fields,$where, $order='a.id desc', $start=0,$size=5){
        $list = $this->alias('goods')
            ->join('savor_finance_category cate on goods.category_id=cate.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $count = $this->alias('goods')
            ->join('savor_finance_category cate on goods.category_id=cate.id','left')
            ->where($where)
            ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

}