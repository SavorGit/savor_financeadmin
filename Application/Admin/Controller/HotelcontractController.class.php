<?php
namespace Admin\Controller;

class HotelcontractController extends BaseController {

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
        $this->contract_ctype_arr   = array(array('id'=>'1','name'=>'主合同'),array('id'=>'2','name'=>'副合同'),);
        $this->status_arr           = $config_contract['contract_status'];
        $this->contract_company_arr = C('CONTRACT_COMPANY');
        $this->oss_host = get_oss_host();
    }
    
    public function datalist() {
    	$sign_start_time = I('sign_start_time','');
    	$sign_end_time = I('sign_end_time','');
    	$area_id = I('area_id',0,'intval');
    	$status = I('status',0,'intval');
    	$renew_templateid = I('renew_templateid',0,'intval');
    	$pay_templateid = I('pay_templateid',0,'intval');
    	$sign_user_id = I('sign_user_id',0,'intval');
    	$is_expire60day = I('is_expire60day',0,'intval');
    	$contractname = I('contractname','');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $where = array('type'=>10);
        if(!empty($sign_start_time) && !empty($sign_end_time)){
            $where['sign_time'] = array(array('egt',$sign_start_time),array('elt',$sign_end_time));
        }
        if($area_id){
            $where['area_id'] = $area_id;
        }
        if($status){
            $where['status'] = $status;
        }
        if($renew_templateid){
            $where['renew_templateid'] = $renew_templateid;
        }
        if($pay_templateid){
            $where['pay_templateids'] = array('like',"%,$pay_templateid,%");
        }
        if($sign_user_id){
            $where['sign_user_id'] = $sign_user_id;
        }
        if($is_expire60day){
            $expire_etime = date('Y-m-d',strtotime("+60 day"));
            $where['contract_etime'] = array('elt',$expire_etime);
        }
        if($contractname){
            $where['name'] = array('like',"%$contractname%");
        }
        $m_signuser = new \Admin\Model\SignuserModel();
        $result_signuser = $m_signuser->getDataList('id,uname as name',array(),'id asc');
        $sign_users = array();
        foreach ($result_signuser as $v){
            $sign_users[$v['id']] = $v;
        }
        $start  = ($page-1) * $size;
        $m_contract  = new \Admin\Model\ContractModel();
        $fields = 'id,serial_number,name,sign_user_id,self_type,sign_time,contract_stime,contract_etime,status,oss_addr,type';
        $result = $m_contract->getDataList($fields,$where,'id desc',$start,$size);

        $datalist = array();
        if(!empty($result['list'])){
            $m_contracthotel = new \Admin\Model\ContracthotelModel();
            $datalist = $result['list'];
            foreach ($datalist as $k=>$v){
                $datalist[$k]['sign_user'] = $sign_users[$v['sign_user_id']]['name'];
                $self_type_str = '主合同';
                if($v['self_type']==2){
                    $self_type_str='副合同';
                }
                $res_hotel_num = $m_contracthotel->getRow('count(id) as num',array('contract_id'=>$v['id']));
                $hotel_num = 0;
                if(!empty($res_hotel_num)){
                    $hotel_num = $res_hotel_num['num'];
                }
                $sign_time = '';
                if($v['sign_time']!='0000-00-00'){
                    $sign_time = $v['sign_time'];
                }
                $expire_time = '';
                if($v['contract_stime']!='0000-00-00' && $v['contract_etime']!='0000-00-00'){
                    $expire_time = $v['contract_stime'].'~'.$v['contract_etime'];
                }

                $datalist[$k]['self_type_str'] = $self_type_str;
                $datalist[$k]['status_str'] = $this->status_arr[$v['status']]['name'];
                $datalist[$k]['hotel_num'] = $hotel_num;
                $datalist[$k]['expire_time'] = $expire_time;
                $datalist[$k]['sign_time'] = $sign_time;
                if(!empty($v['oss_addr'])){
                    $datalist[$k]['oss_addr'] = $this->oss_host.$v['oss_addr'];
                }
            }
        }

        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getHotelAreaList();
        $m_costtemplate  = new \Admin\Model\CosttemplateModel();
        $result_template = $m_costtemplate->getDataList('id,name,type',array(),'id asc');

        $pay_templates = $renew_templates = array();
        foreach ($result_template as $v){
            $tinfo = array('id'=>$v['id'],'name'=>$v['name']);
            if($v['type']==1){
                $pay_templates[]=$tinfo;
            }else{
                $renew_templates[]=$tinfo;
            }
        }
        $this->assign('signuser', $sign_users);
        $this->assign('area', $area_arr);
        $this->assign('area_id', $area_id);
        $this->assign('pay_templates', $pay_templates);
        $this->assign('renew_templates', $renew_templates);
        $this->assign('renew_templateid', $renew_templateid);
        $this->assign('pay_templateid', $pay_templateid);
        $this->assign('sign_user_id', $sign_user_id);
        $this->assign('is_expire60day', $is_expire60day);
        $this->assign('contract_status', $this->status_arr);
        $this->assign('contractname', $contractname);
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
                'sign_user_id'=>array('is_verify'=>1,'tips'=>'请选择签约人'),'self_type'=>array('is_verify'=>1,'tips'=>'请选择合同类型'),
                'area_id'=>array('is_verify'=>1,'tips'=>'请选择签约城市'),'sign_time'=>array('is_verify'=>1,'tips'=>'请输入签约日期'),
                'archive_time'=>array('is_verify'=>1,'tips'=>'请选择归档日期'),'is_inputdevice'=>array('is_verify'=>0,'tips'=>'请选择投入设备'),
                'contract_stime'=>array('is_verify'=>1,'tips'=>'请输入合同有效期'),'contract_etime'=>array('is_verify'=>1,'tips'=>'请输入合同有效期'),
                'hotel_signer'=>array('is_verify'=>1,'tips'=>'请输入合同签约人'),'hotel_signer_phone1'=>array('is_verify'=>1,'tips'=>'请输入电话1'),
                'hotel_signer_phone2'=>array('is_verify'=>0,''),'company_name'=>array('is_verify'=>1,'tips'=>'请输入公司名称'),
                'company_short_name'=>array('is_verify'=>1,'tips'=>'请输入公司简称'),'company_area_id'=>array('is_verify'=>1,'tips'=>'请选择所属城市'),
                'address'=>array('is_verify'=>1,'tips'=>'请输入注册地址'),'company_property'=>array('is_verify'=>1,'tips'=>'请选择企业性质'),
                'invoice_type'=>array('is_verify'=>0,'tips'=>''),'rate'=>array('is_verify'=>0,'tips'=>''),
                'account_name'=>array('is_verify'=>1,'tips'=>'请输入开户名称'),'bank_name'=>array('is_verify'=>1,'tips'=>'请输入开户行名称'),'bank_account'=>array('is_verify'=>1,'tips'=>'请输入账号'),
                'contact1'=>array('is_verify'=>2,'tips'=>'请输入联系人1'),'contact_phone1'=>array('is_verify'=>2,'tips'=>'请输入电话1'),
                'contact_phone12'=>array('is_verify'=>0,'tips'=>''),'contact_qq1'=>array('is_verify'=>0,'tips'=>''),'contact_wechat1'=>array('is_verify'=>0,'tips'=>''),
                'contact2'=>array('is_verify'=>2,'tips'=>'请输入联系人2'),'contact_phone2'=>array('is_verify'=>2,'tips'=>'请输入电话2'),
                'contact_phone22'=>array('is_verify'=>0,'tips'=>''),'contact_qq2'=>array('is_verify'=>0,'tips'=>''),'contact_wechat2'=>array('is_verify'=>0,'tips'=>''),
                'renew_templateid'=>array('is_verify'=>1,'tips'=>'请选择续约条款'),'default_pay_templateid'=>array('is_verify'=>1,'tips'=>'请选择默认付费条款'),
                'pay_templateids'=>array('is_verify'=>0,'tips'=>''),'status'=>array('is_verify'=>0,'tips'=>''),
                'media_id'=>array('is_verify'=>0,'tips'=>'请选择上传文件'),
                'change_content'=>array('is_verify'=>1,'tips'=>'请输入变更内容'),'desc'=>array('is_verify'=>0,'tips'=>'请输入备注'),'remark'=>array('is_verify'=>0,'tips'=>'请输入合同备注')
            );
            $oldis_draft = I('post.oldis_draft',0,'intval');
            if($oldis_draft==1){
                $all_params['change_content']['is_verify']=0;
            }
            $is_draft = I('post.is_draft',0,'intval');
            $add_data = array('type'=>10,'is_draft'=>$is_draft);
            $contract_params = array();
            foreach ($all_params as $k=>$v){
                if(isset($_POST["$k"])){
                    $$k=$_POST["$k"];
                    $add_data["$k"] = $_POST["$k"];
                }
                if($is_draft==0){
                    if($v['is_verify']==1 && empty($_POST["$k"])){
                        $this->output($v['tips'], 'hotelcontract/addcontract', 2, 0);
                    }
                    if($v['is_verify']==2 && !empty($_POST["$k"])){
                        $contract_params["$k"] = $_POST["$k"];
                    }
                }
            }
            if($is_draft==0){
                if(empty($contract_params)){
                    $this->output('请输入联系人1', 'hotelcontract/addcontract', 2, 0);
                }
                $tmp_params = array();
                $info = array();
                if(!empty($contract_params['contact1'])){
                    $info['contact1'] = $contract_params['contact1'];
                }
                if(!empty($contract_params['contact_phone1'])){
                    $info['contact_phone1'] = $contract_params['contact_phone1'];
                }
                $tmp_params['contact1'] = $info;
                $info = array();
                if(!empty($contract_params['contact2'])){
                    $info['contact2'] = $contract_params['contact2'];
                }
                if(!empty($contract_params['contact_phone2'])){
                    $info['contact_phone2'] = $contract_params['contact_phone2'];
                }
                $tmp_params['contact2'] = $info;
                if(count($tmp_params['contact1'])<=1 && count($tmp_params['contact2'])<=1){
                    $this->output('请输入联系人和电话信息', 'hotelcontract/addcontract', 2, 0);
                }
            }

            $all_pay_templateids = $add_data['pay_templateids'];
            if(!empty($all_pay_templateids)){
                foreach ($all_pay_templateids as $k=>$v){
                    if($v==$add_data['default_pay_templateid']){
                        unset($all_pay_templateids[$k]);
                    }
                }
            }
            if(!empty($all_pay_templateids) && $add_data['default_pay_templateid']){
                array_unshift($all_pay_templateids,$add_data['default_pay_templateid']);
            }
            if(!empty($all_pay_templateids)){
                $pay_templateids = join(',',$all_pay_templateids);
                $add_data['pay_templateids'] = ",{$pay_templateids},";
            }else{
                if($add_data['default_pay_templateid']){
                    $add_data['pay_templateids'] = ",{$add_data['default_pay_templateid']},";
                }
            }
            if($add_data['contract_stime'] && $add_data['contract_etime']){
                if($add_data['contract_stime']>$add_data['contract_etime']){
                    $this->output('请输入正确的合同日期', 'hotelcontract/addcontract', 2, 0);
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
            unset($add_data['default_pay_templateid'],$add_data['media_id']);
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
                $this->output('操作成功', 'hotelcontract/datalist');
            }else{
                $this->output('操作失败', 'hotelcontract/addcontract',2,0);
            }
        }else{
            $vinfo = array('self_type'=>1,'status'=>0,'is_draft'=>1);
            $pay_templateids = array();
            if($contract_id){
                $vinfo = $m_contract->getInfo(array('id'=>$contract_id));
                if(!empty($vinfo['oss_addr'])){
                    $vinfo['oss_name'] = $vinfo['name'];
                }
                if(!empty($vinfo['pay_templateids'])){
                    $pay_templateids = explode(',',trim($vinfo['pay_templateids'],','));
                    $vinfo['default_pay_templateid'] = $pay_templateids[0];
                    unset($pay_templateids[0]);
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
            $m_costtemplate  = new \Admin\Model\CosttemplateModel();
            $result_template = $m_costtemplate->getDataList('id,name,content,type',array(),'id asc');
            $pay_templates = $renew_templates = array();
            foreach ($result_template as $v){
                $tinfo = array('id'=>$v['id'],'name'=>$v['name'],'content'=>$v['content'],'select'=>'');
                if($v['type']==1){
                    if(in_array($v['id'],$pay_templateids)){
                        $tinfo['select'] = 'selected';
                    }
                    $pay_templates[]=$tinfo;
                }else{
                    $renew_templates[]=$tinfo;
                }
            }
            $this->assign('vinfo',$vinfo);
            $this->assign('city_arr',$city_arr);
            $this->assign('sign_user_arr',$sign_user_arr);
            $this->assign('company_property_arr',$this->company_property_arr);
            $this->assign('invoice_type_arr',$this->invoice_type_arr);
            $this->assign('contract_company_arr',$this->contract_company_arr);
            $this->assign('contract_ctype_arr',$this->contract_ctype_arr);
            $this->assign('pay_templates',$pay_templates);
            $this->assign('renew_templates',$renew_templates);
            $this->display('addcontract');
        }
    }

    public function detail(){
        $id = I('id',0,'intval');
        $m_history = new \Admin\Model\ContracthistoryModel();
        $vinfo = $m_history->getInfo(array('id'=>$id));
        if(!empty($vinfo['oss_addr'])){
            $vinfo['oss_name'] = $vinfo['name'];
        }
        $m_area = new \Admin\Model\AreaModel();
        $city_arr = $m_area->getHotelAreaList();
        $m_signuser = new \Admin\Model\SignuserModel();
        $sign_user_arr = $m_signuser->getDataList('id,uname',array('status'=>1),'id asc');
        $m_costtemplate  = new \Admin\Model\CosttemplateModel();
        $result_template = $m_costtemplate->getDataList('id,name,content,type',array(),'id asc');
        $all_templates = array();
        foreach ($result_template as $v){
            $all_templates[$v['id']]=$v;
        }

        $renew_templates = '';
        if(!empty($vinfo['renew_templateid'])){
            $renew_templates = $all_templates[$vinfo['renew_templateid']]['content'];
        }

        $pay_templates = array();
        if(!empty($vinfo['pay_templateids'])){
            $pay_templateids = explode(',',trim($vinfo['pay_templateids'],','));
            foreach ($pay_templateids as $k=>$v){
                $name = $all_templates[$v]['name'];
                if($k==0){
                    $name.='(默认)';
                }
                $content = json_decode($all_templates[$v]['content'],true);
                $now_content = '';
                foreach ($content as $cv){
                    $now_content.="开机率 {$cv['min']}-{$cv['max']}:{$cv['cost']}元/屏;";
                }
                $pay_templates[]=array('name'=>$name,'content'=>$now_content);
            }
        }
        $m_contract_hotel = new \Admin\Model\ContracthotelModel();
        $fields = 'hotel.name,hotel.addr,hotel.contractor,hotel.mobile';
        $res_hotels = $m_contract_hotel->getList($fields,array('a.contract_id'=>$vinfo['contract_id']),'a.id desc');
        $contract_hotels = array();
        foreach ($res_hotels as $k=>$v){
            $content = "餐厅名称：{$v['name']}"."\n"."地址：{$v['addr']}"."\n"."负责人：{$v['contractor']} {$v['mobile']}";
            $hinfo = array('location'=>$k+1,'content'=>$content);
            $contract_hotels[]=$hinfo;
        }

        $this->assign('vinfo',$vinfo);
        $this->assign('city_arr',$city_arr);
        $this->assign('sign_user_arr',$sign_user_arr);
        $this->assign('company_property_arr',$this->company_property_arr);
        $this->assign('invoice_type_arr',$this->invoice_type_arr);
        $this->assign('contract_company_arr',$this->contract_company_arr);
        $this->assign('contract_ctype_arr',$this->contract_ctype_arr);
        $this->assign('pay_templates',$pay_templates);
        $this->assign('renew_templates',$renew_templates);
        $this->assign('contract_hotels',$contract_hotels);
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

    public function relationhotel(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $contract_id = I('id',0,'intval');
        $self_type = I('self_type',0,'intval');
        $start = ($page-1)*$size;

        $m_contract_hotel = new \Admin\Model\ContracthotelModel();
        $fields = 'a.id,a.add_time,hotel.id as hotel_id,hotel.name,ext.mac_addr';
        $result = $m_contract_hotel->getList($fields,array('a.contract_id'=>$contract_id),'a.id desc',$start,$size);
        $datalist = $result['list'];
        $m_hotel = new \Admin\Model\HotelModel();
        $m_box = new \Admin\Model\BoxModel();
        $m_tv = new \Admin\Model\TvModel();
        $m_boxcost = new \Admin\Model\BoxcostModel();
        foreach ($datalist as $k=>$v){
            $small_num = 0;
            if($v['mac_addr']!='000000000000'){
                $small_num = 1;
            }
            $boxs = array();
            if($self_type==1){
                $bfields = 'box.id as box_id,box.mac as box_mac,room.id as room_id,room.name as room_name,box.name as box_name';
                $bwhere = array('hotel.id'=>$v['hotel_id'],'box.state'=>1,'box.flag'=>0);
                $boxs = $m_box->getBoxByCondition($bfields,$bwhere);
                foreach ($boxs as $bk=>$bv){
                    $res_tv = $m_tv->getDataList('count(id) as num',array('box_id'=>$bv['box_id'],'state'=>1,'flag'=>0),'id desc');
                    $boxs[$bk]['tv_num'] = $res_tv[0]['num'];
                    $template_name = '';
                    $cost_id = 0;
                    $res_cost = $m_boxcost->getList('a.id as cost_id,template.name',array('a.hotel_id'=>$v['hotel_id'],'a.box_id'=>$bv['box_id']),'a.id desc');
                    if(!empty($res_cost)){
                        $template_name = $res_cost[0]['name'];
                        $cost_id = $res_cost[0]['cost_id'];
                    }
                    $boxs[$bk]['cost_id'] = $cost_id;
                    $boxs[$bk]['template_name'] = $template_name;
                }
            }
            $nums = $m_hotel->getStatisticalNumByHotelId($v['hotel_id']);
            $datalist[$k]['room_num'] = $nums['room_num'];
            $datalist[$k]['box_num'] = $nums['box_num'];
            $datalist[$k]['tv_num'] = $nums['tv_num'];
            $datalist[$k]['small_num'] = $small_num;
            $datalist[$k]['boxs'] = $boxs;
        }

        $this->assign('contract_id', $contract_id);
        $this->assign('self_type', $self_type);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }


    public function addrelationhotel(){
        $contract_id = I('contract_id',0,'intval');
        if(IS_GET){
            $m_hotel = new \Admin\Model\HotelModel();
            $hotel_list = $m_hotel->getDataList('id,name',array('type'=>1,'state'=>1,'flag'=>0),'id desc');
            $this->assign('contract_id',$contract_id);
            $this->assign('hotel_list',$hotel_list);
            $this->display('addrelationhotel');
        }else{
            $hotel_id = I('hotel_id',0,'intval');
            if(empty($hotel_id)){
                $this->output('请选择酒楼', 'hotelcontract/addrelationhotel',2,0);
            }
            $m_contract_hotel = new \Admin\Model\ContracthotelModel();
            $data = array('contract_id'=>$contract_id,'hotel_id'=>$hotel_id);
            $res_hotel = $m_contract_hotel->getInfo($data);
            if(!empty($res_hotel)){
                $this->output('酒楼已关联,请重新选择', 'hotelcontract/addrelationhotel',2,0);
            }
            $m_contract_hotel->add($data);
            $m_contract  = new \Admin\Model\ContractModel();
            $res_contract = $m_contract->getRow('self_type,pay_templateids',array('id'=>$contract_id));
            $m_costtemplate  = new \Admin\Model\CosttemplateModel();
            $result_template = $m_costtemplate->getDataList('id',array('type'=>1,'is_standard'=>1),'id asc');
            $pay_template_standard_id = 0;
            if(!empty($result_template)){
                $pay_template_standard_id=$result_template[0]['id'];
            }
            $pay_template_fid = -1;
            if(!empty($res_contract['pay_templateids'])){
                $pay_templateids = explode(',',trim($res_contract['pay_templateids'],','));
                $pay_template_fid = $pay_templateids[0];
            }

            if($res_contract['self_type']==1 && $pay_template_standard_id && $pay_template_fid==$pay_template_standard_id){
                $bfields = 'box.id as box_id,box.mac as box_mac,room.id as room_id,room.name as room_name,box.name as box_name';
                $bwhere = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
                $m_box = new \Admin\Model\BoxModel();
                $boxs = $m_box->getBoxByCondition($bfields,$bwhere);
                foreach ($boxs as $bk=>$bv){
                    $add_data = array('hotel_id'=>$hotel_id,'room_id'=>$bv['room_id'],'box_id'=>$bv['box_id'],
                        'box_mac'=>$bv['box_mac'],'template_id'=>$pay_template_fid);
                    $m_boxcost = new \Admin\Model\BoxcostModel();
                    $res_cost = $m_boxcost->getInfo(array('hotel_id'=>$hotel_id,'box_id'=>$bv['box_id']));
                    if(!empty($res_cost)){
                        $m_boxcost->updateData(array('id'=>$res_cost['id']),$add_data);
                    }else{
                        $m_boxcost->add($add_data);
                    }
                }
            }

            $this->output('操作成功', 'hotelcontract/relationhotel');
        }
    }

    public function relationhoteldel(){
        $id = I('get.id',0,'intval');
        $hotel_id = I('get.hotel_id',0,'intval');
        $m_contract_hotel = new \Admin\Model\ContracthotelModel();
        $result = $m_contract_hotel->delData(array('id'=>$id));
        if($result){
            $m_boxcost = new \Admin\Model\BoxcostModel();
            $m_boxcost->delData(array('hotel_id'=>$hotel_id));
            $this->output('操作成功!', 'hotelcontract/relationhotel',2);
        }else{
            $this->output('操作失败', 'hotelcontract/relationhotel',2,0);
        }
    }

    public function setboxcost(){
        $contract_id = I('contract_id',0,'intval');
        $hotel_id = I('hotel_id',0,'intval');
        $room_id = I('room_id',0,'intval');
        $box_id = I('box_id',0,'intval');
        $cost_id = I('cost_id',0,'intval');
        $box_mac = I('box_mac','');
        $m_boxcost = new \Admin\Model\BoxcostModel();
        if(IS_GET){
            $m_contract  = new \Admin\Model\ContractModel();
            $result = $m_contract->getInfo(array('id'=>$contract_id));
            $templates = array();
            if(!empty($result['pay_templateids'])){
                $template_id = 0;
                if($cost_id){
                    $res_costinfo = $m_boxcost->getInfo(array('id'=>$cost_id));
                    $template_id = $res_costinfo['template_id'];
                }

                $pay_templateids = explode(',',trim($result['pay_templateids'],','));
                $m_costtemplate  = new \Admin\Model\CosttemplateModel();
                $templates = $m_costtemplate->getDataList('id,name',array('id'=>array('in',$pay_templateids)),'id asc');
                foreach ($templates as $k=>$v){
                    $is_select = '';
                    if($template_id==$v['id']){
                        $is_select = 'selected';
                    }
                    $templates[$k]['select'] = $is_select;
                }
            }
            $this->assign('contract_id',$contract_id);
            $this->assign('hotel_id',$hotel_id);
            $this->assign('room_id',$room_id);
            $this->assign('box_id',$box_id);
            $this->assign('box_mac',$box_mac);
            $this->assign('cost_id',$cost_id);
            $this->assign('templates',$templates);
            $this->display();
        }else{
            $cost_id = I('post.cost_id',0,'intval');
            $template_id = I('post.template_id',0,'intval');
            $add_data = array('hotel_id'=>$hotel_id,'room_id'=>$room_id,'box_id'=>$box_id,
                'box_mac'=>$box_mac,'template_id'=>$template_id);
            if($cost_id){
                $result = $m_boxcost->updateData(array('id'=>$cost_id),$add_data);
            }else{
                $result = $m_boxcost->add($add_data);
            }
            if($result){
                $this->output('操作成功', 'hotelcontract/relationhotel');
            }else{
                $this->output('操作失败', 'hotelcontract/relationhotel',2,0);
            }
        }

    }

    public function relationcontract(){
        $id = I('id',0,'intval');
        $m_contract  = new \Admin\Model\ContractModel();
        if(IS_GET){
            $res = $m_contract->getRow('parent_id',array('id'=>$id));
            $datalist = $m_contract->getDataList('id,serial_number',array('type'=>10,'self_type'=>1),'id desc');
            foreach ($datalist as $k=>$v){
                $is_select = '';
                if($v['id']==$res['parent_id']){
                    $is_select = 'selected';
                }
                $datalist[$k]['is_select'] = $is_select;
            }
            $this->assign('datalist',$datalist);
            $this->assign('id',$id);
            $this->display('relationcontract');
        }else{
            $parent_id = I('post.parent_id',0,'intval');
            $res_data = $m_contract->updateData(array('id'=>$id),array('parent_id'=>$parent_id));
            if($res_data){
                $this->output('操作成功', 'hotelcontract/datalist');
            }else{
                $this->output('操作失败', 'hotelcontract/addcontract',2,0);
            }
        }

    }


}