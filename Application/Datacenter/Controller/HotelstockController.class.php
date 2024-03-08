<?php
namespace Datacenter\Controller;
use Admin\Controller\BaseController;
class HotelstockController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $area_id = I('area_id',0,'intval');
        $category_id = I('category_id',0,'intval');
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            if($v['id']!=246){
                $area_arr[$v['id']]=$v;
            }
        }
        $m_category = new \Admin\Model\CategoryModel();
        $res_category = $m_category->getAll('id,name',array('status'=>1),0,1000,'id asc');
        $category_arr = array();
        foreach ($res_category as $v){
            $category_arr[$v['id']]=$v;
        }
        $where = array();
        if($area_id){
            $where['area_id'] = $area_id;
        }
        if($category_id){
            $where['category_id'] = $category_id;
        }
        if(empty($start_time) || empty($end_time)){
            $start_time = date('Y-m-d',strtotime("-1 month"));
            $end_time = date('Y-m-d');
        }
        $start_time = $start_time>'2024-01-30'?$start_time:'2024-01-30';
        $where['static_date']= array(array('EGT',$start_time),array('ELT',$end_time));


        $start = ($pageNum-1)*$size;
        $m_hotelstock_archivedata = new \Admin\Model\HotelStockArchivedataModel();
        $res_list = $m_hotelstock_archivedata->getDataList('*',$where, 'id desc', $start,$size);
        $data_list = $res_list['list'];

        $this->assign('area_id', $area_id);
        $this->assign('category_id', $category_id);
        $this->assign('area', $area_arr);
        $this->assign('category', $category_arr);
        $this->assign('start_time', $start_time);
        $this->assign('end_time', $end_time);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }
}