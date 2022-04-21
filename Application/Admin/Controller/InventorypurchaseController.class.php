<?php
namespace Admin\Controller;

class InventorypurchaseController extends BaseController {
    
    private $required_arr = array(
        'name'=>'请填写合同名称','department_id'=>'请选择采购组织',
        'department_user_id'=>'请选择采购人','total_fee'=>'请填写采购金额','amount'=>'请填写采购总数',
        'supplier_id'=>'请选择供应商','purchase_date'=>"请选择采购日期"
        
    );
    private $detail_required_arr = array(
        'goods_id'=>'请选择采购商品','unit_id'=>'请选择单位','price'=>'请填写单价','amount'=>'请填写采购数量',
    );
    private $session_key = 'inventorypurchase_id_';
    public function __construct() {
        parent::__construct();
        
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
        $where  = [];
        
        $department_id = I('department_id',0,'intval');
        if($department_id){
            $where['a.department_id'] = $department_id;
            $this->assign('department_id',$department_id);
        }
        $supplier_id  = I('supplier_id',0,'intval');
        if($supplier_id){
            $where['a.supplier_id'] = $supplier_id;
            $this->assign('supplier_id',$supplier_id);
        }
        $name = I('name','','trim');
        if(!empty($name)){
            $where['a.name'] = array("like","%".$name."%");
            $this->assign('name',$name);
        }
        
        $m_puchase = new \Admin\Model\PurchaseModel();
        $fileds = "a.id,a.serial_number,a.name,d.name department_name,a.purchase_date,a.amount,s.name supplier_name,
                   case a.status
				   when 1 then '进行中'
				   when 2 then '已完成' END AS status";
        
        $result = $m_puchase->getList($fileds,$where, $orders, $start,$size);
        
        
        
        //获取采购组织
        $m_department = new \Admin\Model\DepartmentModel();
        $where = [];
        $where['status'] = 1;
        $department_arr = $m_department->where($where)->select();
        
        //供应商
        $m_supplier   = new \Admin\Model\SupplierModel();
        $where = [];
        $where['status'] = 1;
        $supplier_arr = $m_supplier->where($where)->select();
        
        $this->assign('list',$result['list']);
        $this->assign('page',$result['page']);
        $this->assign('department_arr',$department_arr);
        $this->assign('supplier_arr',  $supplier_arr);
        $this->display();
    }
    public function add(){
        
        //采购合同
        //pcontract_arr
        $m_contract =  new \Admin\Model\ContractModel();
        $where = [];
        $where['type'] = 40;
        $where['status'] = array('in',array(1,2));
        $pcontract_arr  = $m_contract->field('id,serial_number')->where()->select();
        //获取采购组织
        $m_department = new \Admin\Model\DepartmentModel();
        $where = [];
        $where['status'] = 1;
        $department_arr = $m_department->where($where)->select();
        
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
            $serial_number         = I('serial_number','','trim');            //采购单号
            $contract_id           = I('contract_id',0,'intval');             //合同id
            $name                  = I('name','','trim');                     //合同标题
            $department_id         = I('department_id',0,'intval');           //采购组织
            $department_user_id    = I('department_user_id',0,'intval');      //采购人
            $total_fee             = I('total_fee','','trim');                //采购总金额
            $amount                = I('amount',0,'intval');                  //采购总数
            $supplier_id           = I('supplier_id',0,'intval');             //供应商
            $purchase_date         = I('purchase_date','','trim');            //采购日期
            $status                = I('status',1,'intval');                  //采购状态
            $des                   = I('des','','trim');                      //备注
            
            
            $data = [];
            $data['contract_id']        = $contract_id;
            $data['serial_number ']     = $serial_number;
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
        $pcontract_arr  = $m_contract->field('id,serial_number')->where()->select();
        
        //获取采购组织
        $m_department = new \Admin\Model\DepartmentModel();
        $where = [];
        $where['status'] = 1;
        $department_arr = $m_department->where($where)->select();
        
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
            $serial_number         = I('serial_number','','trim');            //采购单号
            $contract_id           = I('contract_id',0,'intval');             //合同id
            $name                  = I('name','','trim');                     //合同标题
            $department_id         = I('department_id',0,'intval');           //采购组织
            $department_user_id    = I('department_user_id',0,'intval');      //采购人
            $total_fee             = I('total_fee','','trim');                //采购总金额
            $amount                = I('amount',0,'intval');                  //采购总数
            $supplier_id           = I('supplier_id',0,'intval');             //供应商
            $purchase_date         = I('purchase_date','','trim');            //采购日期
            $status                = I('status',1,'intval');                  //采购状态
            $des                   = I('des','','trim');                      //备注
            
            
            $data = [];
            $data['serial_number']      = $serial_number;
            $data['contract_id ']       = $contract_id ;
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
            $data['total_amount']= $total_amount;
            $ret = $m_purchase_detail->addData($data);
            if($ret){
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
            $where = [];
            $where['id'] = $unit_id;
            $unit_info = $m_unit->field('convert_type')->where($where)->find();
            $total_amount = intval($unit_info['convert_type'] * $amount);  //总瓶数
            
            $m_purchase_detail = new \Admin\Model\PurchaseDetailModel();
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
                $this->outputNew('编辑成功!', 'inventorypurchase/detaillist');
                
            }else {
                $this->error('编辑失败');
            }
            
        }
    }
    public function deldetail(){
        $purchase_id = I('get.purchase_id',0,'intval');
        $id          = I('get.id',0,'intval');
        $m_purchase_detail = new \Admin\Model\PurchaseDetailModel();
        $where= [];
        $where['id'] = $id;
        $where['purchase_id'] = $purchase_id;
        $data = [];
        $data['status'] = 2;
        $ret = $m_purchase_detail->updateData($where, $data);
        if($ret){
            $this->output('删除成功!', 'inventorypurchase/detaillist','');
        }else {
            $this->error('删除失败');
        }
    }
}