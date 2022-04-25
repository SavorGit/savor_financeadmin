<?php
namespace Admin\Model;
use Common\Lib\Page;

class StockDetailModel extends BaseModel{
	protected $tableName='finance_stock_detail';

    public function getList($fields,$where, $order='a.id desc', $start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
            ->join('savor_finance_category cate on goods.category_id=cate.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $count = $this->alias('a')
            ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
            ->join('savor_finance_category cate on goods.category_id=cate.id','left')
            ->where($where)
            ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

    public function getStockGoods(){
        $sql_goods = "select id,name from savor_finance_goods where id in(
        select goods_stock.goods_id from (select goods_id,sum(total_amount) as amount from savor_finance_stock_detail group by goods_id) as goods_stock
        where goods_stock.amount>0) and status=1";
        $res = $this->query($sql_goods);
        return $res;
    }

    public function getChangeList($fields,$where, $order='a.id desc', $group,$start=0,$size=5){
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

}