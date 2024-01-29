<?php
namespace Datacenter\Controller;
use Admin\Controller\BaseController;
class PurchasecontractController extends BaseController {

    public function datalist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        
        
        $start  = ($page-1) * $size;
        $order = I('_order','a.id');
        
        $this->assign('_order',$order);
        $sort = I('_sort','asc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        
        $where  = [];
        $end_date = I('end_date');
        $end_date = !empty($end_date) ? $end_date :  date('Y-m-d',strtotime('-1 day'));
        $where['a.static_date'] = $end_date;
        
        
        $m_data_payables = new \Admin\Model\DataPayablesModel();
        $list = $m_data_payables->getList('a.*', $where, $orders,$start,$size);
        //echo $m_data_payables->getLastSql();exit;
        //print_r($list);exit;
        $this->assign('end_date', $end_date);
        
        $this->assign('datalist',$list['list']);
        $this->assign('page',$list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$page);
        $this->display();
    }

}