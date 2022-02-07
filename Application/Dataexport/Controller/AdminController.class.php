<?php
namespace Dataexport\Controller;

class AdminController extends BaseController {

    private $oss_host = '';
    private $company_property_arr = array();
    private $invoice_type_arr = array();
    private $contract_ctype_arr = array();
    private $status_arr = array();
    private $contract_company_arr = array();

    public function __construct() {
        parent::__construct();
        $config_contract = C('FINACE_CONTRACT');
        $this->company_property_arr = $config_contract['company_property'];
        $this->invoice_type_arr     = $config_contract['invoice_type'];
        $this->contract_ctype_arr   = $config_contract['contract_ctype']['administration'];
        $this->status_arr           = $config_contract['contract_status'];
        $this->contract_company_arr = C('CONTRACT_COMPANY');
        $this->oss_host = get_oss_host();
    }
    
    public function datalist() {
    	$sign_start_time = I('sign_start_time','');
    	$sign_end_time = I('sign_end_time','');
    	$area_id = I('area_id',0,'intval');
    	$status = I('status',0,'intval');
    	$ctype = I('ctype',0,'intval');
    	$sign_user_id = I('sign_user_id',0,'intval');
        $where = array('type'=>50);
        if(!empty($sign_start_time) && !empty($sign_end_time)){
            $where['sign_time'] = array(array('egt',$sign_start_time),array('elt',$sign_end_time));
        }
        if($area_id){
            $where['area_id'] = $area_id;
        }
        if($status){
            $where['status'] = $status;
        }
        if($ctype){
            $where['ctype'] = $ctype;
        }
        if($sign_user_id){
            $where['sign_user_id'] = $sign_user_id;
        }
        $m_signuser = new \Admin\Model\SignuserModel();
        $result_signuser = $m_signuser->getDataList('id,uname as name',array('status'=>1),'id asc');
        $sign_users = array();
        foreach ($result_signuser as $v){
            $sign_users[$v['id']] = $v;
        }
        $m_contract  = new \Admin\Model\ContractModel();
        $fields = 'id,serial_number,name,sign_user_id,ctype,sign_time,contract_stime,contract_etime,status,oss_addr,type';
        $datalist = $m_contract->getDataList($fields,$where,'id desc');
        if(!empty($datalist)){
            foreach ($datalist as $k=>$v){
                $sign_time = '';
                if($v['sign_time']!='0000-00-00'){
                    $sign_time = $v['sign_time'];
                }
                $expire_time = '';
                if($v['contract_stime']!='0000-00-00' && $v['contract_etime']!='0000-00-00'){
                    $expire_time = $v['contract_stime'].'~'.$v['contract_etime'];
                }
                $datalist[$k]['sign_user'] = $sign_users[$v['sign_user_id']]['name'];
                $datalist[$k]['ctype_str'] = $this->contract_ctype_arr[$v['ctype']]['name'];
                $datalist[$k]['status_str'] = $this->status_arr[$v['status']]['name'];
                $datalist[$k]['expire_time'] = $expire_time;
                $datalist[$k]['sign_time'] = $sign_time;
            }
        }
        $cell = array(
            array('id','ID'),
            array('serial_number','合同编号'),
            array('name','合同名称'),
            array('sign_user','签约人'),
            array('ctype_str','合同类型'),
            array('sign_time','签约时间'),
            array('expire_time','合同有效期'),
            array('status_str','合作状态')
        );
        $filename = '行政合同列表';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }
}