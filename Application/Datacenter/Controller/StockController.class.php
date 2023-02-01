<?php
namespace Datacenter\Controller;
use Common\Lib\Page;
use Admin\Controller\BaseController;
class StockController extends BaseController {
    
    public function inlist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        
        
        $start  = ($page-1) * $size;
        
        $order = I('_order','stock.id');
        
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        
        $where = [];
        $where['stock.io_date '] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        $where['a.status']       = 1;
        $where['stock.type']     = 10;
        $where['stock.io_type']  = array('in','11,12,13');
        
        
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $fields = "stock.name stock_name,stock.serial_number,stock.io_date,
                   case stock.io_type
				   when 11 then '采购入库'
                   when 12 then '调拨入库'
                   when 13 then '餐厅退回' END AS io_type,
                   case stock.status
                   when 1 then '进行中'
                   when 2 then '已完成'
                   when 3 then '已领取'
                   when 4 then '已验收' END AS status,
 
                   s.name supplier_name,goods.barcode,goods.name goods_name,
                   unit.name u_name,area.region_name,a.total_amount,a.price,a.rate";
        $result = $m_stock_detail->getAllStockGoods($fields, $where,$orders,$start,$size);
        foreach($result['list'] as $key=>$v){
            
            $result['list'][$key]['rate'] = !empty($v['rate']) ? ($v['rate']*100).'%':'';
            $rate_money = $v['price'] * $v['rate'];
            $total_money = $v['price'] * $v['total_amount'];
            $no_rate_total_money = $total_money - $rate_money * $v['total_amount'];
            
            $result['list'][$key]['no_rate_price']      = $v['price'] - $rate_money;
            $result['list'][$key]['rate_money']         = $rate_money;
            $result['list'][$key]['total_money']        = $total_money;
            $result['list'][$key]['no_rate_total_money']= $no_rate_total_money;
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
     * @desc 入库汇总表
     */
    public function insummary(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        
        
        $start  = ($page-1) * $size;
        
        $order = I('_order','stock.id');
        
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        
        $where = [];
        $where['stock.io_date '] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        $where['a.status']       = 1;
        $where['stock.type']     = 10;
        $where['stock.io_type']  = array('in','11,12,13');
        
        $fields = 'a.goods_id,goods.barcode,goods.name goods_name,unit.name u_name,
                   brand.name brand_name';
        
        $group = 'a.goods_id';
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $result = $m_stock_detail->getAllStockGoods($fields, $where,$orders,$start,$size,$group);
        foreach($result['list'] as $key=>$v){
            
            $where = [];
            $where['stock.io_date '] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
            $where['a.goods_id'] = $v['goods_id'];
            $where['a.status']       = 1;
            $where['stock.type']     = 10;
            $where['stock.io_type']  = array('in','11,12,13');
            
            $fields = 'a.total_amount,a.price,a.rate,goods.name goods_name,brand.name brand_name';
            $rts = $m_stock_detail->alias('a')
                           ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                           ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                           ->join('savor_finance_brand brand on goods.brand_id=brand.id','left')
                           ->field($fields)
                           ->where($where)
                           ->select();
            $total_amount = 0;          //数量
            $total_money = 0;           //含税总金额
            $no_rate_total_money = 0;   //不含税总金额
            foreach($rts as $kk=>$vv){
                //数量
                $total_amount += $vv['total_amount'];
                $total_money  += $vv['price'] * $vv['total_amount'];
                $rate_money    = $vv['price'] * $vv['rate'];
                $no_rate_total_money += $total_money - $rate_money;
                
            }
            $result['list'][$key]['total_amount']         = $total_amount;
            $result['list'][$key]['total_money']          = $total_money;
            $result['list'][$key]['no_rate_total_money']  = $no_rate_total_money;
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
     * @desc 唯一识别码跟踪
     */
    public function idcodetrack(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        
        
        $start  = ($page-1) * $size;
        
        $order = I('_order','stock.id');
        
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        
        $where = [];
        $where['stock.io_date '] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        
        $where['stock.type']     = 10;
        $where['stock.io_type']  = array('in','11');
        //print_r($where);exit;
        $m_stock_detail = new \Admin\Model\StockRecordModel();
        
        $fields = 'a.idcode,goods.barcode,goods.name goods_name';
        $group  = 'a.idcode';
       
        $result = $m_stock_detail->alias('a')
        ->field($fields)
        ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
        ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
        ->where($where)
        ->order($order)
        ->group($group)
        ->select();
        
        
        
        
        $this->assign('list', $result);
        //$this->assign('page',  );
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
        
    }
    /**
     * @desc 商品收发明细表
     */
    public function goodsiolist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        
        
        $start  = ($page-1) * $size;
        
        $order = I('_order','stock.id');
        
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        
        $where = [];
        $where['stock.io_date '] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        $where['a.status']       = 1;
        //$where['stock.type']     = 10;
        //$where['stock.io_type']  = array('in','11,12,13');
        
        $fields = 'a.goods_id,goods.barcode,goods.name goods_name,unit.name u_name,
                   brand.name brand_name';
        
        $group = 'a.goods_id';
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $result = $m_stock_detail->getAllStockGoods($fields, $where,$orders,$start,$size,$group);
        
        
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }
    
    
    
    
    
    
    
    
    
}