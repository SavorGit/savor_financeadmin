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

    public function checkHotelThreshold($hotel_id){
        $m_sale = new \Admin\Model\SaleModel();
        $fileds = 'sum(a.settlement_price) as money';
        $where = array('a.hotel_id'=>$hotel_id,'a.ptype'=>0,'record.type'=>7,'record.wo_reason_type'=>1,'record.wo_status'=>2);
        $res_data = $m_sale->getList($fileds,$where, '', 0,0);
        $ys_money = intval($res_data[0]['money']);

        $where['a.is_expire'] = 1;
        $res_data = $m_sale->getList($fileds,$where, '', 0,0);
        $cq_money = intval($res_data[0]['money']);

        $redis = new \Common\Lib\SavorRedis();
        $redis->select(9);
        $cache_key = C('FINANCE_HOTELSTOCK');
        $hotel_cache_key = $cache_key.":$hotel_id";
        $res_hotel_stock = $redis->get($hotel_cache_key);
        $stock_num = 0;
        if(!empty($res_hotel_stock)){
            $hotel_stock = json_decode($res_hotel_stock,true);
            foreach ($hotel_stock['goods_list'] as $v){
                $stock_num+=$v['stock_num'];
            }
        }
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $res_config = $m_sys_config->getAllconfig();
        $sale_ys_money = $res_config['sale_ys_money'];
        $sale_cq_money = $res_config['sale_cq_money'];
        $hotel_stock_num = $res_config['hotel_stock_num'];
        $is_out = 1;
        if($ys_money>=$sale_ys_money || $cq_money>=$sale_cq_money || $stock_num>=$hotel_stock_num){
            $is_out = 0;
        }
        return $is_out;
    }

    public function getStockInfo($fields,$where){
        $data = $this->alias('a')
            ->join('savor_finance_purchase p on a.purchase_id=p.id','left')
            ->join('savor_finance_supplier s on p.supplier_id=s.id','left')
            ->field($fields)
            ->where($where)
            ->find();
        return $data;
    }
}