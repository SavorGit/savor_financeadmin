<?php
namespace Dataexport\Controller;

class SalepaymentController extends BaseController {

    public function datalist(){
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        $where = array();
        if(!empty($start_time) && !empty($end_time)){
            $now_start_time = date('Y-m-d',strtotime($start_time));
            $now_end_time = date('Y-m-d',strtotime($end_time));
            $where['a.pay_time'] = array(array('egt',$now_start_time),array('elt',$now_end_time));
        }

        $m_salepayment = new \Admin\Model\SalePaymentModel();
        $res_list = $m_salepayment->getList('a.*,hotel.name as hotel_name',$where,'a.id desc');
        $m_paymentrecord = new \Admin\Model\SalePaymentRecordModel();
        $data_list = array();
        foreach ($res_list as $v){
            $res_idcodes = $m_paymentrecord->getList('a.*,sale.idcode',array('a.sale_payment_id'=>$v['id']),'a.id desc');
            foreach ($res_idcodes as $iv){
                $data_list[]=array('id'=>$v['id'],'serial_number'=>$v['serial_number'],'hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],
                    'tax_rate'=>$v['tax_rate'],'pay_time'=>$v['pay_time'],'idcode'=>$iv['idcode'],'idcode_pay_money'=>$iv['pay_money']
                );
            }
        }
        $cell = array(
            array('id','ID'),
            array('serial_number','收款单标识码'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('tax_rate','税率(%)'),
            array('idcode_pay_money','收款金额'),
            array('pay_time','收款时间'),
            array('idcode','唯一识别码'),
        );
        $filename = '收款列表';
        $this->exportToExcel($cell,$data_list,$filename,1);


    }
}