<?php
namespace Datacenter\Controller;
use Admin\Controller\BaseController;
class PurchasecontractController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
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
            }
        }

        $this->assign('area_id', $area_id);
        $this->assign('area', $area_arr);
        $this->assign('datalist',$datalist);
        $this->assign('page',$result['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

}