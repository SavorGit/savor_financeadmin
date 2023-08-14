<?php
namespace Datacenter\Controller;
use Admin\Controller\BaseController;
class SaleController extends BaseController {

    public function residentage(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-30 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->display();
    }

    public function hotelage(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-30 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->display();
    }
}