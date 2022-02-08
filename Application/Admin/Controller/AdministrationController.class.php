<?php
namespace Admin\Controller;

class AdministrationController extends BaseController {

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
    	$contractname = I('contractname','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
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
        if($contractname){
            $where['name'] = array('like',"%$contractname%");
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
        $start  = ($page-1) * $size;
        $m_contract  = new \Admin\Model\ContractModel();
        $fields = 'id,serial_number,name,sign_user_id,ctype,sign_time,contract_stime,contract_etime,status,oss_addr,type';
        $result = $m_contract->getDataList($fields,$where,'id desc',$start,$size);

        $datalist = array();
        if(!empty($result['list'])){
            $datalist = $result['list'];
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
                if(!empty($v['oss_addr'])){
                    $datalist[$k]['oss_addr'] = $this->oss_host.$v['oss_addr'];
                }
            }
        }
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getHotelAreaList();
        $this->assign('signuser', $sign_users);
        $this->assign('ctype', $ctype);
        $this->assign('contractname', $contractname);
        $this->assign('area', $area_arr);
        $this->assign('area_id', $area_id);
        $this->assign('sign_user_id', $sign_user_id);
        $this->assign('contract_status', $this->status_arr);
        $this->assign('contract_ctype_arr', $this->contract_ctype_arr);
        $this->assign('status', $status);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('datalist');
    }

    public function addcontract(){
        $contract_id = I('id',0,'intval');
        $m_contract  = new \Admin\Model\ContractModel();
        if(IS_POST){
            $all_params = array(
                'serial_number'=>array('is_verify'=>1,'tips'=>'请输入合同编号'),'name'=>array('is_verify'=>1,'tips'=>'请输入合同名称'),
                'company_id'=>array('is_verify'=>1,'tips'=>'请选择签约公司'),'sign_department'=>array('is_verify'=>1,'tips'=>'请输入签约部门'),
                'sign_user_id'=>array('is_verify'=>1,'tips'=>'请选择签约人'),'ctype'=>array('is_verify'=>1,'tips'=>'请选择合同类型'),
                'area_id'=>array('is_verify'=>1,'tips'=>'请选择签约城市'),'sign_time'=>array('is_verify'=>1,'tips'=>'请输入签约日期'),
                'archive_time'=>array('is_verify'=>1,'tips'=>'请选择归档日期'),
                'contract_stime'=>array('is_verify'=>1,'tips'=>'请输入合同有效期'),'contract_etime'=>array('is_verify'=>1,'tips'=>'请输入合同有效期'),
                'contract_money'=>array('is_verify'=>1,'tips'=>'请输入金额'),
                'invoice_type'=>array('is_verify'=>1,'tips'=>'请选择发票类型'),'rate'=>array('is_verify'=>1,'tips'=>'请输入税率'),
                'invoice_no'=>array('is_verify'=>1,'tips'=>'请输入发票编号'),
                'change_content'=>array('is_verify'=>1,'tips'=>'请输入变更内容'),'desc'=>array('is_verify'=>0,'tips'=>'请输入备注'),
                'media_id'=>array('is_verify'=>0,'tips'=>'请选择上传文件'),
            );
            $is_draft = I('post.is_draft',0,'intval');
            $oldis_draft = I('post.oldis_draft',0,'intval');
            if($oldis_draft==1){
                $all_params['change_content']['is_verify']=0;
            }
            $add_data = array('type'=>50,'is_draft'=>$is_draft);
            foreach ($all_params as $k=>$v){
                if(isset($_POST["$k"])){
                    $$k=$_POST["$k"];
                    $add_data["$k"] = $_POST["$k"];
                }
                if($is_draft==0 && $v['is_verify']==1 && empty($_POST["$k"])){
                    $this->output($v['tips'], 'administration/addcontract', 2, 0);
                }

            }
            if($add_data['contract_stime'] && $add_data['contract_etime']){
                if($add_data['contract_stime']>$add_data['contract_etime']){
                    $this->output('请输入正确的合同日期', 'administration/addcontract', 2, 0);
                }
                $now_date = date('Y-m-d');
                if($now_date>=$add_data['contract_stime'] && $now_date<$add_data['contract_etime']){
                    $add_data['status']=2;
                }elseif($add_data['contract_stime']>$now_date){
                    $add_data['status']=1;
                }elseif($now_date>$add_data['contract_etime']){
                    $add_data['status']=3;
                }
            }
            if($add_data['media_id']>0){
                $m_media = new \Admin\Model\MediaModel();
                $res_media = $m_media->getMediaInfoById($add_data['media_id']);
                $add_data['oss_addr'] = $res_media['oss_path'];
            }
            $info_invoice = array();
            if(!empty($add_data['invoice_type'])){
                $info_invoice['invoice_type'] = $add_data['invoice_type'];
            }
            if(!empty($add_data['rate'])){
                $info_invoice['rate'] = $add_data['rate'];
            }
            if(!empty($add_data['invoice_no'])){
                $info_invoice['invoice_no'] = $add_data['invoice_no'];
            }
            if(!empty($info_invoice)){
                $add_data['info_invoice'] = json_encode($info_invoice);
            }
            unset($add_data['media_id'],$add_data['invoice_type'],$add_data['rate'],$add_data['invoice_no']);

            $is_update = 0;
            if($contract_id){
                $userInfo = session('sysUserInfo');
                $add_data['sysuser_id'] = $userInfo['id'];
                $add_data['update_time'] = date('Y-m-d H:i:s');
                $m_contract->updateData(array('id'=>$contract_id),$add_data);
                $is_update = 1;
            }else{
                $contract_id = $m_contract->add($add_data);
            }
            if($oldis_draft==0 && $is_draft==0 && $is_update){
                $m_history = new \Admin\Model\ContracthistoryModel();
                $add_data['contract_id'] = $contract_id;
                $m_history->add($add_data);
            }
            if($contract_id){
                $this->output('操作成功', 'administration/datalist');
            }else{
                $this->output('操作失败', 'administration/addcontract',2,0);
            }
        }else{
            $vinfo = array('is_draft'=>1,'status'=>0);
            if($contract_id){
                $vinfo = $m_contract->getInfo(array('id'=>$contract_id));
                if(!empty($vinfo['info_invoice'])){
                    $info_invoice = json_decode($vinfo['info_invoice'],true);

                    if(isset($info_invoice['invoice_type'])){
                        $vinfo['invoice_type'] = $info_invoice['invoice_type'];
                    }
                    if(isset($info_invoice['rate'])){
                        $vinfo['rate'] = $info_invoice['rate'];
                    }
                    if(isset($info_invoice['invoice_no'])){
                        $vinfo['invoice_no'] = $info_invoice['invoice_no'];
                    }
                }
                if($vinfo['sign_time']=='0000-00-00'){
                    $vinfo['sign_time'] = '';
                }
                if($vinfo['archive_time']=='0000-00-00'){
                    $vinfo['archive_time'] = '';
                }
                if($vinfo['contract_stime']=='0000-00-00'){
                    $vinfo['contract_stime'] = '';
                }
                if($vinfo['contract_etime']=='0000-00-00'){
                    $vinfo['contract_etime'] = '';
                }
                $vinfo['change_content'] = '';
                $vinfo['desc'] = '';
                $media_id = 0;
                if(!empty($vinfo['oss_addr'])){
                    $m_media = new \Admin\Model\MediaModel();
                    $res_media = $m_media->getRow('id,name',array('oss_addr'=>$vinfo['oss_addr']),'id desc');
                    $media_id = $res_media['id'];
                    $vinfo['oss_name'] = $res_media['name'];
                }
                $vinfo['media_id'] = $media_id;
            }
            $m_area = new \Admin\Model\AreaModel();
            $city_arr = $m_area->getHotelAreaList();
            $m_signuser = new \Admin\Model\SignuserModel();
            $sign_user_arr = $m_signuser->getDataList('id,uname',array('status'=>1),'id asc');

            $this->assign('vinfo',$vinfo);
            $this->assign('city_arr',$city_arr);
            $this->assign('sign_user_arr',$sign_user_arr);
            $this->assign('company_property_arr',$this->company_property_arr);
            $this->assign('invoice_type_arr',$this->invoice_type_arr);
            $this->assign('contract_company_arr',$this->contract_company_arr);
            $this->assign('contract_ctype_arr',$this->contract_ctype_arr);
            $this->display('addcontract');
        }
    }

    public function detail(){
        $id = I('id',0,'intval');
        $m_history = new \Admin\Model\ContracthistoryModel();
        $vinfo = $m_history->getInfo(array('id'=>$id));
        if(!empty($vinfo['oss_addr'])){
            $m_media = new \Admin\Model\MediaModel();
            $res_media = $m_media->getRow('id,name',array('oss_addr'=>$vinfo['oss_addr']),'id desc');
            $vinfo['oss_name'] = $res_media['name'];
        }
        if(!empty($vinfo['info_invoice'])){
            $info_invoice = json_decode($vinfo['info_invoice'],true);

            if(isset($info_invoice['invoice_type'])){
                $vinfo['invoice_type'] = $info_invoice['invoice_type'];
            }
            if(isset($info_invoice['rate'])){
                $vinfo['rate'] = $info_invoice['rate'];
            }
            if(isset($info_invoice['invoice_no'])){
                $vinfo['invoice_no'] = $info_invoice['invoice_no'];
            }
        }
        $m_area = new \Admin\Model\AreaModel();
        $city_arr = $m_area->getHotelAreaList();
        $m_signuser = new \Admin\Model\SignuserModel();
        $sign_user_arr = $m_signuser->getDataList('id,uname',array('status'=>1),'id asc');

        $this->assign('vinfo',$vinfo);
        $this->assign('city_arr',$city_arr);
        $this->assign('sign_user_arr',$sign_user_arr);
        $this->assign('company_property_arr',$this->company_property_arr);
        $this->assign('invoice_type_arr',$this->invoice_type_arr);
        $this->assign('contract_company_arr',$this->contract_company_arr);
        $this->assign('contract_ctype_arr',$this->contract_ctype_arr);
        $this->assign('vinfo',$vinfo);
        $this->display();
    }

    public function history(){
        $contract_id = I('id',0,'intval');
        $m_contract  = new \Admin\Model\ContractModel();
        $res_contract = $m_contract->getRow('name',array('id'=>$contract_id));

        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数

        $where = array('contract_id'=>$contract_id);
        $start  = ($page-1) * $size;
        $m_costtemplate  = new \Admin\Model\ContracthistoryModel();
        $result = $m_costtemplate->getDataList('*',$where,'id desc',$start,$size);
        $datalist = array();
        if(!empty($result)){
            $datalist = $result['list'];
            $m_sysuser = new \Admin\Model\SysuserModel();
            foreach ($datalist as $k=>$v){
                $res_user = $m_sysuser->getUserInfo($v['sysuser_id']);
                $datalist[$k]['username'] = $res_user['remark'];
                if(!empty($v['oss_addr'])){
                    $datalist[$k]['oss_addr'] = $this->oss_host.$v['oss_addr'];
                }
            }
        }
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('id',$contract_id);
        $this->assign('name',$res_contract['name']);
        $this->display('history');
    }


}