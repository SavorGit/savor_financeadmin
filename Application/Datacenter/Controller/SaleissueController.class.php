<?php
namespace Datacenter\Controller;
use Common\Lib\Page;
use Admin\Controller\BaseController;
class SaleissueController extends BaseController {
    
    public function datalist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        $start  = ($page-1) * $size;
        $order = I('_order','a.id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        
        $where = [];
        $where['a.add_time'] = array(array('EGT',$start_date.' 00:00:00'),array('ELT',$end_date.' 23:59:59'));
        /*$fields = "a.add_time,a.id,case a.type when 1 then '餐厅销售' when 2 then '团购售卖' when 3 then '其他售卖' end as type,
                   a.idcode,area.region_name,a.hotel_id,hotel.name hotel_name,goods.barcode,
                   goods.name goods_name,unit.name unit_name,spe.name spe_name,a.settlement_price,
                   a.cost_price,a.settlement_price-a.cost_price as profit ,
                   spr.add_time pay_time,spr.pay_money,a.settlement_price-spr.pay_money uncollected_money,
                   a.invoice_time,a.invoice_money,sysuser.remark,user.nickName,user.name";*/
        $fields = "a.add_time,a.id,a.type,record.wo_reason_type,
                   a.idcode,area.region_name,a.hotel_id,hotel.name hotel_name,goods.barcode,
                   goods.name goods_name,unit.name unit_name,spe.name spe_name,a.settlement_price,
                   a.cost_price,a.settlement_price-a.cost_price as profit ,
                   a.invoice_time,a.invoice_money,sysuser.remark,user.nickName,user.name,ar.region_name tg_region_name";
        $m_sale = new \Admin\Model\SaleModel();
        $result = $m_sale->getAllList($fields, $where, $orders, $start, $size);
        $all_sale_types = C('SALE_TYPES');
        $all_stock_types = C('STOCK_USE_TYPE');
        $m_sale_payment_record = new \Admin\Model\SalePaymentRecordModel();
        foreach($result['list'] as $key=>$v){
            if($v['type']==1){
                $type = $all_stock_types[$v['wo_reason_type']];
            }else{
                $type = $all_sale_types[$v['type']];
                $result['list'][$key]['unit_name'] = '瓶';
                $result['list'][$key]['region_name'] = $v['tg_region_name'];
            }
            $result['list'][$key]['type'] = $type;
            $rts = $m_sale_payment_record->where(array('sale_id'=>$v['id']))->field('add_time as  pay_time,pay_money')->order('add_time desc')->select();
            if(empty($v['name'])){
                $result['list'][$key]['name'] = $v['nickname'];
            }
            if(empty($rts)){
                $account_days =  ceil((time() - strtotime($v['add_time'])) / 86400) ;
                $result['list'][$key]['account'] = $account_days.'天';
                $result['list'][$key]['uncollected_money'] = $v['settlement_price'];
            }else {
                $t_money = 0;
                foreach($rts as $kk=>$vv){
                    $t_money += $vv['pay_money'];
                }
                $account_days = ceil((strtotime($rts[0]['pay_time']) - strtotime($v['add_time'])) / 86400) ;
                $result['list'][$key]['uncollected_money'] = $v['settlement_price'] - $t_money;
                $result['list'][$key]['pay_money'] = $t_money;
                $result['list'][$key]['pay_time']  = $rts[0]['pay_time'];
                $result['list'][$key]['account']   = $account_days.'天';
            }
            /*if($v['uncollected_money']==0 && $v['pay_time']!=''){
                $account_days =  ceil((strtotime($v['pay_time']) - strtotime($v['add_time'])) / 86400) ;
            }else {
                
                $account_days =  ceil((time() - strtotime($v['add_time'])) / 86400) ;
                $result['list'][$key]['uncollected_money'] = $v['settlement_price'];
            }    
            $result['list'][$key]['account'] = $account_days.'天';
            if(empty($v['name'])){
                $result['list'][$key]['name'] = $v['nickname'];
            }*/
        }
        
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }
    /**
     * @desc 数据查询 销售出库单汇总表
     */
    public function datasummary(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        
        
        $start  = ($page-1) * $size;
        
        $order = I('_order','a.id');
        
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        
        /*$where = [];
        $where['a.add_time'] = array(array('EGT',$start_date.' 00:00:00'),array('ELT',$end_date.' 23:59:59'));
        
        $fields = "a.hotel_id,hotel.name hotel_name";
        $group  = "a.hotel_id";
        $m_sale = new \Admin\Model\SaleModel();
        $list = $m_sale->alias('a')
                         ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
                         
                         ->field($fields)
                         ->where($where)
                         ->order($orders)
                         ->group($group)
                         ->limit($start,$size)
                         ->select();
        $sale_type_arr = $this->sale_type_arr;
        foreach($list as $key=>$v){
            
            foreach($sale_type_arr as $kk=>$vv){
                $ret = $m_sale->alias('a')
                              ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
                              ->join('savor_finance_goods hotel on a.hotel_id = hotel.id','left')
            }
        }
        $count =  $m_sale->alias('a')
                         ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
                         ->field($fields)
                         ->where($where)
                         ->order($orders)
                         ->group($group)
                         ->select();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $result = array('list'=>$list,'page'=>$show);
        
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);*/
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }
    /**
     * @desc 数据查询-应收账款汇总
     */
    public function receivables(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->display();
    }
    /**
     * @desc 数据查询-账龄分析表
     */
    public function accountage(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->display();
    }
    
}