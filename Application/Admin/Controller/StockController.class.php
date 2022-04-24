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

        $where = array('type'=>10);
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
        $area_arr = $department_arr = $supplier_arr = $departmentuser_arr = array();
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
        $m_department_user = new \Admin\Model\DepartmentUserModel();
        $res_department_users = $m_department_user->getAll('id,name',array('status'=>1),0,10000,'id asc');
        foreach ($res_department_users as $v){
            $departmentuser_arr[$v['id']]=$v;
        }
        $start = ($pageNum-1)*$size;
        $fields = 'a.*,p.purchase_date,p.amount as purchase_amount,p.status as purchase_status,
        p.department_user_id as purchase_department_user_id,p.supplier_id';
        $m_stock = new \Admin\Model\StockModel();
        $res_list = $m_stock->getList($fields,$where,'a.id desc', $start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_stock_record = new \Admin\Model\StockRecordModel();
            foreach ($res_list['list'] as $v){
                $v['supplier'] = $supplier_arr[$v['supplier_id']]['name'];
                $v['area'] = $area_arr[$v['area_id']]['region_name'];
                $v['department'] = $department_arr[$v['department_id']]['name'];
                $v['purchase_department_username'] = $departmentuser_arr[$v['purchase_department_user_id']]['name'];
                $v['department_username'] = $departmentuser_arr[$v['department_user_id']]['name'];
                $now_amount = 0;
                $field='sum(total_amount) as total_amount';
                $res_stock_record = $m_stock_record->getRow($field,array('stock_id'=>$v['id'],'type'=>1));
                if(!empty($res_stock_record['total_amount'])){
                    $now_amount = intval($res_stock_record['total_amount']);
                }
                $v['now_amount'] = $now_amount;
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
            $res_department_users = $m_department_user->getAll('id,name,department_id',array('status'=>1),0,10000,'id asc');
            foreach ($res_department_users as $v){
                $v['name'] = $department_arr[$v['department_id']]['name'].'-'.$v['name'];
                $departmentuser_arr[$v['id']]=$v;
            }
            $m_purchase = new \Admin\Model\PurchaseModel();
            $res_purchase  = $m_purchase->getAll('id,name,serial_number',array(),0,1000000,'id asc');
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

    public function stockgoodsrecordlist(){
        $stock_detail_id = I('stock_detail_id',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $type = I('type',0,'intval');

        $start = ($pageNum-1)*$size;
        $m_stock_reord = new \Admin\Model\StockRecordModel();
        $fields = 'a.id,a.idcode,a.price,goods.barcode,a.goods_id,a.unit_id,goods.name,cate.name as category';
        $where = array('a.stock_detail_id'=>$stock_detail_id);
        if($type){
            $where['a.type'] = $type;
        }

        $res_list = $m_stock_reord->getList($fields,$where, 'a.id desc', $start,$size);
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
                $v['price']=abs($v['price']);
                $data_list[]=$v;
            }
        }

        $this->assign('stock_detail_id',$stock_detail_id);
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

            $data = array('stock_id'=>$stock_id,'purchase_detail_id'=>$purchase_detail_id,
                'goods_id'=>$res_info['goods_id'],'unit_id'=>$res_info['unit_id'],
                'stock_amount'=>$res_info['amount'],'stock_total_amount'=>$res_info['total_amount'],'status'=>1);
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
                $fields = 'a.id,a.unit_id,a.goods_id,g.name,u.name as unit_name';
                $all_purchase_detail = $m_pdetail->getList($fields,$where,'a.id desc');
                foreach ($all_purchase_detail as $k=>$v){
                    $all_purchase_detail[$k]['name'] = $v['name'].'-'.$v['unit_name'];
                }
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
            $use_type = I('post.use_type',0,'intval');
            $area_id = I('post.area_id',0,'intval');
            $hotel_id = I('post.hotel_id',0,'intval');
            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            $data = array('serial_number'=>$serial_number,'name'=>$name,'io_type'=>$io_type,'use_type'=>$use_type,'io_date'=>$io_date,
                'department_id'=>$department_id,'department_user_id'=>$department_user_id,'amount'=>$amount,'total_fee'=>$total_fee,
                'area_id'=>$area_id,'hotel_id'=>$hotel_id,'type'=>20,'sysuser_id'=>$sysuser_id
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
            $department_arr = $departmentuser_arr = $area_arr = array();

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
            $res_department_users = $m_department_user->getAll('id,name,department_id',array('status'=>1),0,10000,'id asc');
            foreach ($res_department_users as $v){
                $v['name'] = $department_arr[$v['department_id']]['name'].'-'.$v['name'];
                $departmentuser_arr[$v['id']]=$v;
            }
            $m_hotel = new \Admin\Model\HotelModel();
            $hotel_list = $m_hotel->getDataList('id,name,area_id',array('type'=>1),'area_id asc');
            foreach ($hotel_list as $k=>$v){
                $hotel_list[$k]['name'] = "{$area_arr[$v['area_id']]['region_name']}--".$v['name'];
            }

            $vinfo = array('status'=>1);
            if($id){
                $vinfo = $m_stock->getInfo(array('id'=>$id));
            }
            $this->assign('departmentuser_arr',$departmentuser_arr);
            $this->assign('department_arr',$department_arr);
            $this->assign('area_arr',$area_arr);
            $this->assign('hotel_list',$hotel_list);
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
            $stock_amount = I('stock_amount',0,'intval');

            $m_unit = new \Admin\Model\UnitModel();
            $res_unit = $m_unit->getInfo(array('id'=>$unit_id));
            $stock_total_amount = $res_unit['convert_type']*$stock_amount;
            $data = array('stock_id'=>$stock_id,'goods_id'=>$goods_id,'unit_id'=>$unit_id,
                'stock_amount'=>$stock_amount,'stock_total_amount'=>$stock_total_amount,'status'=>1);
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

    public function stocklist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');
        $area_id = I('area_id',0,'intval');
        $category_id = I('category_id',0,'intval');

        $where = array();
        if(!empty($keyword)){
            $where['goods.name'] = array('like',"%$keyword%");
        }
        if($category_id){
            $where['goods.category_id'] = $category_id;
        }
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $all_goods = $m_stock_detail->getAllData('goods_id','','','goods_id');
        if(!empty($all_goods)){
            $goods_ids = array();
            foreach ($all_goods as $v){
                $goods_ids[]=$v['goods_id'];
            }
            $where['goods.id']=array('in',$goods_ids);
        }

        $start = ($pageNum-1)*$size;
        $fields = 'goods.id as goods_id,goods.barcode,goods.name,cate.name as category';
        $m_goods = new \Admin\Model\GoodsModel();
        $res_list = $m_goods->getList($fields,$where, 'goods.id desc', $start,$size);
        $data_list = array();

        $area_arr = $category_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }
        $m_category = new \Admin\Model\CategoryModel();
        $res_category = $m_category->getAll('id,name',array('status'=>1),0,1000,'id asc');
        foreach ($res_category as $v){
            $category_arr[$v['id']]=$v;
        }
        if(!empty($res_list['list'])){
            $m_stock_record = new \Admin\Model\StockRecordModel();
            foreach ($res_list['list'] as $v){
                $goods_id = $v['goods_id'];
                $fields = 'sum(total_fee) as total_fee,sum(total_amount) as total_amount';
                $res_goods_record = $m_stock_record->getAll($fields,array('goods_id'=>$goods_id,'type'=>array('in',array(1,2,3))),0,1,'id desc');
                $price = $total_fee = $stock_num = 0;
                if(!empty($res_goods_record[0]['total_fee']) && !empty($res_goods_record[0]['total_amount'])){
                    $total_fee = intval($res_goods_record[0]['total_fee']);
                    $stock_num = intval($res_goods_record[0]['total_amount']);
                    $price = intval($total_fee/$stock_num);
                }
                $v['price'] = $price;
                $v['stock_num'] = $stock_num;
                $v['total_fee'] = $total_fee;
                $v['area'] = $area_arr[$v['area_id']]['region_name'];
                $data_list[] = $v;
            }
        }

        $this->assign('area_id', $area_id);
        $this->assign('category_id', $category_id);
        $this->assign('area', $area_arr);
        $this->assign('category', $category_arr);
        $this->assign('keyword',$keyword);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function stockchangelist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $goods_id = I('goods_id',0,'intval');
        $type = I('type',0,'intval');
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        $where = array('a.goods_id'=>$goods_id);
        if($type){
            $where['a.type'] = $type;
        }
        if(!empty($start_time) && !empty($end_time)){
            $now_start_time = date('Y-m-d 00:00:00',strtotime($start_time));
            $now_end_time = date('Y-m-d 23:59:59',strtotime($end_time));
            $where['a.add_time'] = array(array('egt',$now_start_time),array('elt',$now_end_time));
        }
        $departments = $specifications = $units = array();
        $m_department = new \Admin\Model\DepartmentModel();
        $res_departments = $m_department->getAll('id,name',array('status'=>1),0,1000,'id asc');
        foreach ($res_departments as $v){
            $departments[$v['id']]=$v;
        }

        $m_spec = new \Admin\Model\SpecificationModel();
        $res_spec = $m_spec->getDataList('id,name',array('status'=>1),'id desc');
        foreach ($res_spec as $v){
            $specifications[$v['id']]=$v;
        }
        $m_unit = new \Admin\Model\UnitModel();
        $res_unit = $m_unit->getDataList('id,name',array('status'=>1),'id desc');
        foreach ($res_unit as $v){
            $units[$v['id']]=$v;
        }
        $all_types = array('1'=>'入库','2'=>'出库','3'=>'拆箱');

        $start = ($pageNum-1)*$size;
        $fields = 'a.id,goods.name,goods.specification_id,a.unit_id,stock.department_id,a.type,stock.serial_number,sum(a.amount) as amount,sum(a.total_amount) as total_amount,a.add_time';
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_list = $m_stock_record->getChangeList($fields,$where, 'a.id desc', 'a.batch_no',$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $v['department']=$departments[$v['department_id']]['name'];
                $v['specification']=$specifications[$v['specification_id']]['name'];
                $v['unit']=$units[$v['unit_id']]['name'];
                $v['type_str'] = $all_types[$v['type']];
                if($v['total_amount']>0){
                    $v['total_amount'] = '+'.$v['total_amount'];
                }
                $data_list[] = $v;
            }
        }

        $this->assign('start_time', $start_time);
        $this->assign('end_time', $end_time);
        $this->assign('all_types', $all_types);
        $this->assign('type', $type);
        $this->assign('goods_id', $goods_id);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function stockgoodsdetail(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $goods_id = I('goods_id',0,'intval');

        $departments = $specifications = $units = array();
        $m_department = new \Admin\Model\DepartmentModel();
        $res_departments = $m_department->getAll('id,name',array('status'=>1),0,1000,'id asc');
        foreach ($res_departments as $v){
            $departments[$v['id']]=$v;
        }

        $m_spec = new \Admin\Model\SpecificationModel();
        $res_spec = $m_spec->getDataList('id,name',array('status'=>1),'id desc');
        foreach ($res_spec as $v){
            $specifications[$v['id']]=$v;
        }
        $m_unit = new \Admin\Model\UnitModel();
        $res_unit = $m_unit->getDataList('id,name',array('status'=>1),'id desc');
        foreach ($res_unit as $v){
            $units[$v['id']]=$v;
        }

        $where = array('a.goods_id'=>$goods_id,'a.status'=>1,'stock.type'=>10);
        $start = ($pageNum-1)*$size;
        $fields = 'a.id,goods.id as goods_id,goods.name,goods.barcode,goods.specification_id,a.unit_id,stock.department_id,stock.serial_number,a.amount,stock.io_date';
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $res_list = $m_stock_detail->getChangeList($fields,$where, 'a.id desc', $start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $v['department']=$departments[$v['department_id']]['name'];
                $v['specification']=$specifications[$v['specification_id']]['name'];
                $v['unit']=$units[$v['unit_id']]['name'];

                $m_stock_record = new \Admin\Model\StockRecordModel();
                $fields = 'sum(total_amount) as total_amount';
                $rwhere = array('goods_id'=>$goods_id,'unit_id'=>$v['unit_id'],'type'=>array('in',array(1,2,3)));
                $res_goods_inrecord = $m_stock_record->getAll($fields,$rwhere,0,1);
                $now_amount = 0;
                if(!empty($res_goods_inrecord)){
                    $now_amount = $res_goods_inrecord[0]['total_amount'];
                }
                $v['now_amount'] = $now_amount;
                $data_list[] = $v;
            }
        }
        $this->assign('goods_id', $goods_id);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function stockgoodsrecord(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $goods_id = I('goods_id',0,'intval');
        $unit_id = I('unit_id',0,'intval');

        $areas = $departmentusers = $units = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $areas[$v['id']]=$v;
        }
        $m_department_user = new \Admin\Model\DepartmentUserModel();
        $res_department_users = $m_department_user->getAll('id,name',array('status'=>1),0,10000,'id asc');
        foreach ($res_department_users as $v){
            $departmentusers[$v['id']]=$v;
        }
        $m_unit = new \Admin\Model\UnitModel();
        $res_unit = $m_unit->getDataList('id,name',array('status'=>1),'id desc');
        foreach ($res_unit as $v){
            $units[$v['id']]=$v;
        }

        $where = array('a.goods_id'=>$goods_id,'a.unit_id'=>$unit_id,'a.type'=>array('in',array(1,3)),'a.status'=>0);
        $start = ($pageNum-1)*$size;
        $fields = 'a.*,goods.name,goods.specification_id,stock.serial_number,stock.area_id';
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_list = $m_stock_record->getRecordList($fields,$where, 'a.id desc', $start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $all_op_user = C('STOCK_MANAGER');
            foreach ($res_list['list'] as $v){
                $v['area']=$areas[$v['area_id']]['region_name'];
                $v['unit']=$units[$v['unit_id']]['name'];
                $v['department_user']=$departmentusers[$v['department_user_id']]['name'];
                $v['op_user'] = $all_op_user[$v['op_openid']];
                $data_list[] = $v;
            }
        }
        $this->assign('goods_id',$goods_id);
        $this->assign('unit_id',$unit_id);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
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