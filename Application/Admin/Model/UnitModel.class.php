<?php
namespace Admin\Model;

class UnitModel extends BaseModel{
	protected $tableName='finance_unit';

    public function getStockUnitByGoods($goods_id){
        /*
        $sql_unit = "select * from savor_finance_unit where id in(
        select unit_stock.unit_id from (select unit_id,sum(amount) as amount from savor_finance_stock_detail where goods_id={$goods_id} group by unit_id) as unit_stock
        where unit_stock.amount>0)";

        $sql_unit = "select * from savor_finance_unit where id in(select unit_id from savor_finance_stock_detail where goods_id={$goods_id} group by unit_id)";
        */
        $sql_unit = "select * from savor_finance_unit where category_id=(select category_id from savor_finance_goods where id={$goods_id}) and status=1";
        $res = $this->query($sql_unit);
        return $res;
    }

}