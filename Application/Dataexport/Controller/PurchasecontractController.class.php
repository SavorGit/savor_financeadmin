<?php
namespace Dataexport\Controller;
class PurchasecontractController extends BaseController {

    public function datalist(){
        $area_id = I('area_id',0,'intval');
        
        
        $end_date = I('end_date');
        $end_date = !empty($end_date) ? $end_date : date('Y-m-d',strtotime('-1 day'));
        
        $where = [];
        $where['static_date'] = $end_date;
        $m_date_payables = new \Admin\Model\DataPayablesModel();
        $datalist = $m_date_payables->getList('*', $where, 'id asc');
        
        $cell = array(
            array('supplier','供应商名称'),
            array('area_name','城市'),
            array('goods_name','采购商品名称'),
            array('purchase_total_money','采购款总金额'),
            array('have_pay_money','已付款金额'),
            array('not_pay_money','未付款金额'),
            

        );
        $filename = '应付账款明细';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }

}