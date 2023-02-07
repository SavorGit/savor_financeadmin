<?php
namespace Dataexport\Controller;
class PurchasecontractController extends BaseController {

    public function datalist(){
        $area_id = I('area_id',0,'intval');

        $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }
        $where = array('type'=>40);
        if($area_id){
            $where['area_id'] = $area_id;
        }
        $pageNum = 1;
        $size = 10000;
        $start  = ($pageNum-1) * $size;
        $m_contract  = new \Admin\Model\ContractModel();
        $fields = 'id,serial_number,name,sign_user_id,self_type,sign_time,status,area_id,contract_money,
        info_money,company_name,account_name,bank_name,bank_account';
        $result = $m_contract->getDataList($fields,$where,'id desc',$start,$size);
        $datalist = array();
        if(!empty($result['list'])){
            $datalist = $result['list'];
            foreach ($datalist as $k=>$v){
                $sign_time = '';
                if($v['sign_time']!='0000-00-00'){
                    $sign_time = $v['sign_time'];
                }
                $contract_money = intval($v['contract_money']);
                $info_money = json_decode($v['info_money'],true);
                $have_pay_money = intval($info_money['have_pay_monye']);
                $no_pay_money = $contract_money-$have_pay_money;

                $datalist[$k]['area_name'] = $area_arr[$v['area_id']]['region_name'];
                $datalist[$k]['sign_time'] = $sign_time;
                $datalist[$k]['contract_money'] = $contract_money;
                $datalist[$k]['have_pay_money'] = $have_pay_money;
                $datalist[$k]['no_pay_money'] = $no_pay_money;
                $datalist[$k]['fk_zh'] = '';
                $datalist[$k]['zyf_money'] = '';
            }
        }
        $cell = array(
            array('company_name','供应商名称'),
            array('sign_time','单据日期'),
            array('contract_money','采购款总金额'),
            array('have_pay_money','已付款金额合计'),
            array('zyf_money','转预付金额'),
            array('no_pay_money','未付款金额'),
            array('fk_zh','付款账户'),
            array('serial_number','采购合同编号'),
            array('area_name','城市'),
            array('account_name','收款单位名称'),
            array('bank_account','收款人账号'),

        );
        $filename = '应付账款明细';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }

}