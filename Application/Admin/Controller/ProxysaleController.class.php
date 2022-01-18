<?php
/**
 *资源管理控制器
 *
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
use Think\Model;
class ProxysaleController extends BaseController{
    
    private $oss_host = '';
	private $company_property_arr = [];
	private $invoice_type_arr    = [];
	private $contract_ctype_arr  = [];
	private $goods_nums = 5;
	private $status_arr = [];
    public function __construct(){
        parent::__construct();
		$config_proxy_sale_contract = C('FINACE_CONTRACT');
		$this->company_property_arr = $config_proxy_sale_contract['company_property'];
		$this->invoice_type_arr     = $config_proxy_sale_contract['invoice_type'];
		$this->contract_ctype_arr   = $config_proxy_sale_contract['contract_ctype']['proxysale'];
		$this->status_arr           = $config_proxy_sale_contract['contract_status'];
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
		}
		if($sign_user_id){
			$where['a.sign_user_id'] = $sign_user_id;
			$this->assign('sign_user_id',$sign_user_id);
		}
		if($name){
			$where['a.name'] = array('like',"%".$name."%");
			$this->assign('name',$name);
		}
		$m_contract = new \Admin\Model\ContractModel();
		$fileds = "a.*,b.uname";
		
		$result = $m_contract->getList($fileds,$where, $orders, $start=0,$size);
		
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
        $this->display('add');
    }
    public function doadd(){
		
		if(IS_POST){
			$data =  [];
			$is_draft      				 = I('post.is_draft',0,'intval');               //是否保存草稿
			$data['serial_number']   	 = I('post.serial_number','','trim');     		//合同编号
			$data['name']   	         = I('post.name','','trim');     		        //合同名称
			$data['company_id']      	 = I('post.company_id',0,'intval');       		//签约公司          
			$data['sign_department'] 	 = I('post.sign_department','','trim');   		//签约部门
			$data['sign_user_id']    	 = I('post.sign_user_id',0,'intval');     		//签约人
			$data['ctype']           	 = I('post.ctype',0,'intval');                  //合同类型
			$data['area_id']         	 = I('post.area_id',0,'intval');            	//签约城市
			$data['sign_time']       	 = I('post.sign_time','','trim');               //签署日期
			$data['archive_time']    	 = I('post.archive_time','','trim');            //归档日期
			$data['statement_time']  	 = I('post.statement_time','','trim');          //对账日期
			$data['settlement_time'] 	 = I('post.settlement_time','','trim');         //结算日期
			$data['contract_stime']  	 = I('post.contract_stime','','trim');          //合同开始日期
			$data['contract_etime']      = I('post.contract_etime','','trim');          //合同结束日期
			$data['hotel_signer']        = I('post.hotel_signer','','trim');            //合同签约人
			$data['hotel_signer_phone1'] = I('post.hotel_signer_phone1','','trim');     //合同签约人电话1
			$data['hotel_signer_phone2'] = I('post.hotel_signer_phone2','','trim');     //合同签约人电话2
			
			
			
			$data['company_name']        = I('post.company_name','','trim');            //公司名称
			$data['company_short_name']  = I('post.company_short_name','','trim');      //公司简介
			$data['company_area_id']     = I('post.company_area_id','','trim');         //公司所属城市
			$data['address']             = I('post.address','','trim');                 //公司注册地址
			$data['company_property']    = I('post.company_property','','trim');        //公司企业性质
			$data['invoice_type']    	 = I('post.invoice_type',0,'intval');     		//公司发票类型
			$data['rate']                = I('post.rate','','trim');                    //公司税率
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
			
			$goods_name            = I('post.goods_name');                              //商品名称
			$goods_number          = I('post.goods_number');                            //商品编码
			$goods_type 		   = I('post.goods_type');                              //品类
			$goods_unit 		   = I('post.goods_unit');                              //基本单位
			$goods_standard 	   = I('post.goods_standard');                          //规格
			$goods_box_standard    = I('post.goods_box_standard');                      //箱规
			$goods_alcohol 	       = I('post.goods_alcohol');                           //酒精度
			$goods_discount_rate   = I('post.goods_discount_rate');                     //折扣率
			$goods_discount_mount  = I('post.goods_discount_mount');                    //折扣额
			$goods_commission 	   = I('post.goods_commission');                        //佣金
			$balance_unit 		   = I('post.balance_unit');                            //结算单位
			$info_goods = [];
			for($i=0;$i<$this->goods_nums;$i++){
				$info_goods[$i]['goods_name']          = $goods_name[$i];
				$info_goods[$i]['goods_number']        = $goods_number[$i];
				$info_goods[$i]['goods_type']          = $goods_type[$i];
				$info_goods[$i]['goods_unit']          = $goods_unit[$i];
				$info_goods[$i]['goods_standard']      = $goods_standard[$i];
				$info_goods[$i]['goods_box_standard']  = $goods_box_standard[$i];
				$info_goods[$i]['goods_alcohol'] = $goods_alcohol[$i];
				$info_goods[$i]['goods_discount_rate'] = $goods_discount_rate[$i];
				$info_goods[$i]['goods_discount_mount']= $goods_discount_mount[$i];
				$info_goods[$i]['goods_commission']    = $goods_commission[$i];
				$info_goods[$i]['balance_unit']        = $balance_unit[$i];
				
			}
			$data['info_goods'] = json_encode($info_goods);
			
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
					$this->output('添加成功!', 'proxysale/index');
				}else{
					$this->error('添加失败');
				}
			}else{
				$ret  = $m_contract->addData($data);
				if($ret){
					$m_contract_history = new \Admin\Model\ContracthistoryModel();
					$rts = $m_contract_history->addData($data);
					$this->output('添加成功!', 'proxysale/index');
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
		
		$info_goods = json_decode($vinfo['info_goods'],true);
		$this->assign('vinfo',$vinfo);
		//print_r($info_goods);exit;
		$this->assign('info_goods',$info_goods);
		$this->assign('city_arr',$city_arr);
		$this->assign('sign_user_arr',$sign_user_arr);
		$this->assign('company_property_arr',$this->company_property_arr);
		$this->assign('invoice_type_arr',$this->invoice_type_arr);
		$this->assign('contract_company_arr',$this->contract_company_arr);
		$this->assign('contract_ctype_arr',$this->contract_ctype_arr);
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
			$data =  [];
			$is_draft      				 = I('post.is_draft',0,'intval');               //是否保存草稿
			$data['serial_number']   	 = I('post.serial_number','','trim');     		//合同编号
			$data['name']   	         = I('post.name','','trim');     		        //合同名称
			$data['company_id']      	 = I('post.company_id',0,'intval');       		//签约公司          
			$data['sign_department'] 	 = I('post.sign_department','','trim');   		//签约部门
			$data['sign_user_id']    	 = I('post.sign_user_id',0,'intval');     		//签约人
			$data['ctype']           	 = I('post.ctype',0,'intval');                  //合同类型
			$data['area_id']         	 = I('post.area_id',0,'intval');            	//签约城市
			$data['sign_time']       	 = I('post.sign_time','','trim');               //签署日期
			$data['archive_time']    	 = I('post.archive_time','','trim');            //归档日期
			$data['statement_time']  	 = I('post.statement_time','','trim');          //对账日期
			$data['settlement_time'] 	 = I('post.settlement_time','','trim');         //结算日期
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
			
			$goods_name            = I('post.goods_name');                              //商品名称
			$goods_number          = I('post.goods_number');                            //商品编码
			$goods_type 		   = I('post.goods_type');                              //品类
			$goods_unit 		   = I('post.goods_unit');                              //基本单位
			$goods_standard 	   = I('post.goods_standard');                          //规格
			$goods_box_standard    = I('post.goods_box_standard');                      //箱规
			$goods_alcohol 	       = I('post.goods_alcohol');                           //酒精度
			$goods_discount_rate   = I('post.goods_discount_rate');                     //折扣率
			$goods_discount_mount  = I('post.goods_discount_mount');                    //折扣额
			$goods_commission 	   = I('post.goods_commission');                        //佣金
			$balance_unit 		   = I('post.balance_unit');                            //结算单位
			$info_goods = [];
			for($i=0;$i<$this->goods_nums;$i++){
				$info_goods[$i]['goods_name']          = $goods_name[$i];
				$info_goods[$i]['goods_number']        = $goods_number[$i];
				$info_goods[$i]['goods_type']          = $goods_type[$i];
				$info_goods[$i]['goods_unit']          = $goods_unit[$i];
				$info_goods[$i]['goods_standard']      = $goods_standard[$i];
				$info_goods[$i]['goods_box_standard']  = $goods_box_standard[$i];
				$info_goods[$i]['goods_alcohol'] = $goods_alcohol[$i];
				$info_goods[$i]['goods_discount_rate'] = $goods_discount_rate[$i];
				$info_goods[$i]['goods_discount_mount']= $goods_discount_mount[$i];
				$info_goods[$i]['goods_commission']    = $goods_commission[$i];
				$info_goods[$i]['balance_unit']        = $balance_unit[$i];
				
			}
			$data['info_goods'] = json_encode($info_goods);
			
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
					$this->output('编辑成功!', 'proxysale/index');
				}else{
					$this->error('编辑失败');
				}
			}else{
				
				$ret  = $m_contract->updateData(array('id'=>$id),$data);
				if($ret){
					$m_contract_history = new \Admin\Model\ContracthistoryModel();
					$data['contract_id'] = $id;
					$rts = $m_contract_history->addData($data);
					$this->output('编辑成功!', 'proxysale/index');
				}else{
					$this->error('编辑失败');
				}
			}
		}
	}
	public function linkhotel(){
		
		$id = I('id',0,'intval');
		$m_contract = new \Admin\Model\ContractModel;
		$contract_info = $m_contract->where('id='.$id)->field('area_id')->find();
		
		
		$m_hotel = new \Admin\Model\HotelModel();
		$where= [];
		$where['area_id'] = $contract_info['area_id'];
		
		$hotel_list = $m_hotel->where($where)->field('id,name')->select();
		
		$m_contract_hotel= new \Admin\Model\ContracthotelModel();
		$link_hotel_list = $m_contract_hotel->where('contract_id='.$id)->select();
		$link_hotel_list_arr = array_column($link_hotel_list,'hotel_id');
		foreach($hotel_list as $key=>$v){
			if(in_array($v['id'],$link_hotel_list_arr)){
				$hotel_list[$key]['select'] = 'selected';
			}
		}
		$this->assign('hotel_list',$hotel_list);
		$this->assign('id',$id);
		$this->display('linkhotel');
	}
    public function dolinkhotel(){
		$id = I('post.id',0,'intval');
		$hotel_ids = I('post.hotel_ids');
		$data = [];
		foreach($hotel_ids as $key=>$v){
			$data[$key]['contract_id'] = $id;
			$data[$key]['hotel_id']    = $v;
			$data[$key]['add_time']    = date('Y-m-d H:i:s');
		}
		
		$m_contract_hotel= new \Admin\Model\ContracthotelModel();
		$ret = $m_contract_hotel->where(array('contract_id'=>$id))->delete();
		
		$m_contract_hotel->addAll($data);
		$this->output('关联成功','proxysale/index');
		
		
	}
}