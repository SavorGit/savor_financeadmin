<?php
namespace Admin\Controller;

class StockController extends BaseController {

    public function inlist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');
        $area_id = I('area_id',0,'intval');
        $io_type = I('io_type',0,'intval');
        $department_id = I('department_id',0,'intval');
        $supplier_id = I('supplier_id',0,'intval');

        $where = array();
        if(!empty($keyword)){
            $where['a.name'] = array('like',"$keyword");
        }
        if($area_id){
            $where['a.area_id'] = $area_id;
        }
        if($department_id){
            $where['a.department_id'] = $department_id;
        }
        if($io_type){
            $where['a.io_type'] = $io_type;
        }else{
            $io_types = C('STOCK_IN_TYPES');
            $where['a.io_type'] = array('in',array_keys($io_types));
        }
        if($supplier_id){
            $where['p.supplier_id'] = $supplier_id;
        }
        $area_arr = $department_arr = $supplier_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }
        $m_department = new \Admin\Model\DepartmentModel();
        $res_departments = $m_department->getAll('id,name',array('status'=>1),0,1000,'id asc');
        foreach ($res_departments as $v){
            $department_arr[$v['id']]=$v;
        }
        $m_supplier = new \Admin\Model\SupplierModel();
        $res_supplier = $m_supplier->getAll('id,name',array('status'=>1),0,1000,'id asc');
        foreach ($res_supplier as $v){
            $supplier_arr[$v['id']]=$v;
        }

        $start = ($pageNum-1)*$size;
        $fields = 'a.*,p.purchase_date,p.amount as purchase_amount,p.status as purchase_status,
        p.department_user_id as purchase_department_user_id,p.supplier_id';
        $m_stock = new \Admin\Model\StockModel();
        $res_list = $m_stock->getList($fields,$where,'a.id desc', $start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $v['supplier'] = $supplier_arr[$v['supplier_id']]['name'];
                $v['area'] = $area_arr[$v['area_id']]['region_name'];
                $v['department'] = $department_arr[$v['department_id']]['name'];
                $data_list[] = $v;
            }
        }

        $this->assign('area_id', $area_id);
        $this->assign('department_id', $department_id);
        $this->assign('io_type', $io_type);
        $this->assign('supplier_id', $supplier_id);
        $this->assign('supplier_arr', $supplier_arr);
        $this->assign('departments', $res_departments);
        $this->assign('area', $area_arr);
        $this->assign('keyword',$keyword);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function addinstock(){
        $id = I('id',0,'intval');
        $m_stock = new \Admin\Model\StockModel();
        if(IS_POST){
            $serial_number = I('post.serial_number','','trim');
            $name = I('post.name','','trim');
            $io_type = I('post.io_type',0,'intval');
            $io_date = I('post.io_date','');
            $department_id = I('post.department_id',0,'intval');
            $department_user_id = I('post.department_user_id',0,'intval');
            $purchase_id = I('post.purchase_id',0,'intval');
            $area_id = I('post.area_id',0,'intval');

            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            $data = array('serial_number'=>$serial_number,'name'=>$name,'io_type'=>$io_type,'io_date'=>$io_date,'area_id'=>$area_id,
                'department_id'=>$department_id,'department_user_id'=>$department_user_id,'purchase_id'=>$purchase_id,'type'=>10,
                'sysuser_id'=>$sysuser_id
            );
            if($id){
                $result = $m_stock->updateData(array('id'=>$id),$data);
            }else{
                $data['update_time'] = date('Y-m-d H:i:s');
                $result = $m_stock->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'stock/inlist');
            }else{
                $this->output('操作失败', 'stock/addinstock',2,0);
            }
        }else{
            $area_arr = $department_arr = $departmentuser_arr = $purchase_arr = array();
            $m_area  = new \Admin\Model\AreaModel();
            $res_area = $m_area->getHotelAreaList();
            foreach ($res_area as $v){
                $area_arr[$v['id']]=$v;
            }
            $m_department = new \Admin\Model\DepartmentModel();
            $res_departments = $m_department->getAll('id,name',array('status'=>1),0,1000,'id asc');
            foreach ($res_departments as $v){
                $department_arr[$v['id']]=$v;
            }
            $m_department_user = new \Admin\Model\DepartmentUserModel();
            $res_department_users = $m_department_user->getAll('id,name',array('status'=>1),0,10000,'id asc');
            foreach ($res_department_users as $v){
                $departmentuser_arr[$v['id']]=$v;
            }
            $m_purchase = new \Admin\Model\PurchaseModel();
            $res_purchase  = $m_purchase->getAll('id,name,serial_number',array('status'=>1),0,1000000,'id asc');
            foreach ($res_purchase as $v){
                $purchase_arr[$v['id']]=$v;
            }

            $vinfo = array('status'=>1);
            if($id){
                $vinfo = $m_stock->getInfo(array('id'=>$id));
            }
            $this->assign('departmentuser_arr',$departmentuser_arr);
            $this->assign('purchase_arr',$purchase_arr);
            $this->assign('area_arr',$area_arr);
            $this->assign('department_arr',$department_arr);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function instockgoodslist(){
        $stock_id = I('stock_id',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');

        $start = ($pageNum-1)*$size;
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $fields = 'a.id,goods.barcode,a.goods_id,a.unit_id,goods.name,cate.name as category';
        $where = array('a.stock_id'=>$stock_id,'a.status'=>1);
        if(!empty($keyword)){
            $where['goods.name'] = array('like',"%$keyword%");
        }
        $res_list = $m_stock_detail->getList($fields,$where, 'a.id desc', $start,$size);
        $data_list = array();
        if(!empty($res_list)){
            $m_unit = new \Admin\Model\UnitModel();
            $res_unit = $m_unit->getDataList('id,name',array('status'=>1),'id desc');
            $all_unit = array();
            foreach ($res_unit as $v){
                $all_unit[$v['id']]=$v;
            }
            foreach ($res_list['list'] as $v){
                $v['unit']=$all_unit[$v['unit_id']]['name'];
                $data_list[]=$v;
            }
        }

        $this->assign('stock_id',$stock_id);
        $this->assign('keyword',$keyword);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function instockgoodsadd(){
        $stock_id = I('stock_id',0,'intval');
        $id = I('id',0,'intval');

        $m_pdetail = new \Admin\Model\PurchaseDetailModel();
        if(IS_POST){
            $purchase_detail_id = I('post.purchase_detail_id',0,'intval');
            $res_info = $m_pdetail->getInfo(array('id'=>$purchase_detail_id));

            $data = array('stock_id'=>$stock_id,'goods_id'=>$res_info['goods_id'],'price'=>$res_info['price'],
                'unit_id'=>$res_info['unit_id'],'status'=>1);
            $m_stock_detail = new \Admin\Model\StockDetailModel();
            if($id){
                $m_stock_detail->updateData(array('id'=>$id),$data);
            }else{
                $m_stock_detail->add($data);
            }
            $this->output('操作成功!', 'stock/instockgoodslist');
        }else{
            $m_stock = new \Admin\Model\StockModel();
            $res_stock = $m_stock->getInfo(array('id'=>$stock_id));

            $all_purchase_detail = array();
            if($res_stock['purchase_id']){
                $where = array('a.purchase_id'=>$res_stock['purchase_id']);
                $fields = 'a.id,a.goods_id,g.name';
                $all_purchase_detail = $m_pdetail->getList($fields,$where,'a.id desc');
            }
            $this->assign('stock_id',$stock_id);
            $this->assign('id',$id);
            $this->assign('all_purchase_detail',$all_purchase_detail);
            $this->display();
        }
    }


    public function outlist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');
        $io_type = I('io_type',0,'intval');
        $department_id = I('department_id',0,'intval');

        $io_types = C('STOCK_OUT_TYPES');
        $where = array('type'=>20);
        if(!empty($keyword)){
            $where['name'] = array('like',"$keyword");
        }
        if($department_id){
            $where['department_id'] = $department_id;
        }
        if($io_type){
            $where['io_type'] = $io_type;
        }
        $department_arr = $departmentuser_arr = array();

        $m_department = new \Admin\Model\DepartmentModel();
        $res_departments = $m_department->getAll('id,name',array('status'=>1),0,1000,'id asc');
        foreach ($res_departments as $v){
            $department_arr[$v['id']]=$v;
        }
        $m_department_user = new \Admin\Model\DepartmentUserModel();
        $res_department_users = $m_department_user->getAll('id,name',array('status'=>1),0,10000,'id asc');
        foreach ($res_department_users as $v){
            $departmentuser_arr[$v['id']]=$v;
        }

        $start = ($pageNum-1)*$size;
        $m_stock = new \Admin\Model\StockModel();
        $res_list = $m_stock->getDataList('*',$where,'id desc',$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $v['department'] = $department_arr[$v['department_id']]['name'];
                $v['department_user'] = $departmentuser_arr[$v['department_user_id']]['name'];
                $v['io_type_str'] = $io_types[$v['io_type']];
                $data_list[] = $v;
            }
        }
        $this->assign('department_id', $department_id);
        $this->assign('io_type', $io_type);
        $this->assign('departments', $res_departments);
        $this->assign('keyword',$keyword);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function addoutstock(){
        $id = I('id',0,'intval');
        $m_stock = new \Admin\Model\StockModel();
        if(IS_POST){
            $serial_number = I('post.serial_number','','trim');
            $name = I('post.name','','trim');
            $department_user_id = I('post.department_user_id',0,'intval');
            $department_id = I('post.department_id',0,'intval');
            $io_date = I('post.io_date','');
            $amount = I('post.amount',0,'intval');
            $total_fee = I('post.total_fee',0,'intval');
            $io_type = I('post.io_type',0,'intval');
            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            $data = array('serial_number'=>$serial_number,'name'=>$name,'io_type'=>$io_type,'io_date'=>$io_date,
                'department_id'=>$department_id,'department_user_id'=>$department_user_id,'amount'=>$amount,'total_fee'=>$total_fee,
                'type'=>20,'sysuser_id'=>$sysuser_id
            );
            if($id){
                $result = $m_stock->updateData(array('id'=>$id),$data);
            }else{
                $data['update_time'] = date('Y-m-d H:i:s');
                $result = $m_stock->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'stock/outlist');
            }else{
                $this->output('操作失败', 'stock/addoutstock',2,0);
            }
        }else{
            $department_arr = $departmentuser_arr = array();

            $m_department = new \Admin\Model\DepartmentModel();
            $res_departments = $m_department->getAll('id,name',array('status'=>1),0,1000,'id asc');
            foreach ($res_departments as $v){
                $department_arr[$v['id']]=$v;
            }
            $m_department_user = new \Admin\Model\DepartmentUserModel();
            $res_department_users = $m_department_user->getAll('id,name',array('status'=>1),0,10000,'id asc');
            foreach ($res_department_users as $v){
                $departmentuser_arr[$v['id']]=$v;
            }
            $vinfo = array('status'=>1);
            if($id){
                $vinfo = $m_stock->getInfo(array('id'=>$id));
            }
            $this->assign('departmentuser_arr',$departmentuser_arr);
            $this->assign('department_arr',$department_arr);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function outstockgoodslist(){
        $stock_id = I('stock_id',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');

        $start = ($pageNum-1)*$size;
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $fields = 'a.id,goods.barcode,a.goods_id,a.amount,a.unit_id,goods.name,cate.name as category';
        $where = array('a.stock_id'=>$stock_id,'a.status'=>1);
        if(!empty($keyword)){
            $where['goods.name'] = array('like',"%$keyword%");
        }
        $res_list = $m_stock_detail->getList($fields,$where, 'a.id desc', $start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_unit = new \Admin\Model\UnitModel();
            $res_unit = $m_unit->getDataList('id,name',array('status'=>1),'id desc');
            $all_unit = array();
            foreach ($res_unit as $v){
                $all_unit[$v['id']]=$v;
            }
            foreach ($res_list['list'] as $v){
                $v['unit']=$all_unit[$v['unit_id']]['name'];
                $data_list[]=$v;
            }
        }

        $this->assign('stock_id',$stock_id);
        $this->assign('keyword',$keyword);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function outstockgoodsadd(){
        $stock_id = I('stock_id',0,'intval');
        $id = I('id',0,'intval');
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        if(IS_POST){
            $goods_id = I('goods_id',0,'intval');
            $unit_id = I('unit_id',0,'intval');
            $data = array('stock_id'=>$stock_id,'goods_id'=>$goods_id,'unit_id'=>$unit_id,'status'=>1);
            if($id){
                $m_stock_detail->updateData(array('id'=>$id),$data);
            }else{
                $m_stock_detail->add($data);
            }
            $this->output('操作成功!', 'stock/outstockgoodslist');
        }else{
            $vinfo = array();
            if(!empty($id)){
                $vinfo = $m_stock_detail->getInfo(array('id'=>$id));
            }
            $all_goods = $m_stock_detail->getStockGoods();
            $this->assign('stock_id',$stock_id);
            $this->assign('id',$id);
            $this->assign('all_goods',$all_goods);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function getAjaxStockUnit(){
        $goods_id = I('goods_id',0,'intval');
        $unit_id = 0;
        $units = array();
        $m_unit = new \Admin\Model\UnitModel();
        $res_unit = $m_unit->getStockUnitByGoods($goods_id);
        if(!empty($res_unit)){
            foreach ($res_unit as $v){
                $is_select = '';
                if($v['id']==$unit_id){
                    $is_select = 'selected';
                }
                $v['is_select'] = $is_select;
                $units[]=$v;
            }
        }
        $res_data = array('units'=>$units);
        die(json_encode($res_data));
    }

}