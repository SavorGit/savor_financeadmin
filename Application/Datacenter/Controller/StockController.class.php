<?php
namespace Datacenter\Controller;
use Common\Lib\Page;
use Admin\Controller\BaseController;
class StockController extends BaseController {
    
    private $store_type_arr = array(
        array('id'=>1,'name'=>'中转仓'),
        array('id'=>2,'name'=>'前置仓'),
    );
    public function dinlist(){
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
        $where['stock.io_date'] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        $where['a.status']       = 1;
        $where['stock.type']     = 10;
        $where['stock.io_type']  = array('in','11,12,13');
        
        
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $fields = "a.id stock_detail_id,stock.id stock_id,stock.name stock_name,stock.serial_number,stock.io_date,
                   case stock.io_type
				   when 11 then '采购入库'
                   when 12 then '调拨入库'
                   when 13 then '餐厅退回' END AS io_type,
                   case stock.status
                   when 1 then '进行中'
                   when 2 then '已完成'
                   when 3 then '已领取'
                   when 4 then '已验收' END AS status,
 
                   s.name supplier_name,goods.id goods_id,goods.barcode,goods.name goods_name,
                   unit.name u_name,area.id area_id,area.region_name,a.total_amount,a.price,a.rate";
        $result = $m_stock_detail->getAllStockGoods($fields, $where,$orders,$start,$size);
        foreach($result['list'] as $key=>$v){
            
            //数量
            $where = [];
            $map['stock_id']        = $v['stock_id'];
            $map['stock_detail_id'] = $v['stock_detail_id'];
            $map['goods_id']        = $v['goods_id'];
            $rt = $m_stock_record->field('sum(abs(total_amount)) as total_amount,sum(abs(total_fee)) as total_fee')
                                 ->where($map)
                                 ->find();
            $total_amount = $rt['total_amount'];
            
            
            $result['list'][$key]['rate'] = !empty($v['rate']) ? ($v['rate']*100).'%':'';
            $no_rate_price = round($v['price'] / (1+$v['rate']),2); //不含税单价
            
            $rate_money = $v['price'] - $no_rate_price;
            $total_money = $v['price'] * $total_amount;
            
            
            
            $no_rate_total_money = $no_rate_price * $total_amount;
            
            $result['list'][$key]['no_rate_price']      = $no_rate_price;
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
        $this->display('inlist');
    }
    /**
     * @desc 入库汇总表
     */
    public function dinsummary(){
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
        $where['stock.io_date'] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        $where['a.status']       = 1;
        $where['stock.type']     = 10;
        $where['stock.io_type']  = array('in','11,12,13');
        
        $fields = 'a.goods_id,goods.barcode,goods.name goods_name,unit.name u_name,
                   brand.name brand_name,sp.name sp_name';
        
        $group = 'a.goods_id';
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $result = $m_stock_detail->getAllStockGoods($fields, $where,$orders,$start,$size,$group);
        foreach($result['list'] as $key=>$v){
            
            $where = [];
            $where['stock.io_date'] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
            $where['a.goods_id'] = $v['goods_id'];
            $where['a.status']       = 1;
            $where['stock.type']     = 10;
            $where['stock.io_type']  = array('in','11,12,13');
            
            $fields = 'a.id stock_detail_id,stock.id stock_id,a.total_amount,a.price,a.rate,
                       goods.id goods_id,goods.name goods_name,brand.name brand_name';
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
                $map = [];
                $map['stock_id']        = $vv['stock_id'];
                $map['stock_detail_id'] = $vv['stock_detail_id'];
                $map['goods_id']        = $vv['goods_id'];
                $rt = $m_stock_record->field('sum(abs(total_amount)) as total_amount,sum(abs(total_fee)) as total_fee')
                ->where($map)
                ->find();
                
                
                $total_amount += $rt['total_amount'];
                
                $total_money  += $vv['price'] * $rt['total_amount'];
                $rate_money    = $vv['price'] /(1+$vv['rate']) ;
                $no_rate_total_money += $rate_money * $rt['total_amount'];
                
            }
            $result['list'][$key]['total_amount']         = $total_amount;
            $result['list'][$key]['total_money']          = $total_money;
            $result['list'][$key]['no_rate_total_money']  = round($no_rate_total_money,2);
        }
        
        
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('insummary');
    }
    /**
     * @desc 唯一识别码跟踪
     */
    public function didcodetrack(){
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
        $where['stock.io_date'] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        
        $where['stock.type']     = 10;
        $where['stock.io_type']  = array('in','11,12,13');
        //print_r($where);exit;
        $m_stock_detail = new \Admin\Model\StockRecordModel();
        
        $fields = 'a.idcode,goods.id goods_id,goods.barcode,goods.name goods_name';
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
        $this->display('idcodetrack');
        
    }
    /**
     * @desc 商品收发明细表
     */
    public function dgoodsiolist(){
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
        $where['stock.io_date'] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        $where['a.status']       = 1;
        //$where['stock.type']     = 10;
        //$where['stock.io_type']  = array('in','11,12,13');
        
        $fields = 'a.goods_id,goods.barcode,goods.name goods_name,unit.name u_name,
                   brand.name brand_name';
        
        $group = 'a.goods_id';
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $result = $m_stock_detail->alias('a')
                                 ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                                 ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                 ->join('savor_area_info area on stock.area_id=area.id','left')
                                 ->join('savor_finance_supplier s on goods.supplier_id= s.id','left')
                                 ->join('savor_finance_brand brand on goods.brand_id=brand.id','left')
                                 ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
                                 ->field($fields)
                                 ->where($where)
                                 ->order($order)
                                 ->group($group)
                                 ->limit($start,$size)
                                 ->select();
        $all_result  = $m_stock_detail->alias('a')
                                 ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                                 ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                 ->join('savor_finance_supplier s on goods.supplier_id= s.id','left')
                                 ->join('savor_finance_brand brand on goods.brand_id=brand.id','left')
                                 ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
                                 ->field($fields)
                                 ->where($where)
                                 ->group($group)
                                 ->select();
        $count = count($all_result);
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$result,'page'=>$show);
        
        $this->assign('list', $data['list']);
        $this->assign('page',  $data['page']);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('goodsiolist');
    }
    /**
     * @desc 出库成本核算 
     */
    public function doutlistcost(){
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
        $where['stock.io_date'] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        
        $where['stock.io_date'] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        $where['a.status']       = 1;
        $where['stock.type']     = 20;
        
        $fields = 'a.goods_id,goods.barcode,goods.name goods_name,unit.name u_name,
                   brand.name brand_name';
        $group = 'a.goods_id';
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $result = $m_stock_detail->alias('a')
                                ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                                ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                ->join('savor_area_info area on stock.area_id=area.id','left')
                                ->join('savor_finance_supplier s on goods.supplier_id= s.id','left')
                                ->join('savor_finance_brand brand on goods.brand_id=brand.id','left')
                                ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
                                ->field($fields)
                                ->where($where)
                                ->order($order)
                                ->group($group)
                                ->limit($start,$size)
                                ->select();
        $all_result  = $m_stock_detail->alias('a')
        ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
        ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
        ->join('savor_finance_supplier s on goods.supplier_id= s.id','left')
        ->join('savor_finance_brand brand on goods.brand_id=brand.id','left')
        ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
        ->field($fields)
        ->where($where)
        ->group($group)
        ->select();
        $count = count($all_result);
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$result,'page'=>$show);
        
        $this->assign('list', $data['list']);
        $this->assign('page',  $data['page']);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('outlistcost');
    }
    /**
     * @desc 库龄明细表
     */
    public function stockageDetail(){
        
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        $start  = ($page-1) * $size;
        $order = I('_order','id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        
        $where = [];
        $area_id    = I('area_id',0,'intval');
        $store_type = I('store_type',0,'intval');
        if($area_id){
            $where['area_id'] = $area_id;
            $this->assign('area_id',$area_id);
        }
        if($store_type){
            $where['store_type'] = $store_type;
            $this->assign('store_type',$store_type);
        }
        $goods_name = I('goods_name','','trim');
        
        $goods_id_str = '';
        if(!empty($goods_name)){
            
            $m_goods = new \Admin\Model\GoodsModel();
            $map = [];
            $map['name'] = array('like','%'.$goods_name.'%');
            
            $goods_list = $m_goods->field('id goods_id')->where($map)->select();
            if(!empty($goods_list)){
                $goods_id_arr = [];
                
                foreach($goods_list as $key=>$v){
                    $goods_id_arr [] = $v['goods_id'];
                    $goods_id_str .=$space.$v['goods_id'];
                    $space = ',';
                }
                $where['goods_id'] = array('in',$goods_id_arr);
            }
            
        }
        
        $end_date   = I('end_date','');
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d',strtotime('-1 days'));
        $where['static_date'] = $end_date;
        
        $m_accountage_detail = new \Admin\Model\DataAccountageDetailModel();
        $data = $m_accountage_detail->getList('*', $where, $orders,$start,$size);
        
        //print_r($data['list']);
        //仓库类型
        $store_type_arr = $this->store_type_arr;
        
        //城市
        $m_area = new \Admin\Model\AreaModel();
        $city_arr = $m_area->getHotelAreaList();
        
        $this->assign('store_type_arr',$store_type_arr);
        $this->assign('goods_id_str',$goods_id_str);
        $this->assign('goods_name',$goods_name);
        $this->assign('city_arr',$city_arr);
        $this->assign('end_date',$end_date);
        $this->assign('list',$data['list']);
        $this->assign('page',$data['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }
    /**
     * @desc  销售出库单列表
     */
    public function salestock(){
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
        $where['a.add_time'] = array(array('EGT',$start_date.' 00:00:00'),array('ELT',$end_date.' 23:59:59')) ;
        $m_sale = new \Admin\Model\SaleModel();
        
    }
    
    
    
    
    
    
    
}