<?php
/**
 *广告销售合同管理控制器
 *
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
use Think\Model;
class AdsaleController extends BaseController{
    
    private $oss_host = '';
	private $company_property_arr = [];
	private $invoice_type_arr    = [];
	private $contract_ctype_arr  = [];
	private $goods_nums = 5;
	private $status_arr = [];
	private $settlement_type_arr = [];
	private $required_arr = array('serial_number'=>'请填写合同编号','name'=>'请填写合同名称','company_id'=>'请选择签约公司',
								  'sign_department'=>'请选择签约部门','sign_user_id'=>'请填写签约人',
								  'area_id'=>'请选择签约城市','sign_time'=>'请选择签署日期','archive_time'=>'请选择归档日期',
								  'contract_money'=>'请填写金额','ctype'=>'请选择合同类型',
								  'hotel_signer'=>'请填写合同签约人',
								  'hotel_signer_phone1'=>'请填写合同签约人电话1',
								  
								  'company_name'=>'请填写公司名称','company_short_name'=>'请填写公司简介','company_area_id'=>'请选择公司所属城市',
								  'address'=>'请填写公司注册地址','account_name'=>'请填写公司开户名称','company_property'=>'请选择公司企业性质',
								  'bank_name'=>'请填写公司开户行名称','bank_account'=>'请填写公司开户账号',
	                              //'contact1'=>'请填写联系人1','contact_phone1'=>'请填写联系人电话1','contact2'=>'请填写联系人2','contact_phone2'=>'请填写联系人电话2',
								  
								  'putin_area_ids'=>'请选择投放城市','putin_hotelnum'=>'请填写投放酒楼数量',
								  'putin_boxnum'=>'请填写投放版位数量','putin_advtime'=>'请填写广告时长','putin_play_frequency'=>'请填写播放频次',
								  
								  
								  'info_invoice_type'=>'请选择发票类型','info_invoice_rate'=>'请填写发票税率','info_invoice_code'=>'请填写发票编号'
								  );
    public function __construct(){
        parent::__construct();
		$config_proxy_sale_contract = C('FINACE_CONTRACT');
		$this->company_property_arr = $config_proxy_sale_contract['company_property'];
		$this->invoice_type_arr     = $config_proxy_sale_contract['invoice_type'];
		$this->contract_ctype_arr   = $config_proxy_sale_contract['contract_ctype']['adsale'];
		$this->status_arr           = $config_proxy_sale_contract['contract_status'];
		$this->settlement_type_arr  = $config_proxy_sale_contract['settlement_type']['advsale'];
		$this->contract_company_arr = C('CONTRACT_COMPANY');
        $this->oss_host = get_oss_host();
		
    }
    public function index(){
		
		$ajaxversion   = I('ajaxversion',0,'intval');//1 版本升级酒店列表
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','a.id');
		
		$this->assign('_order',$order);
		$sort = I('_sort','desc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		
		$start_date = I('start_date','');
		$end_date   = I('end_date','');
		$area_id    = I('area_id',0,'intval');
		$ctype      = I('ctype',0,'intval');
		$status     = I('status',0,'intval');
		$sign_user_id = I('sign_user_id',0,'intval');
		$name       = I('name','','trim');
		$where = [];
		if($name){
			$map1['a.name']= array('like',"%".$name."%");
			$map2['a.purchased_item'] = array('like',"%".$name."%");
			$where['_complex'] = array(
				$map1,
				$map2,
				'_logic' => 'or'
			);
			$this->assign('name',$name);
		}
		
		
		if($start_date){
			$where['a.sign_time']= array('EGT',$start_date);
			$this->assign('start_date',$start_date);
		}
		if($end_date){
			$where['a.sign_time']= array('ELT',$end_date);
			$this->assign('end_date',$end_date);
		}
		if($area_id){
			$where['a.area_id'] = $area_id;
			$this->assign('area_id',$area_id);
		}
		if($ctype){
			$where['a.ctype'] = $ctype;
			$this->assign('ctype',$ctype);
		}
		if($status){
			$now_date = date('Y-m-d');
			if($status==1){
				
				//$s_time>=$now_date
				$where['a.contract_stime'] = array('gt',$now_date);
				$where['a.status'] = array('neq',4);
				
			}else if($status ==2){
				$where['a.contract_stime'] = array('ELT',$now_date);
				$where['a.contract_etime']  = array('GT',$now_date);
				$where['a.status'] = array('neq',4);
			}else if($status==3){
				$where['a.contract_etime'] = array('ELT',$now_date);
				$where['a.status'] = array('neq',4);
			}else if($status==4){
				$where['a.status']=4;
			}
			$this->assign('status',$status);
		}
		if($sign_user_id){
			$where['a.sign_user_id'] = $sign_user_id;
			$this->assign('sign_user_id',$sign_user_id);
		}
		
		$where['a.type'] = 30;
		$m_contract = new \Admin\Model\ContractModel();
		$fileds = "a.*,b.uname";
		
		$result = $m_contract->getList($fileds,$where, $orders, $start,$size);
		
		//print_r($this->contract_ctype_arr);exit;
		$m_contract_hotel = new \Admin\Model\ContracthotelModel();
		
		foreach($result['list'] as $key=>$v){
			$nums = $m_contract_hotel->where(array('contract_id'=>$v['id']))->count();
			$result['list'][$key]['contract_hotel_nums'] = $nums;
			
		}
		//城市
		
		$m_area = new \Admin\Model\AreaModel();
		$city_arr = $m_area->getHotelAreaList();
		
		$m_signuser = new \Admin\Model\SignuserModel();
		$sign_user_arr = $m_signuser->field('id,uname')->where('status=1')->select();
		$this->assign('sign_user_arr',$sign_user_arr);
		$this->assign('city_arr',$city_arr);
		$this->assign('status_arr',$this->status_arr);
		$this->assign('contract_ctype_arr',$this->contract_ctype_arr);
		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
        $this->display('index');
    }
    public function add(){
		
		$m_area = new \Admin\Model\AreaModel();
		$city_arr = $m_area->getHotelAreaList();
		$m_signuser = new \Admin\Model\SignuserModel();
		$sign_user_arr = $m_signuser->field('id,uname')->where('status=1')->select();
		
		//print_r($this->contract_ctype_arr);exit;
		
		$this->assign('city_arr',$city_arr);
		$this->assign('sign_user_arr',$sign_user_arr);
		$this->assign('company_property_arr',$this->company_property_arr);
		$this->assign('invoice_type_arr',$this->invoice_type_arr);
		$this->assign('contract_company_arr',$this->contract_company_arr);
		$this->assign('contract_ctype_arr',$this->contract_ctype_arr);
		$this->assign('settlement_type_arr',$this->settlement_type_arr);
        $this->display('add');
    }
    public function doadd(){
		
		if(IS_POST){
			$userinfo = session('sysUserInfo');
			$data =  [];
			$is_draft      				 = I('post.is_draft',0,'intval');               //是否保存草稿
			if($is_draft==0){
				foreach($this->required_arr as $key=>$v){
					$tmp = I('post.'.$key);
					if(empty($tmp)){
						$this->error($v);
						break;
					}
					
				}
				//联系人1  联系人2
				$contract_params["contact1"] = I('post.contact1','','trim');
				$contract_params["contact_phone1"] = I('post.contact_phone1','','trim');
				$contract_params["contact2"] = I('post.contact2','','trim');
				$contract_params["contact_phone2"] = I('post.contact_phone2','','trim');
				if(empty($contract_params)){
				    $this->error('请输入联系人1');
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
				    $this->error('请输入联系人和电话信息');
				}
				
			}
			
			
			$data['serial_number']   	 = I('post.serial_number','','trim');     		//合同编号
			$data['name']   	         = I('post.name','','trim');     		        //合同名称
			$data['company_id']      	 = I('post.company_id',0,'intval');       		//签约公司          
			$data['sign_department'] 	 = I('post.sign_department','','trim');   		//签约部门
			$data['sign_user_id']    	 = I('post.sign_user_id',0,'intval');     		//签约人
			$data['area_id']         	 = I('post.area_id',0,'intval');            	//签约城市
			$data['sign_time']       	 = I('post.sign_time','','trim');               //签署日期
			$data['archive_time']    	 = I('post.archive_time','','trim');            //归档日期
			$data['contract_money']      = I('post.contract_money','','trim');          //金额
			$data['ctype']           	 = I('post.ctype',0,'intval');                  //合同类型
			
			$data['contract_stime']  	 = I('post.contract_stime','','trim');          //合同开始日期
			$data['contract_etime']      = I('post.contract_etime','','trim');          //合同结束日期
			$data['hotel_signer']        = I('post.hotel_signer','','trim');            //合同签约人
			$data['hotel_signer_phone1'] = I('post.hotel_signer_phone1','','trim');     //合同签约人电话1
			$data['hotel_signer_phone2'] = I('post.hotel_signer_phone2','','trim');     //合同签约人电话2
			$data['change_content']      = I('post.change_content','','trim');          //变更内容
			$data['desc']                = I('post.desc','','trim');                    //变更备注
			
			
			
			$data['company_name']        = I('post.company_name','','trim');            //公司名称
			$data['company_short_name']  = I('post.company_short_name','','trim');      //公司简介
			$data['company_area_id']     = I('post.company_area_id','','trim');         //公司所属城市
			$data['address']             = I('post.address','','trim');                 //公司注册地址
			$data['company_property']    = I('post.company_property','','trim');        //公司企业性质
			$data['invoice_type']    	 = I('post.invoice_type',0,'intval');     		//公司发票类型
			$data['rate']                = I('post.rate','','trim');                    //公司税率
			//$data['invoice_code']        = I('post.invoice_code','','trim');            //发票编号
			$data['account_name']        = I('post.account_name','','trim');            //公司开户名称
			$data['bank_name']           = I('post.bank_name','','trim');               //公司开户行名称
			$data['bank_account']        = I('post.bank_account','','trim');            //公司开户行账号
			$data['contact1']            = I('post.contact1','','trim');                //联系人1
			$data['contact_phone1']      = I('post.contact_phone1','','trim');          //电话1
			$data['contact_phone12']     = I('post.contact_phone12','','trim');         //电话2
			$data['contact_qq1']         = I('post.contact_qq1','','trim');             //qq
			$data['contact_wechat1']     = I('post.contact_wechat1','','trim');         //微信
			$data['contact2']            = I('post.contact2','','trim');                //联系人2
			$data['contact_phone2']      = I('post.contact_phone2','','trim');          //电话1
			$data['contact_phone22']     = I('post.contact_phone22','','trim');         //电话2
			$data['contact_qq2']         = I('post.contact_qq2','','trim');             //qq
			$data['contact_wechat2']     = I('post.contact_wechat2','','trim');         //微信
			$data['type']                = 30;
			$data['sysuser_id']          = $userinfo['id'];
			$data['oss_addr']            = I('post.oss_addr');
			
			
			$putin_area_ids              = I('post.putin_area_ids','');                 //投放城市
			if(!empty($putin_area_ids)){
				$putin_area_ids_str = $space = "";
				foreach($putin_area_ids as $v){
					$putin_area_ids_str .= $space .$v;
					$space = ',';
				}
				$data['putin_area_ids'] = $putin_area_ids_str;
			}
			$data['putin_hotelnum']      = I('post.putin_hotelnum','','trim');           //投放酒楼数量
			$data['putin_boxnum']        = I('post.putin_boxnum','','trim');             //投放版位数量
			$data['putin_advtime']       = I('post.putin_advtime','','trim');            //广告时长
			$data['putin_play_frequency']= I('post.putin_play_frequency','','trim');     //播放频次
			$data['putin_playnum']       = I('post.putin_playnum','','trim');            //播放总次数
			
			
			$goods_name1                  = I('post.goods_name1');                              //商品名称
			$goods_number1                = I('post.goods_number1');                            //数量
			
			$goods_name2                  = I('post.goods_name2');                              //商品名称
			$goods_number2                = I('post.goods_number2');                            //数量
			
			$goods_name3                 = I('post.goods_name3');                               //商品名称
			$goods_number3                = I('post.goods_number3');                            //数量
			
			
			/*
			$data['info_goods'] = json_encode($info_goods);*/
			//结算信息
			$data['settlement_type'] = I('post.settlement_type',0,'intval');         //结算方式  1现金2易货3现金+易货
			if($data['settlement_type']<=1){//现金
				$prepayment              = I('post.prepayment','','trim');           //预付款金额
				$prepayment_time         = I('post.prepayment_time','','trim');      //预付款日期 
				$medium_payment          = I('post.medium_payment','','trim');       //中期结款金额
				$medium_payment_time     = I('post.medium_payment_time','','trim');  //中期结款付款日期
				$tail_prepayment         = I('post.tail_prepayment','','trim');      //尾款金额
				$tail_prepayment_time    = I('post.tail_prepayment_time','','trim'); //尾款结款日期
				$f_have_pay_monye        = I('post.f_have_pay_monye','','trim');     //已付款金额
				$f_no_pay_monye          = I('post.f_no_pay_monye','','trim');       //未付款金额
				$info_money['prepayment']           = $prepayment;
				$info_money['prepayment_time']      = $prepayment_time;
				$info_money['medium_payment']       = $medium_payment;
				$info_money['medium_payment_time']  = $medium_payment_time;
				$info_money['tail_prepayment']      = $tail_prepayment;
				$info_money['tail_prepayment_time'] = $tail_prepayment_time;
				$info_money['f_have_pay_monye']     = $f_have_pay_monye;
				$info_money['f_no_pay_monye']       = $f_no_pay_monye;
				$data['info_money'] = json_encode($info_money);
			}else if($data['settlement_type']==2){//易货
				$info_goods = [];
				for($i = 0; $i<$this->goods_nums;$i++){
					
					$info_goods['goods_1']['goods_name'][] =  $goods_name1[$i];
					
					$info_goods['goods_1']['goods_number'][] =  $goods_number1[$i];
				}
				for($i =0;$i<$this->goods_nums;$i++){
					
					$info_goods['goods_2']['goods_name'][] =  $goods_name2[$i];
					
					$info_goods['goods_2']['goods_number'][] =  $goods_number2[$i];
				}
				for($i = 0;$i<$this->goods_nums;$i++){
					
					$info_goods['goods_3']['goods_name'][] =  $goods_name3[$i];
					
					$info_goods['goods_3']['goods_number'][] =  $goods_number3[$i];
				}
				$data['info_goods'] = json_encode($info_goods);
			}else if($data['settlement_type']==3){//现金+易货
				$prepayment              = I('post.prepayment','','trim');           //预付款金额
				$prepayment_time         = I('post.prepayment_time','','trim');      //预付款日期 
				$medium_payment          = I('post.medium_payment','','trim');       //中期结款金额
				$medium_payment_time     = I('post.medium_payment_time','','trim');  //中期结款付款日期
				$tail_prepayment         = I('post.tail_prepayment','','trim');      //尾款金额
				$tail_prepayment_time    = I('post.tail_prepayment_time','','trim'); //尾款结款日期
				$f_have_pay_monye        = I('post.f_have_pay_monye','','trim');     //已付款金额
				$f_no_pay_monye          = I('post.f_no_pay_monye','','trim');       //未付款金额
				$info_money['prepayment']           = $prepayment;
				$info_money['prepayment_time']      = $prepayment_time;
				$info_money['medium_payment']       = $medium_payment;
				$info_money['medium_payment_time']  = $medium_payment_time;
				$info_money['tail_prepayment']      = $tail_prepayment;
				$info_money['tail_prepayment_time'] = $tail_prepayment_time;
				$info_money['f_have_pay_monye']     = $f_have_pay_monye;
				$info_money['f_no_pay_monye']       = $f_no_pay_monye;
				$data['info_money'] = json_encode($info_money);
				
				$info_goods = [];
				for($i = 0; $i<$this->goods_nums;$i++){
					
					$info_goods['goods_1']['goods_name'][] =  $goods_name1[$i];
					
					$info_goods['goods_1']['goods_number'][] =  $goods_number1[$i];
				}
				for($i =0;$i<$this->goods_nums;$i++){
					
					$info_goods['goods_2']['goods_name'][] =  $goods_name2[$i];
					
					$info_goods['goods_2']['goods_number'][] =  $goods_number2[$i];
				}
				for($i = 0;$i<$this->goods_nums;$i++){
					
					$info_goods['goods_3']['goods_name'][] =  $goods_name3[$i];
					
					$info_goods['goods_3']['goods_number'][] =  $goods_number3[$i];
				}
				$data['info_goods'] = json_encode($info_goods);
			}
			//发票信息
			$info_invoice_type = I('post.info_invoice_type','','trim');
			$info_invoice_rate = I('post.info_invoice_rate','','trim');
			$info_invoice_code = I('post.info_invoice_code','','trim');
			$info_invoice['info_invoice_type'] = $info_invoice_type;
			$info_invoice['info_invoice_rate'] = $info_invoice_rate;
			$info_invoice['info_invoice_code'] = $info_invoice_code;
			$data['info_invoice'] = json_encode($info_invoice);
			
			
			
			$data['update_time'] = date('Y-m-d H:i:s');
			
			$s_time  = strtotime($data['contract_stime']." 00:00:00");
			$e_time  = strtotime($data['contract_etime']." 23:59:59");
			
			$now_date  = time();
			
			 if($s_time>=$now_date ){ //待生效
				$data['status'] = 2;
			}else if($s_time<$now_date && $e_time>now_date){//进行中
				$data['status'] = 1;
			}else if($e_time<=$now_date){//已到期
				$data['status'] = 3;
			}
			$media_id = I('post.media_id',0,'intval');
			if($media_id){
				$m_media = new \Admin\Model\MediaModel();
				$media_info = $m_media->where('id='.$media_id)->find();
				$data['oss_addr'] = $media_info['oss_addr'];
			}
			
			
			$m_contract = new \Admin\Model\ContractModel();
			if($is_draft==1){//保存草稿
				$ret  = $m_contract->addData($data);
				
				if($ret){
					$this->output('添加成功!', 'adsale/index');
				}else{
					$this->error('添加失败');
				}
			}else{
				$ret  = $m_contract->addData($data);
				if($ret){
					$data['contract_id'] = $ret;
					$m_contract_history = new \Admin\Model\ContracthistoryModel();
					$rts = $m_contract_history->addData($data);
					$this->output('添加成功!', 'adsale/index');
				}else{
					$this->error('添加失败');
				}
				
			}
		} 
	}
	public function edit(){
		
		$id = I('get.id',0,'intval');
		
		$m_area = new \Admin\Model\AreaModel();
		$city_arr = $m_area->getHotelAreaList();
		$m_signuser = new \Admin\Model\SignuserModel();
		$sign_user_arr = $m_signuser->field('id,uname')->where('status=1')->select();
		
		
		$m_contract = new \Admin\Model\ContractModel();
		$vinfo = $m_contract->where('id='.$id)->find();
		if($vinfo['sign_time']=='0000-00-00')       $vinfo['sign_time'] = '';
		if($vinfo['archive_time']=='0000-00-00')    $vinfo['archive_time'] = '';
		if($vinfo['contract_stime']=='0000-00-00')  $vinfo['contract_stime'] = '';
		if($vinfo['contract_etime']=='0000-00-00')  $vinfo['contract_etime'] = '';
		if($vinfo['statement_time']=='0000-00-00')  $vinfo['statement_time'] = '';
		if($vinfo['settlement_time']=='0000-00-00') $vinfo['settlement_time'] = '';
		
		
		
		$info_goods   = json_decode($vinfo['info_goods'],true);
		
		$info_money   = json_decode($vinfo['info_money'],true);
		$info_invoice = json_decode($vinfo['info_invoice'],true);
		
		//投放城市
		$putin_area_ids = $vinfo['putin_area_ids'];
		if(!empty($putin_area_ids)){
			$putin_area_id_arr = explode(',',$putin_area_ids);
			$city_id_arr = [];
			foreach($city_arr as $key=>$v){
				if(in_array($v['id'],$putin_area_id_arr)){
					$city_arr[$key]['select'] = 'selected';
				}
			}
			
			
		}
		$media_id = 0;
		if(!empty($vinfo['oss_addr'])){
			$m_media = new \Admin\Model\MediaModel();
			$res_media = $m_media->getRow('id,name',array('oss_addr'=>$vinfo['oss_addr']),'id desc');
			$media_id = $res_media['id'];
			$vinfo['oss_name'] = $res_media['name'];
		}
		$vinfo['media_id'] = $media_id;
		
		
		
		$this->assign('vinfo',$vinfo);
		//print_r($info_goods);exit;
		$this->assign('info_goods',$info_goods);
		$this->assign('info_money',$info_money);
		$this->assign('info_invoice',$info_invoice);
		$this->assign('city_arr',$city_arr);
		$this->assign('sign_user_arr',$sign_user_arr);
		$this->assign('company_property_arr',$this->company_property_arr);
		$this->assign('invoice_type_arr',$this->invoice_type_arr);
		$this->assign('contract_company_arr',$this->contract_company_arr);
		$this->assign('contract_ctype_arr',$this->contract_ctype_arr);
		
		$this->assign('settlement_type_arr',$this->settlement_type_arr);
		
        $this->display('edit');
	}
	public function doedit(){
		$id = I('post.id',0,'intval');
		$m_contract = new \Admin\Model\ContractModel();
		$vinfo = $m_contract->where('id='.$id)->find();
		if(empty($vinfo)){
			$this->error('该合同不存在或已删除');
		}
		
		if(IS_POST){
			$userinfo = session('sysUserInfo');
			$data =  [];
			$is_draft      				 = I('post.is_draft',0,'intval');               //是否保存草稿
			if($is_draft==0){
				foreach($this->required_arr as $key=>$v){
					$tmp = I('post.'.$key);
					if(empty($tmp)){
						$this->error($v);
						break;
					}
				}
				$change_content = I('post.change_content','','trim');
				if(empty($change_content)){
				    $this->error('请输入变更内容');
				}
				
				//联系人1  联系人2
				$contract_params["contact1"] = I('post.contact1','','trim');
				$contract_params["contact_phone1"] = I('post.contact_phone1','','trim');
				$contract_params["contact2"] = I('post.contact2','','trim');
				$contract_params["contact_phone2"] = I('post.contact_phone2','','trim');
				if(empty($contract_params)){
				    $this->error('请输入联系人1');
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
				    $this->error('请输入联系人和电话信息');
				}
			}
			$data['serial_number']   	 = I('post.serial_number','','trim');     		//合同编号
			$data['name']   	         = I('post.name','','trim');     		        //合同名称
			$data['company_id']      	 = I('post.company_id',0,'intval');       		//签约公司          
			$data['sign_department'] 	 = I('post.sign_department','','trim');   		//签约部门
			$data['sign_user_id']    	 = I('post.sign_user_id',0,'intval');     		//签约人
			$data['area_id']         	 = I('post.area_id',0,'intval');            	//签约城市
			$data['sign_time']       	 = I('post.sign_time','','trim');               //签署日期
			$data['archive_time']    	 = I('post.archive_time','','trim');            //归档日期
			$data['contract_money']      = I('post.contract_money','','trim');          //金额
			$data['ctype']           	 = I('post.ctype',0,'intval');                  //合同类型
			
			$data['contract_stime']  	 = I('post.contract_stime','','trim');          //合同开始日期
			$data['contract_etime']      = I('post.contract_etime','','trim');          //合同结束日期
			$data['hotel_signer']        = I('post.hotel_signer','','trim');            //合同签约人
			$data['hotel_signer_phone1'] = I('post.hotel_signer_phone1','','trim');     //合同签约人电话1
			$data['hotel_signer_phone2'] = I('post.hotel_signer_phone2','','trim');     //合同签约人电话2
			$data['change_content']      = I('post.change_content','','trim');          //变更内容
			$data['desc']                = I('post.desc','','trim');                    //变更备注
			
			
			
			$data['company_name']        = I('post.company_name','','trim');            //公司名称
			$data['company_short_name']  = I('post.company_short_name','','trim');      //公司简介
			$data['company_area_id']     = I('post.company_area_id','','trim');         //公司所属城市
			$data['address']             = I('post.address','','trim');                 //公司注册地址
			$data['company_property']    = I('post.company_property','','trim');        //公司企业性质
			$data['invoice_type']    	 = I('post.invoice_type',0,'intval');     		//公司发票类型
			$data['rate']                = I('post.rate','','trim');                    //公司税率
			//$data['invoice_code']        = I('post.invoice_code','','trim');            //发票编号
			$data['account_name']        = I('post.account_name','','trim');            //公司开户名称
			$data['bank_name']           = I('post.bank_name','','trim');               //公司开户行名称
			$data['bank_account']        = I('post.bank_account','','trim');            //公司开户行账号
			$data['contact1']            = I('post.contact1','','trim');                //联系人1
			$data['contact_phone1']      = I('post.contact_phone1','','trim');          //电话1
			$data['contact_phone12']     = I('post.contact_phone12','','trim');         //电话2
			$data['contact_qq1']         = I('post.contact_qq1','','trim');             //qq
			$data['contact_wechat1']     = I('post.contact_wechat1','','trim');         //微信
			$data['contact2']            = I('post.contact2','','trim');                //联系人2
			$data['contact_phone2']      = I('post.contact_phone2','','trim');          //电话1
			$data['contact_phone22']     = I('post.contact_phone22','','trim');         //电话2
			$data['contact_qq2']         = I('post.contact_qq2','','trim');             //qq
			$data['contact_wechat2']     = I('post.contact_wechat2','','trim');         //微信
			$data['type']                = 30;
			$data['sysuser_id']          = $userinfo['id'];
			$data['oss_addr']            = I('post.oss_addr');
			
			
			$putin_area_ids              = I('post.putin_area_ids','');                 //投放城市
			if(!empty($putin_area_ids)){
				$putin_area_ids_str = $space = "";
				foreach($putin_area_ids as $v){
					$putin_area_ids_str .= $space .$v;
					$space = ',';
				}
				$data['putin_area_ids'] = $putin_area_ids_str;
			}
			$data['putin_hotelnum']      = I('post.putin_hotelnum','','trim');           //投放酒楼数量
			$data['putin_boxnum']        = I('post.putin_boxnum','','trim');             //投放版位数量
			$data['putin_advtime']       = I('post.putin_advtime','','trim');            //广告时长
			$data['putin_play_frequency']= I('post.putin_play_frequency','','trim');     //播放频次
			$data['putin_playnum']       = I('post.putin_playnum','','trim');            //播放总次数
			
			$goods_name1                  = I('post.goods_name1');                              //第一批商品名称
			$goods_number1                = I('post.goods_number1');                            //第一批数量
			
			$goods_name2                  = I('post.goods_name2');                              //第二批商品名称
			$goods_number2                = I('post.goods_number2');                            //第二批数量
			
			$goods_name3                 = I('post.goods_name3');                               //第三批商品名称
			$goods_number3                = I('post.goods_number3');                            //第三批数量
			
			
			/*
			$data['info_goods'] = json_encode($info_goods);*/
			//结算信息
			$data['settlement_type'] = I('post.settlement_type',0,'intval');         //结算方式  1现金2易货3现金+易货
			if($data['settlement_type']<=1){//现金
				$prepayment              = I('post.prepayment','','trim');           //预付款金额
				$prepayment_time         = I('post.prepayment_time','','trim');      //预付款日期 
				$medium_payment          = I('post.medium_payment','','trim');       //中期结款金额
				$medium_payment_time     = I('post.medium_payment_time','','trim');  //中期结款付款日期
				$tail_prepayment         = I('post.tail_prepayment','','trim');      //尾款金额
				$tail_prepayment_time    = I('post.tail_prepayment_time','','trim'); //尾款结款日期
				$f_have_pay_monye        = I('post.f_have_pay_monye','','trim');     //已付款金额
				$f_no_pay_monye          = I('post.f_no_pay_monye','','trim');       //未付款金额
				$info_money['prepayment']           = $prepayment;
				$info_money['prepayment_time']      = $prepayment_time;
				$info_money['medium_payment']       = $medium_payment;
				$info_money['medium_payment_time']  = $medium_payment_time;
				$info_money['tail_prepayment']      = $tail_prepayment;
				$info_money['tail_prepayment_time'] = $tail_prepayment_time;
				$info_money['f_have_pay_monye']     = $f_have_pay_monye;
				$info_money['f_no_pay_monye']       = $f_no_pay_monye;
				$data['info_money'] = json_encode($info_money);
			}else if($data['settlement_type']==2){//易货
				$info_goods = [];
				for($i = 0; $i<$this->goods_nums;$i++){
					
					$info_goods['goods_1']['goods_name'][] =  $goods_name1[$i];
					
					$info_goods['goods_1']['goods_number'][] =  $goods_number1[$i];
				}
				for($i =0;$i<$this->goods_nums;$i++){
					
					$info_goods['goods_2']['goods_name'][] =  $goods_name2[$i];
					
					$info_goods['goods_2']['goods_number'][] =  $goods_number2[$i];
				}
				for($i = 0;$i<$this->goods_nums;$i++){
					
					$info_goods['goods_3']['goods_name'][] =  $goods_name3[$i];
					
					$info_goods['goods_3']['goods_number'][] =  $goods_number3[$i];
				}
				$data['info_goods'] = json_encode($info_goods);
			}else if($data['settlement_type']==3){//现金+易货
				$prepayment              = I('post.prepayment','','trim');           //预付款金额
				$prepayment_time         = I('post.prepayment_time','','trim');      //预付款日期 
				$medium_payment          = I('post.medium_payment','','trim');       //中期结款金额
				$medium_payment_time     = I('post.medium_payment_time','','trim');  //中期结款付款日期
				$tail_prepayment         = I('post.tail_prepayment','','trim');      //尾款金额
				$tail_prepayment_time    = I('post.tail_prepayment_time','','trim'); //尾款结款日期
				$f_have_pay_monye        = I('post.f_have_pay_monye','','trim');     //已付款金额
				$f_no_pay_monye          = I('post.f_no_pay_monye','','trim');       //未付款金额
				$info_money['prepayment']           = $prepayment;
				$info_money['prepayment_time']      = $prepayment_time;
				$info_money['medium_payment']       = $medium_payment;
				$info_money['medium_payment_time']  = $medium_payment_time;
				$info_money['tail_prepayment']      = $tail_prepayment;
				$info_money['tail_prepayment_time'] = $tail_prepayment_time;
				$info_money['f_have_pay_monye']     = $f_have_pay_monye;
				$info_money['f_no_pay_monye']       = $f_no_pay_monye;
				$data['info_money'] = json_encode($info_money);
				
				$info_goods = [];
				for($i = 0; $i<$this->goods_nums;$i++){
					
					$info_goods['goods_1']['goods_name'][] =  $goods_name1[$i];
					
					$info_goods['goods_1']['goods_number'][] =  $goods_number1[$i];
				}
				for($i =0;$i<$this->goods_nums;$i++){
					
					$info_goods['goods_2']['goods_name'][] =  $goods_name2[$i];
					
					$info_goods['goods_2']['goods_number'][] =  $goods_number2[$i];
				}
				for($i = 0;$i<$this->goods_nums;$i++){
					
					$info_goods['goods_3']['goods_name'][] =  $goods_name3[$i];
					
					$info_goods['goods_3']['goods_number'][] =  $goods_number3[$i];
				}
				$data['info_goods'] = json_encode($info_goods);
			}
			//发票信息
			$info_invoice_type = I('post.info_invoice_type','','trim');
			$info_invoice_rate = I('post.info_invoice_rate','','trim');
			$info_invoice_code = I('post.info_invoice_code','','trim');
			$info_invoice['info_invoice_type'] = $info_invoice_type;
			$info_invoice['info_invoice_rate'] = $info_invoice_rate;
			$info_invoice['info_invoice_code'] = $info_invoice_code;
			
			
			$data['info_invoice'] = json_encode($info_invoice);
			
			
			$data['update_time'] = date('Y-m-d H:i:s');
			
			$s_time  = strtotime($data['contract_stime']." 00:00:00");
			$e_time  = strtotime($data['contract_etime']." 23:59:59");
			
			$now_date  = time();
			if($s_time>=$now_date){ //待生效
				
				$data['status'] = 2;
			}else if($s_time<$now_date && $e_time>now_date){//进行中
				
				$data['status'] = 1;
			}else if($e_time<=$now_date){//已到期
				
				$data['status'] = 3;
			}
			
			$is_stop = I('post.is_stop',0);
			
			if($is_stop==1){
				$data['status']= 4;
			}
			$media_id = I('post.media_id',0,'intval');
			
			if($media_id){
				$m_media = new \Admin\Model\MediaModel();
				$media_info = $m_media->field('oss_addr')->where('id='.$media_id)->find();
				
				$data['oss_addr'] = $media_info['oss_addr'];
			}
			
			$m_contract = new \Admin\Model\ContractModel();
			if($is_draft==1){//保存草稿
				$ret  = $m_contract->updateData(array('id'=>$id),$data);
				
				if($ret){
					$this->output('编辑成功!', 'adsale/index');
				}else{
					$this->error('编辑失败');
				}
			}else{
				
				$ret  = $m_contract->updateData(array('id'=>$id),$data);
				if($ret){
					$m_contract_history = new \Admin\Model\ContracthistoryModel();
					$data['contract_id'] = $id;
					$rts = $m_contract_history->addData($data);
					$this->output('编辑成功!', 'adsale/index');
				}else{
					$this->error('编辑失败');
				}
			}
		}
	}
	
	public function logs(){
		
		$ajaxversion   = I('ajaxversion',0,'intval');//1 版本升级酒店列表
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','a.id');
		
		$this->assign('_order',$order);
		$sort = I('_sort','desc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		
		$contract_id = I('get.contract_id',0,'intval');
		
		$m_contract_history = new \Admin\Model\ContracthistoryModel();
		$where = [];
		$where['a.contract_id'] = $contract_id;
		$where['a.type'] = 30;
		$fields = "a.*,b.uname,c.remark";
		$result = $m_contract_history->getList($fields,$where, $orders, $start,$size);
		
		$this->assign('list',$result['list']);
		$this->assign('page',$result['page']);
		$this->display('logs');
	}
	public function logdetail(){
		$id = I('get.id',0,'intval');
		
		$m_area = new \Admin\Model\AreaModel();
		$city_arr = $m_area->getHotelAreaList();
		$m_signuser = new \Admin\Model\SignuserModel();
		$sign_user_arr = $m_signuser->field('id,uname')->where('status=1')->select();
		
		
		$m_contract = new \Admin\Model\ContracthistoryModel();
		$vinfo = $m_contract->where('id='.$id)->find();
		if($vinfo['sign_time']=='0000-00-00')       $vinfo['sign_time'] = '';
		if($vinfo['archive_time']=='0000-00-00')    $vinfo['archive_time'] = '';
		if($vinfo['contract_stime']=='0000-00-00')  $vinfo['contract_stime'] = '';
		if($vinfo['contract_etime']=='0000-00-00')  $vinfo['contract_etime'] = '';
		if($vinfo['statement_time']=='0000-00-00')  $vinfo['statement_time'] = '';
		if($vinfo['settlement_time']=='0000-00-00') $vinfo['settlement_time'] = '';
		
		
		
		$info_goods   = json_decode($vinfo['info_goods'],true);
		
		$info_money   = json_decode($vinfo['info_money'],true);
		$info_invoice = json_decode($vinfo['info_invoice'],true);
		
		//投放城市
		$putin_area_ids = $vinfo['putin_area_ids'];
		if(!empty($putin_area_ids)){
			$putin_area_id_arr = explode(',',$putin_area_ids);
			$city_id_arr = [];
			foreach($city_arr as $key=>$v){
				if(in_array($v['id'],$putin_area_id_arr)){
					$city_arr[$key]['select'] = 'selected';
				}
			}
		}
		$this->assign('vinfo',$vinfo);
		$this->assign('info_goods',$info_goods);
		$this->assign('info_money',$info_money);
		$this->assign('info_invoice',$info_invoice);
		$this->assign('city_arr',$city_arr);
		$this->assign('sign_user_arr',$sign_user_arr);
		$this->assign('company_property_arr',$this->company_property_arr);
		$this->assign('invoice_type_arr',$this->invoice_type_arr);
		$this->assign('contract_company_arr',$this->contract_company_arr);
		$this->assign('contract_ctype_arr',$this->contract_ctype_arr);
		
		$this->assign('settlement_type_arr',$this->settlement_type_arr);
        $this->display('logdetail');
		
	}
}