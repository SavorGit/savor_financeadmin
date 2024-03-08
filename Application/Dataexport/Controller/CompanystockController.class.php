<?php
namespace Dataexport\Controller;
class CompanystockController extends BaseController {

    public function datalist(){
        $area_id = I('area_id',0,'intval');
        $category_id = I('category_id',0,'intval');
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        $where = array();
        if($area_id){
            $where['area_id'] = $area_id;
        }
        if($category_id){
            $where['category_id'] = $category_id;
        }
        $where['static_date']= array(array('EGT',$start_time),array('ELT',$end_time));
        $m_companystock_archivedata = new \Admin\Model\CompanyStockArchivedataModel();
        $datalist = $m_companystock_archivedata->getDataList('*',$where, 'id desc');

        $cell = array(
            array('goods_name','商品名称'),
            array('goods_id','商品编码'),
            array('category_name','商品类型'),
            array('avg_price','商品单价'),
            array('area_id','仓库编码'),
            array('area_name','仓库名称'),
            array('begin_num','期初数量'),
            array('begin_total_fee','期初金额'),
            array('stock_num','库存数量'),
            array('stock_total_fee','库存金额'),
            array('in_num','入库数量'),
            array('in_total_fee','入库金额'),
            array('out_num','出库数量'),
            array('out_total_fee','出库金额'),
            array('static_date','统计日期'),
        );
        $filename = '公司库存管理';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }

}