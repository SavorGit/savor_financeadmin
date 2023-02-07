<?php
namespace Datacenter\Controller;
use Common\Lib\Page;
use Admin\Controller\BaseController;
class PurchaselistController extends BaseController {
   
    public function index(){
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
        $where['p.purchase_date'] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        $where['a.status'] = 1;
        $fileds = "ct.serial_number c_serial_number,p.serial_number,p.purchase_date,a.total_amount,s.name supplier_name,
                   case a.status
				   when 1 then '进行中'
				   when 2 then '已完成' END AS status,
                   g.id goods_id,g.name goods_name";
        $PurchaseDetailModel = new \Admin\Model\PurchaseDetailModel();
        $result = $PurchaseDetailModel->getDataCenterList($fileds,$where,$orders,$start,$size);
        //print_r($result['page']);
        
        
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }
    public function summary(){
        
        $page = I('pageNum',1);
        $size   = I('numPerPage',1000);
        
        
        $start  = ($page-1) * $size;
        
        $order = I('_order','g.id');
        
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        
        $where = [];
        $where['p.purchase_date'] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        $where['a.status'] = 1;
        
        
        $PurchaseDetailModel = new \Admin\Model\PurchaseDetailModel();
        
        $fields = 'g.barcode,a.goods_id ,g.name goods_name ,s.name supplier_name';
        $result = $PurchaseDetailModel->alias('a')
                            ->join('savor_finance_purchase p on a.purchase_id=p.id','left')
                            ->join('savor_finance_goods g on a.goods_id = g.id','left')
                            ->join('savor_finance_supplier s on g.supplier_id= s.id','left')
                            ->field($fields)->where($where)->group('a.goods_id')->select();
        foreach($result as $key=>$v){
            //数量
            $where = [];
            $where['p.purchase_date'] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
            $where['a.status']   = 1;
            $where['a.goods_id'] = $v['goods_id']; 
            
            $fields = 'a.total_amount,a.price,st.id stock_id';
            $rts = $PurchaseDetailModel->alias('a')
                                ->join('savor_finance_purchase p on a.purchase_id=p.id','left')
                                ->join('savor_finance_contract ct on ct.id=p.contract_id','left')
                                ->join('savor_finance_goods g on a.goods_id=g.id','left')
                                ->join('savor_finance_stock st on st.purchase_id=p.id','left')
                                ->field($fields)
                                ->where($where)
                                ->select();
            $total_amount = 0;
            $total_money  = 0;
            foreach($rts as $kk=>$vv){
                $total_amount  +=$vv['total_amount'] ;   
                
                $all_money   = $vv['price'] * $vv['total_amount'];
                $total_money += $all_money;
                
                
                
                
            }
                                
            $result[$key]['total_amount'] = $total_amount;
            $no_rate_total_money = round($total_money / 1.13,2);
            $result[$key]['no_rate_total_money'] = $no_rate_total_money;
            $result[$key]['rate_total_money']    = $total_money - $no_rate_total_money;
            $result[$key]['total_money']  = $total_money;
            
            
            
            
            
            
            
        }
        $count = count($result);
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$result,'page'=>$show);
        
        $this->assign('list', $data['list']);
        $this->assign('page',  $data['page']);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }
}