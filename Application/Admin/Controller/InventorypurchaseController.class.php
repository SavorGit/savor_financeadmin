<?php
namespace Admin\Controller;

class InventorypurchaseController extends BaseController {
    
    /*private $required_arr = array(
        'name'=>'请填写合同名称','department_id'=>'请选择采购组织',
        'department_user_id'=>'请选择采购人','total_fee'=>'请填写采购金额','amount'=>'请填写采购总数',
        'supplier_id'=>'请选择供应商','purchase_date'=>"请选择采购日期"
        
    );*/
    public $clean_writeoff_uid = array(361);//364 yingtao
    private $required_arr = array(
        'name'=>'请填写合同名称','department_id'=>'请选择采购组织',
        'department_user_id'=>'请选择采购人','supplier_id'=>'请选择供应商','purchase_date'=>"请选择采购日期"
    );
    private $detail_required_arr = array(
        'goods_id'=>'请选择采购商品','unit_id'=>'请选择单位','price'=>'请填写单价','amount'=>'请填写采购数量',
    );
    private $paydetail_required_arr = array(
        'pay_date'=>'请选择付款日期','pay_fee'=>'请选择付款金额'
    );
    private $session_key = 'inventorypurchase_id_';
    private $serial_number_prefix = 'TPCG';

    public function __construct() {
        parent::__construct();
    }
    public function index(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $order = I('_order','a.id');
        $sort = I('_sort','desc');
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        $orders = $order.' '.$sort;
        $start  = ($page-1 ) * $size;
        $where  = [];
        $department_id = I('department_id',0,'intval');
        if($department_id){
            $where['a.department_id'] = $department_id;
        }
        $supplier_id  = I('supplier_id',0,'intval');
        if($supplier_id){
            $where['a.supplier_id'] = $supplier_id;
        }
        $name = I('name','','trim');
        if(!empty($name)){
            $where['a.name'] = array("like","%".$name."%");
        }
        if(!empty($start_time) && !empty($end_time)){
            $where['a.purchase_date'] = array(array('egt',$start_time),array('elt',$end_time));
        }

        $department_arr = $this->getDepartmentTree(2);
        //供应商
        $m_supplier   = new \Admin\Model\SupplierModel();
        $supplier_arr = $m_supplier->where(array('status'=>1))->select();

        $m_puchase = new \Admin\Model\PurchaseModel();
        $fileds = "a.id,a.serial_number,a.name,d.name department_name,a.purchase_date,a.amount,a.total_fee,s.name supplier_name,
                   case a.status
				   when 1 then '进行中'
				   when 2 then '已完成' END AS status";
        $result = $m_puchase->getList($fileds,$where, $orders, $start,$size);
        
        $this->assign('list',$result['list']);
        $this->assign('page',$result['page']);
        $this->assign('department_arr',$department_arr);
        $this->assign('supplier_arr',$supplier_arr);
        $this->assign('name',$name);
        $this->assign('supplier_id',$supplier_id);
        $this->assign('department_id',$department_id);
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$page);
        $this->assign('_order',$order);
        $this->assign('_sort',$sort);
        $this->display();
    }

    public function add(){
        //采购合同
        //pcontract_arr
        $m_contract =  new \Admin\Model\ContractModel();
        $where = [];
        $where['type'] = 40;
        $where['status'] = array('in',array(1,2));
        $pcontract_arr  = $m_contract->field('id,serial_number')->where($where)->select();
        //获取采购组织
        /* $m_department = new \Admin\Model\DepartmentModel();
        $where = [];
        $where['status'] = 1;
        $department_arr = $m_department->where($where)->select(); */
        $department_arr = $this->getDepartmentTree(2);
        
        //供应商
        $m_supplier   = new \Admin\Model\SupplierModel();
        $where = [];
        $where['status'] = 1;
        $supplier_arr = $m_supplier->where($where)->select();
        
        //采购商品
        $m_goods = new \Admin\Model\GoodsModel();
        $field = 'id,name';
        $where = [];
        $where['status'] = 1;
        $goods_list = $m_goods->field($field)->where()->select();
        
        $this->assign('pcontract_arr',$pcontract_arr);
        $this->assign('department_arr',$department_arr);
        $this->assign('supplier_arr',  $supplier_arr);
        $this->assign('goods_list',$goods_list);
        $this->display();
    }
    public function getUsers(){
        $room_id = I('department_id','0','intval');
        $m_department_user = new \Admin\Model\DepartmentUserModel();
        $fields = 'id,name ';
        $where = array();
        $where['department_id'] = $room_id;
        $where['status'] = 1;
        $user_list = $m_department_user->where($where)->select();
        echo json_encode($user_list);
        exit;
    }
    public function doadd(){
        if(IS_POST){
            foreach($this->required_arr as $key=>$v){
                $tmp = I('post.'.$key);
                if(empty($tmp)){
                    $this->error($v);
                    break;
                }
                
            }
            $userinfo              = session('sysUserInfo');
            //$serial_number         = I('serial_number','','trim');            //采购单号
            $contract_id           = I('contract_id',0,'intval');             //合同id
            $name                  = I('name','','trim');                     //合同标题
            $department_id         = I('department_id',0,'intval');           //采购组织
            $department_user_id    = I('department_user_id',0,'intval');      //采购人
            $total_fee             = I('total_fee',0,'trim');                //采购总金额
            $amount                = I('amount',0,'intval');                  //采购总数
            $supplier_id           = I('supplier_id',0,'intval');             //供应商
            $purchase_date         = I('purchase_date','','trim');            //采购日期
            $status                = I('status',1,'intval');                  //采购状态
            $des                   = I('des','','trim');                      //备注
            
            
            $data = [];
            $data['contract_id']        = $contract_id;
            //$data['serial_number ']     = $serial_number;
            $data['name']               = $name;
            $data['department_id']      = $department_id;
            $data['department_user_id'] = $department_user_id;
            $data['total_fee']          = $total_fee ;
            $data['amount']             = $amount;
            $data['supplier_id']        = $supplier_id;
            $data['purchase_date']      = $purchase_date;
            $data['status']             = $status;
            $data['des']                = $des;
            $data['sysuser_id']         = $userinfo['id'];
            
            
            $m_purchase = new \Admin\Model\PurchaseModel();
            
            $serial_number_prefix = $this->serial_number_prefix.date('Ymd');
            $map = [];
            $map['serial_number'] = array('like',$serial_number_prefix."%");
            $rts = $m_purchase->field('serial_number')->where($map)->order('id desc')->find();
            if(empty($rts)){
                $data['serial_number'] = $serial_number_prefix.'001';
            }else {
                $sub_str = substr($rts['serial_number'], 12,3);
                $sub_str = intval($sub_str) +1;
                $serial_number = str_pad($sub_str,3,"0",STR_PAD_LEFT);
                $data['serial_number'] =$serial_number_prefix . $serial_number;
            }
            $ret  = $m_purchase->addData($data);
            if($ret){
                
                $this->output('添加成功!', 'inventorypurchase/index');
            }else{
                $this->error('添加失败');
            }
        }
    }
    function edit(){
        $id = I('id',0,'intval');
        
        //采购合同
        //pcontract_arr
        $m_contract =  new \Admin\Model\ContractModel();
        $where = [];
        $where['type'] = 40;
        $where['status'] = array('in',array(1,2));
        $pcontract_arr  = $m_contract->field('id,serial_number')->where($where)->select();
        
        //获取采购组织
        /* $m_department = new \Admin\Model\DepartmentModel();
        $where = [];
        $where['status'] = 1;
        $department_arr = $m_department->where($where)->select(); */
        $department_arr = $this->getDepartmentTree(2);
        
        //供应商
        $m_supplier   = new \Admin\Model\SupplierModel();
        $where = [];
        $where['status'] = 1;
        $supplier_arr = $m_supplier->where($where)->select();
        $m_purchase = new \Admin\Model\PurchaseModel();
        $fields = "a.id,a.name,a.serial_number,c.id contract_id,d.id department_id,a.purchase_date,a.amount,
                   s.id supplier_id,u.id department_user_id,a.total_fee,a.status,a.des";
        
        $where = [];
        $where['a.id'] = $id;
        $result = $m_purchase->alias('a')
                   ->join('savor_finance_contract c on a.contract_id=c.id','left')
                   ->join('savor_finance_department d on a.department_id = d.id','left')
                   ->join('savor_finance_supplier   s on a.supplier_id   = s.id','left')
                   ->join('savor_finance_department_user u on a.department_user_id=u.id','left')
                   ->field($fields)
                   ->where($where)
                   ->find();
        if(empty($result)){
            echo '<script>$.pdialog.closeCurrent();  alertMsg.error("数据错误！");</script>';
        }
        //采购人
        $m_department_user = new \Admin\Model\DepartmentUserModel();
        $fields = "id ,name";
        $where = [];
        $where['department_id'] = $result['department_id'];
        $department_user_arr = $m_department_user->field($fields)->where($where)->select();
        
        
        $this->assign('pcontract_arr',$pcontract_arr);
        $this->assign('department_arr',$department_arr);
        $this->assign('supplier_arr',  $supplier_arr);
        $this->assign('department_user_arr',$department_user_arr);
        $this->assign('vinfo',$result);
        $this->display();
    }
    function doedit(){
        $id = I('post.id',0,'intval');
        if(IS_POST){
            foreach($this->required_arr as $key=>$v){
                $tmp = I('post.'.$key);
                if(empty($tmp)){
                    $this->error($v);
                    break;
                }
                
            }
            $userinfo              = session('sysUserInfo');
            //$serial_number         = I('serial_number','','trim');            //采购单号
            $contract_id           = I('contract_id',0,'intval');             //合同id
            $name                  = I('name','','trim');                     //合同标题
            $department_id         = I('department_id',0,'intval');           //采购组织
            $department_user_id    = I('department_user_id',0,'intval');      //采购人
            //$total_fee             = I('total_fee',0,'trim');                //采购总金额
            //$amount                = I('amount',0,'intval');                  //采购总数
            $supplier_id           = I('supplier_id',0,'intval');             //供应商
            $purchase_date         = I('purchase_date','','trim');            //采购日期
            $status                = I('status',1,'intval');                  //采购状态
            $des                   = I('des','','trim');                      //备注
            
            
            $data = [];
            //$data['serial_number']      = $serial_number;
            $data['contract_id ']       = $contract_id ;
            $data['name']               = $name;
            $data['department_id']      = $department_id;
            $data['department_user_id'] = $department_user_id;
            //$data['total_fee']          = $total_fee ;
            //$data['amount']             = $amount;
            $data['supplier_id']        = $supplier_id;
            $data['purchase_date']      = $purchase_date;
            $data['status']             = $status;
            $data['des']                = $des;
            $data['sysuser_id']         = $userinfo['id'];
            $data['update_time']        = date('Y-m-d H:i:s');
            
            $m_purchase = new \Admin\Model\PurchaseModel();
            $where = [];
            $where['id'] = $id;
            $ret = $m_purchase->updateData($where,$data);
            if($ret){
                $this->output('编辑成功!', 'inventorypurchase/index');
            }else{
                $this->error('编辑失败!');
            }
        }
    }
    public function detaillist(){
        $purchase_id = I('purchase_id',0,'intval');
        if(!empty($purchase_id)){
            session($this->session_key,$purchase_id);
        }else {
            $purchase_id = session($this->session_key);
        }
        
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
    
        
        $fields = 'a.id,g.barcode,g.name goods_name,u.name unit_name,c.name category_name,a.price,a.amount,a.total_fee';
        $where  = [];
        $where['a.purchase_id'] = $purchase_id;
        $where['a.status'] = 1;
        $m_pushase_detail = new \Admin\Model\PurchaseDetailModel();
        $result = $m_pushase_detail->getList($fields,$where, $orders, $start,$size);
        
        $this->assign('purchase_id',$purchase_id);
        $this->assign('list',$result['list']);
        $this->assign('page',$result['page']);
        $this->display();
    }
    public function adddetail(){
        $purchase_id = I('purchase_id',0,'intval');
        //采购商品
        $m_goods = new \Admin\Model\GoodsModel();
        $where = [];
        $where['status'] = 1;
        $goods_list  = $m_goods->field('id,name')->where($where)->select();

        $this->assign('purchase_id',$purchase_id);
        $this->assign('goods_arr',$goods_list);
        
        $this->display();
    }
    public function getgoodsinfo(){
        $goods_id = I('goods_id',0,'intval');
        $m_goods = new \Admin\Model\GoodsModel();
        $where = array('id'=>$goods_id);
        $fields = 'barcode,name,category_id';
        $goods_info = $m_goods->field($fields)
                               ->where($where)
                               ->find();
        $m_unit = new \Admin\Model\UnitModel();
        $fields = "name,id";
        $where = array('category_id'=>$goods_info['category_id'],'status'=>1);
        $unit_info = $m_unit->field($fields)->where($where)->select();
        $data = $goods_info;
        $data['unit_arr'] = $unit_info;
        die(json_encode($data));
    }
    public function doAddDetail(){
        $purchase_id = I('post.purchase_id',0,'intval');
        
        if(IS_POST){
            foreach($this->detail_required_arr as $key=>$v){
                $tmp = I('post.'.$key);
                if(empty($tmp)){
                    $this->error($v);
                    break;
                }
            }
            $goods_id = I('post.goods_id',0,'intval');
            $price    = I('post.price','','trim');
            $unit_id  = I('post.unit_id',0,'intval');
            $amount   = I('post.amount',0,'intval');
            $status   = 1;
            
            $m_unit = new \Admin\Model\UnitModel();
            $where = [];
            $where['id'] = $unit_id;
            $unit_info = $m_unit->field('convert_type')->where($where)->find();
            $total_amount = intval($unit_info['convert_type'] * $amount);  //总瓶数
            
            
            $m_purchase_detail = new \Admin\Model\PurchaseDetailModel();
            $data['purchase_id'] = $purchase_id;
            $data['goods_id']    = $goods_id;
            $data['price']       = $price;
            $data['unit_id']     = $unit_id;
            $data['amount']      = $amount;
            $data['status']      = $status;
            $data['total_fee']      = $amount*$price;
            $data['total_amount']= $total_amount;
            $ret = $m_purchase_detail->addData($data);
            if($ret){
                //更新采购合同总金额、总数量
                $rts = $m_purchase_detail->field('sum(amount) as amount,sum(total_fee) as total_fee')->where(array('purchase_id'=>$purchase_id,'status'=>1))->find();
                //print_r($rts);exit;
                $m_purchase = new \Admin\Model\PurchaseModel();
                $map = [];
                $map['amount'] = $rts['amount'];
                $map['total_fee'] = $rts['total_fee'];
                $m_purchase->updateData(array('id'=>$purchase_id), $map);
                
                
                
                $this->output('添加成功!', 'inventorypurchase/detaillist');
            }else {
                $this->error('添加失败');
            }
            
        }
    }
    public function editdetail(){
        $purchase_id = I('purchase_id',0,'intval');
        $id          = I('id',0,'intval');
        $m_purchase_detail = new \Admin\Model\PurchaseDetailModel();
        $fields = 'a.id,a.goods_id,g.name ,g.barcode,a.unit_id,a.price,a.amount,g.category_id';
        $where = [];
        $where['a.id'] = $id;
        $where['a.purchase_id'] = $purchase_id;
        $detail_info = $m_purchase_detail->alias('a')
                                         ->join('savor_finance_goods g on a.goods_id = g.id','left')
                                         ->join('savor_finance_category c on g.category_id=c.id','left')
                                         ->join('savor_finance_unit  u on a.unit_id  = u.id','left')
                                         ->field($fields)
                                         ->where($where)->find();
        
        if(empty($detail_info)){
            echo '<script>$.pdialog.closeCurrent();  alertMsg.error("数据错误！");</script>';
        }else {
            $m_unit = new \Admin\Model\UnitModel();
            $fields = 'id,name';
            $where = [];
            $where['category_id'] = $detail_info['category_id'];
            $where['status']      = 1;
            
            $unit_arr = $m_unit->field($fields)->where($where)->select();
            
            //采购商品
            $m_goods = new \Admin\Model\GoodsModel();
            $where = [];
            $where['status'] = 1;
            $goods_list  = $m_goods->field('id,name')->where($where)->select();
            $this->assign('goods_arr',$goods_list);
            $this->assign('unit_arr',$unit_arr);
            $this->assign('vinfo',$detail_info);
            $this->assign('purchase_id',$purchase_id);
            $this->assign('id',$id);
            $this->display();
        }
        
    }
    public function doeditdetail(){
        $purchase_id = I('purchase_id',0,'intval');
        $id          = I('id',0,'intval');
        if(IS_POST){
            foreach($this->detail_required_arr as $key=>$v){
                $tmp = I('post.'.$key);
                if(empty($tmp)){
                    $this->error($v);
                    break;
                }
            }
            $goods_id = I('post.goods_id',0,'intval');
            $price    = I('post.price','','trim');
            $unit_id  = I('post.unit_id',0,'intval');
            $amount   = I('post.amount',0,'intval');
            
            $m_unit = new \Admin\Model\UnitModel();
            $unit_info = $m_unit->field('convert_type')->where(array('id'=>$unit_id))->find();
            $total_amount = intval($unit_info['convert_type'] * $amount);  //总瓶数
            $m_purchase_detail = new \Admin\Model\PurchaseDetailModel();
            $sdwhere = array('purchase_detail_id'=>$id,'status'=>1);
            $m_stock_detail = new \Admin\Model\StockDetailModel();
            $ret_instock = $m_stock_detail->field('id')->where($sdwhere)->find();

            $detail_info = $m_purchase_detail->field('goods_id,unit_id,price')->where(array('id'=>$id))->find();
            if($unit_id != $detail_info['unit_id'] || $goods_id!=$detail_info['goods_id']){
                if(!empty($ret_instock)){
                    $this->error('已有入库信息不可修改');
                }
            }
            if($price!=$detail_info['price']){
                $userinfo = session('sysUserInfo');
                $sysuser_id = $userinfo['id'];
                $is_in_changeprice = in_array($sysuser_id,$this->clean_writeoff_uid)?1:0;
                if($is_in_changeprice==0){
                    if(!empty($ret_instock)){
                        $this->error('已有入库信息不可修改，如需修改请发审批！');
                    }
                }
                $m_changeprice = new \Admin\Model\ChangepriceRecordModel();
                $cwhere = array('purchase_id'=>$purchase_id,'purchase_detail_id'=>$id,'goods_id'=>$goods_id);
                $cwhere["DATE_FORMAT(add_time,'%Y-%m-%d')"] = date('Y-m-d');
                $res_data = $m_changeprice->getInfo($cwhere);
                $cdata = array('purchase_id'=>$purchase_id,'purchase_detail_id'=>$id,'goods_id'=>$goods_id,
                    'price'=>$price,'old_price'=>$detail_info['price'],'sysuser_id'=>$userinfo['id']);
                if(empty($res_data)){
                    $m_changeprice->add($cdata);
                }else{
                    $m_changeprice->updateData(array('id'=>$res_data['id']),$cdata);
                }
            }

            $data['goods_id']    = $goods_id;
            $data['price']       = $price;
            $data['unit_id']     = $unit_id;
            $data['amount']      = $amount;
            $data['total_fee']      = $amount*$price;
            $data['total_amount']= $total_amount;
            $data['update_time'] = date('Y-m-d H:i:s');
            $where = [];
            $where['id'] = $id;
            $where['purchase_id'] = $purchase_id;
            $ret = $m_purchase_detail->updateData($where,$data);
            if($ret){
                //更新采购合同总金额、总数量
                $rts = $m_purchase_detail->field('sum(amount) as amount,sum(total_fee) as total_fee')->where(array('purchase_id'=>$purchase_id,'status'=>1))->find();
                //print_r($rts);exit;
                $m_purchase = new \Admin\Model\PurchaseModel();
                $map = [];
                $map['amount'] = $rts['amount'];
                $map['total_fee'] = $rts['total_fee'];
                $m_purchase->updateData(array('id'=>$purchase_id), $map);
                $this->outputNew('编辑成功!', 'inventorypurchase/detaillist');
            }else {
                $this->error('编辑失败');
            }
        }
    }

    public function deldetail(){
        $purchase_id = I('get.purchase_id',0,'intval');
        $id          = I('get.id',0,'intval');
		$m_stock_detail =    new \Admin\Model\StockDetailModel();
		$where = [];
		$where['purchase_detail_id'] = $id;
		$where['status'] = 1;
		$ret = $m_stock_detail->field('id')->where($where)->select();
		if(!empty($ret)){
			$this->error('已有入库信息不可删除');
		}
		
        $m_purchase_detail = new \Admin\Model\PurchaseDetailModel();
        $where= [];
        $where['id'] = $id;
        $where['purchase_id'] = $purchase_id;
        $data = [];
        $data['status'] = 2;
        $ret = $m_purchase_detail->updateData($where, $data);
        if($ret){
            
            //更新采购合同总金额、总数量
            $rts = $m_purchase_detail->field('sum(amount) as amount,sum(total_fee) as total_fee')->where(array('purchase_id'=>$purchase_id,'status'=>1))->find();
            //print_r($rts);exit;
            $m_purchase = new \Admin\Model\PurchaseModel();
            $map = [];
            $map['amount'] = $rts['amount'];
            $map['total_fee'] = $rts['total_fee'];
            $m_purchase->updateData(array('id'=>$purchase_id), $map);
            $this->output('删除成功!', 'inventorypurchase/detaillist','');
        }else {
            $this->error('删除失败');
        }
    }
    public function paylist(){
        $purchase_id = I('purchase_id',0,'intval');
        if(!empty($purchase_id)){
            session($this->session_key,$purchase_id);
        }else {
            $purchase_id = session($this->session_key);
        }
        
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
        
        $m_purchase_paydetail = new \Admin\Model\PurchasePaydetailModel();
        $fields = 'a.id,a.pay_date,a.pay_fee,m.oss_addr,a.add_time';
        $where = [];
        $where['purchase_id'] = $purchase_id;
        $where['status'] = 1;
        $result = $m_purchase_paydetail->getList($fields,$where, $orders, $start,$size);
        $oss_host =  get_oss_host();
        $this->assign('oss_host',$oss_host);
        $this->assign('purchase_id',$purchase_id);
        $this->assign('list',$result['list']);
        $this->assign('page',$result['page']);
        
        
        $this->display();
    }
    public function addpaydetail(){
        
        $purchase_id = I('purchase_id',0,'intval');
        $this->assign('purchase_id',$purchase_id);
        $this->display();
    }
    public function doaddpaydetail(){
        $purchase_id = I('purchase_id',0,'intval');
        if(IS_POST){
            foreach($this->paydetail_required_arr as $key=>$v){
                $tmp = I('post.'.$key);
                if(empty($tmp)){
                    $this->error($v);
                    break;
                }
            }
            $pay_date = I('post.pay_date','','trim');
            $pay_fee    = I('post.pay_fee',0,'trim');
            $fk         = I('post.fk',0,'intval');
            $status   = 1;
            
            
            $m_purchasePaydetail = new \Admin\Model\PurchasePaydetailModel();
            $userinfo              = session('sysUserInfo');
            $data['purchase_id'] = $purchase_id;
            $data['pay_date']    = $pay_date;
            $data['pay_fee']     = $pay_fee;
            $data['media_id']    = $fk;
            $data['status']      = 1;
            $data['add_sysuser_id'] = $userinfo['id'];
            
            $ret = $m_purchasePaydetail->addData($data);
            
            if($ret){
                
                $this->output('添加成功!', 'inventorypurchase/paylist');
            }else{
                $this->error('添加失败');
            }
        }
    }
    public function editpaydetail(){
        $purchase_id = I('purchase_id',0,'intval');
        $id          = I('id',0,'intval');
        $m_purchase_paydetail = new \Admin\Model\PurchasePaydetailModel();
        
        $where = [];
        $where['a.id'] = $id;
        $where['a.purchase_id'] = $purchase_id;
        //print_r($where);exit;
        $fields = 'a.*,m.oss_addr';
        $result = $m_purchase_paydetail->alias('a')
                             ->join('savor_media m on a.media_id=m.id','left')
                             ->field($fields)
                             ->where($where)
                             ->find();
        if(!empty($result['media_id'])){
            $oss_host = get_oss_host();
            $result['oss_addr'] = $oss_host.$result['oss_addr'];
        }
        $this->assign('vinfo',$result);
            
        $this->assign('purchase_id',$purchase_id);
        $this->assign('id',$id);
        $this->display();
    }
    public function doeditpaydetail(){
        $purchase_id = I('purchase_id',0,'intval');
        $id          = I('id',0,'intval');
        if(IS_POST){
            foreach($this->paydetail_required_arr as $key=>$v){
                $tmp = I('post.'.$key);
                if(empty($tmp)){
                    $this->error($v);
                    break;
                }
            }
            $where = [];
            $where['id'] = $id;
            $where['purchase_id'] = $purchase_id;
            
            $userinfo              = session('sysUserInfo');
            $pay_date = I('post.pay_date','','trim');
            $pay_fee  = I('post.pay_fee',0,'trim');
            $fk       = I('post.fk',0,'intval');
            $data = [];
            
            $data['pay_date'] = $pay_date;
            $data['pay_fee']  = $pay_fee;
            $data['media_id'] = $fk;
            $data['update_time'] = date('Y-m-d H:i:s');
            $data['edit_sysuser_id'] = $userinfo['id'];
            
            $m_purchase_paydetail = new \Admin\Model\PurchasePaydetailModel();
            $ret = $m_purchase_paydetail->updateData($where,$data);
            
            if($ret){
                $this->outputNew('编辑成功!', 'inventorypurchase/paylist');
                
            }else {
                $this->error('编辑失败');
            }
        }
    }
    public function delpaydetail(){
        $purchase_id = I('get.purchase_id',0,'intval');
        $id          = I('get.id',0,'intval');
        
        
        $m_purchase_paydetail = new \Admin\Model\PurchasePaydetailModel();
        $where= [];
        $where['id'] = $id;
        $where['purchase_id'] = $purchase_id;
        $data = [];
        $data['status'] = 2;
        $ret = $m_purchase_paydetail->updateData($where, $data);
        if($ret){
            $this->output('删除成功!', 'inventorypurchase/paylist','');
        }else {
            $this->error('删除失败');
        }
    }
}