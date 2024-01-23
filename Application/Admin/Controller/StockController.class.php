<?php
namespace Admin\Controller;

class StockController extends BaseController {

    public $stock_serial_number_prefix = array(
        '1'=>array('in'=>'BJRK','out'=>'BJCK'),
        '9'=>array('in'=>'SHRK','out'=>'SHCK'),
        '236'=>array('in'=>'GZRK','out'=>'GZCK'),
        '246'=>array('in'=>'SZRK','out'=>'SZCK'),
        '248'=>array('in'=>'FSRK','out'=>'FSCK'),
        );

    public $clean_writeoff_uid = array(361);//364 yingtao

    public function inlist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');
        $area_id = I('area_id',0,'intval');
        $io_type = I('io_type',0,'intval');
        $department_id = I('department_id',0,'intval');
        $supplier_id = I('supplier_id',0,'intval');
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        $where = array('type'=>10);
        if(!empty($keyword)){
            $where['a.name'] = array('like',"%$keyword%");
        }
        if($area_id){
            $where['a.area_id'] = $area_id;
        }
        if($department_id){
            $where['a.department_id'] = $department_id;
        }
        $io_types = C('STOCK_IN_TYPES');
        if($io_type){
            $where['a.io_type'] = $io_type;
        }else{
            $where['a.io_type'] = array('in',array_keys($io_types));
        }
        if($supplier_id){
            $where['p.supplier_id'] = $supplier_id;
        }
        if(empty($start_time) || empty($end_time)){
            $start_time = date('Y-m-d',strtotime('-1 month'));
            $end_time = date('Y-m-d');
        }
        $now_start_time = date('Y-m-d',strtotime($start_time));
        $now_end_time = date('Y-m-d',strtotime($end_time));
        $where['a.io_date'] = array(array('egt',$now_start_time),array('elt',$now_end_time));

        $area_arr = $department_list = $supplier_arr = $departmentuser_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }
        $res_departments = $this->getDepartmentTree(2);
        $m_department = new \Admin\Model\DepartmentModel();
        $rp_departments = $m_department->getAll('id,name',array('status'=>1),0,1000,'id asc');
        foreach ($rp_departments as $v){
            $department_list[$v['id']]=$v;
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
            $u8_start_date = C('U8_START_DATE');
            foreach ($res_list['list'] as $v){
                $v['supplier'] = $supplier_arr[$v['supplier_id']]['name'];
                $v['area'] = $area_arr[$v['area_id']]['region_name'];
                $v['department'] = $department_list[$v['department_id']]['name'];
                $v['purchase_department_username'] = $departmentuser_arr[$v['purchase_department_user_id']]['name'];
                $v['department_username'] = $departmentuser_arr[$v['department_user_id']]['name'];
                $now_amount = 0;
                $now_total_fee = 0;
                $field='sum(total_amount) as total_amount,sum(total_fee) as total_fee';
                $res_stock_record = $m_stock_record->getRow($field,array('stock_id'=>$v['id'],'type'=>1));
                if(!empty($res_stock_record['total_amount'])){
                    $now_amount = intval($res_stock_record['total_amount']);
                }
                if(!empty($res_stock_record['total_fee'])){
                    $now_total_fee = intval($res_stock_record['total_fee']);
                }
                $u8_start = 0;
                if($v['io_date']>=$u8_start_date && $v['io_type']==11){
                    $u8_start = 1;
                }
                $v['u8_start'] = $u8_start;
                $v['now_total_fee'] = $now_total_fee;
                $v['now_amount'] = $now_amount;
                $v['io_type_str'] = $io_types[$v['io_type']];
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
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
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
            $io_type = I('post.io_type',0,'intval');//11采购入库,12调拨入库,13餐厅退回
            $io_date = I('post.io_date','');
            $department_id = I('post.department_id',0,'intval');
            $department_user_id = I('post.department_user_id',0,'intval');
            $purchase_id = I('post.purchase_id',0,'intval');
            $area_id = I('post.area_id',0,'intval');
            $total_money = I('post.total_money',0,'intval');

            if($io_type==11){
                if($purchase_id==0){
                    $this->output('请关联采购单', 'stock/addinstock',2,0);
                }
            }else{
                if($purchase_id>0){
                    $in_types = C('STOCK_IN_TYPES');
                    $this->output("入库类型【{$in_types['io_type']}】不能关联采购单", 'stock/addinstock',2,0);
                }
            }
            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            $data = array('serial_number'=>$serial_number,'name'=>$name,'io_type'=>$io_type,'io_date'=>$io_date,'area_id'=>$area_id,
                'department_id'=>$department_id,'department_user_id'=>$department_user_id,'purchase_id'=>$purchase_id,'type'=>10,
                'sysuser_id'=>$sysuser_id,'total_money'=>$total_money
            );
            if($id){
                $result = $m_stock->updateData(array('id'=>$id),$data);
            }else{
                $nowdate = date('Ymd');
                $field = 'count(id) as num';
                $where = array('type'=>10,'area_id'=>$area_id,'DATE_FORMAT(add_time, "%Y%m%d")'=>$nowdate);
                $res_stock = $m_stock->getAll($field,$where,0,1);
                if($res_stock[0]['num']>0){
                    $number = $res_stock[0]['num']+1;
                }else{
                    $number = 1;
                }
                $num_str = str_pad($number,3,'0',STR_PAD_LEFT);
                $serial_number = $this->stock_serial_number_prefix[$area_id]['in'].$nowdate.$num_str;
                $data['serial_number'] = $serial_number;
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
            //$m_department = new \Admin\Model\DepartmentModel();
            //$res_departments = $m_department->getAll('id,name',array('status'=>1),0,1000,'id asc');
            $res_departments = $this->getDepartmentTree(2);
            foreach ($res_departments as $v){
                $department_arr[$v['id']]=$v;
            }
            /* $m_department_user = new \Admin\Model\DepartmentUserModel();
            $res_department_users = $m_department_user->getAll('id,name,department_id',array('status'=>1),0,10000,'id asc');
            foreach ($res_department_users as $v){
                $v['name'] = $department_arr[$v['department_id']]['name'].'-'.$v['name'];
                $departmentuser_arr[$v['id']]=$v;
            } */
            $departmentuser_arr = [];
            $m_purchase = new \Admin\Model\PurchaseModel();
            $res_purchase  = $m_purchase->getAll('id,name,serial_number',array(),0,1000000,'id asc');
            foreach ($res_purchase as $v){
                $purchase_arr[$v['id']]=$v;
            }

            $vinfo = array('status'=>1,'id'=>0);
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
        $fields = 'a.id,goods.barcode,a.goods_id,a.unit_id,a.price,a.rate,goods.name,cate.name as category';
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
                $price = $rate = $norate_price = $rate_money = '';
                if($v['price']>0 && $v['rate']>0){
                    $price = $v['price'];
                    $rate_percent = $v['rate']*100;
                    $rate = $rate_percent.'%';
                    $rate_money = sprintf("%.2f",$v['price']*$v['rate']);
                    $norate_price = $price - $rate_money;
                }
                $v['price'] = $price;
                $v['rate'] = $rate;
                $v['norate_price'] = $norate_price;
                $v['rate_money'] = $rate_money;
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
        $fields = 'a.id,a.idcode,a.price,goods.barcode,a.goods_id,a.unit_id,goods.name,cate.name as category,stock.hotel_id';
        $where = array('a.stock_detail_id'=>$stock_detail_id);
        if($type){
            $where['a.type'] = $type;
        }

        $res_list = $m_stock_reord->getList($fields,$where, 'a.id desc', $start,$size);
        $data_list = array();
        if(!empty($res_list)){
            $m_price_template_hotel = new \Admin\Model\PriceTemplateHotelModel();
            $m_unit = new \Admin\Model\UnitModel();
            $res_unit = $m_unit->getDataList('id,name,convert_type',array('status'=>1),'id desc');
            $all_unit = array();
            foreach ($res_unit as $v){
                $all_unit[$v['id']]=$v;
            }
            foreach ($res_list['list'] as $v){
                $settlement_price = $m_price_template_hotel->getHotelGoodsPrice($v['hotel_id'],$v['goods_id']);
                $goods_settlement_price = $settlement_price * $all_unit[$v['unit_id']]['convert_type'];
                $v['unit']=$all_unit[$v['unit_id']]['name'];
                $v['price']=abs($v['price']);
                $v['settlement_price'] = $goods_settlement_price;
                $data_list[]=$v;
            }
        }

        $this->assign('stock_detail_id',$stock_detail_id);
        $this->assign('type',$type);
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
            $goods_id = I('post.goods_id',0,'intval');
            $unit_id = I('post.unit_id',0,'intval');
            $rate = I('post.rate',0);

            $stock_amount = $stock_total_amount = 0;
            $price = 0;
            if($purchase_detail_id>0){
                $hwhere = array('stock_id'=>$stock_id,'purchase_detail_id'=>$purchase_detail_id);
                $res_info = $m_pdetail->getInfo(array('id'=>$purchase_detail_id));
                $goods_id = $res_info['goods_id'];
                $unit_id = $res_info['unit_id'];
                $stock_amount = $res_info['amount'];
                $stock_total_amount = $res_info['total_amount'];

                $m_unit = new \Admin\Model\UnitModel();
                $res_unit = $m_unit->getInfo(array('id'=>$unit_id));
                $total_amount = $res_unit['convert_type']*1;
                $total_fee = $res_info['price'];
                $price = sprintf("%.2f",$total_fee/$total_amount);//单瓶价格
            }else{
                $hwhere = array('stock_id'=>$stock_id,'goods_id'=>$goods_id);
                $m_stock_record = new \Admin\Model\StockRecordModel();
                $res_record = $m_stock_record->getAll('price,total_fee',array('goods_id'=>$goods_id,'unit_id'=>$unit_id,'type'=>1),0,1,'id asc','');
                if(!empty($res_record)){
                    $price = $res_record[0]['price'];
                }
            }
            if($id){
                $hwhere['id']= array('neq',$id);
            }
            $m_stock_detail = new \Admin\Model\StockDetailModel();
            $res_has = $m_stock_detail->getInfo($hwhere);
            if(!empty($res_has)){
                $this->output('商品不能重复', "stock/instockgoodsadd", 2, 0);
            }
            $m_stock = new \Admin\Model\StockModel();
            $res_stock = $m_stock->getInfo(array('id'=>$stock_id));
            $res_detail_num = $m_stock_detail->getDataList('count(id) as num',array('stock_id'=>$stock_id),'id desc');
            if($res_stock['io_type']==11 && $res_detail_num[0]['num']>1){
                $this->output('采购入库单下入库商品只能添加1个', "stock/instockgoodsadd", 2, 0);
            }

            $data = array('stock_id'=>$stock_id,'purchase_detail_id'=>$purchase_detail_id,
                'goods_id'=>$goods_id,'rate'=>$rate,'price'=>$price,'unit_id'=>$unit_id,'stock_amount'=>$stock_amount,
                'stock_total_amount'=>$stock_total_amount,'status'=>1);
            if($id){
                $m_stock_detail->updateData(array('id'=>$id),$data);
            }else{
                $m_stock_detail->add($data);
            }
            $this->output('操作成功!', 'stock/instockgoodslist');
        }else{
            $m_stock = new \Admin\Model\StockModel();
            $res_stock = $m_stock->getInfo(array('id'=>$stock_id));
            $is_purchase = 0;
            $all_goods = array();
            if(in_array($res_stock['io_type'],array(12,13)) && $res_stock['purchase_id']==0){
                $m_goods = new \Admin\Model\GoodsModel();
                $all_goods = $m_goods->getDataList('id,name',array('status'=>1),'brand_id asc,id asc');
            }

            $all_purchase_detail = array();
            if($res_stock['purchase_id']){
                $is_purchase = 1;
                $where = array('a.purchase_id'=>$res_stock['purchase_id']);
                $fields = 'a.id,a.unit_id,a.goods_id,g.name,u.name as unit_name';
                $all_purchase_detail = $m_pdetail->getList($fields,$where,'a.id desc');
                foreach ($all_purchase_detail as $k=>$v){
                    $all_purchase_detail[$k]['name'] = $v['name'].'-'.$v['unit_name'];
                }
            }
            $this->assign('is_purchase',$is_purchase);
            $this->assign('stock_id',$stock_id);
            $this->assign('id',$id);
            $this->assign('all_purchase_detail',$all_purchase_detail);
            $this->assign('all_goods',$all_goods);
            $this->display();
        }
    }

    public function instockgoodsdel(){
        $id = I('get.id',0,'intval');
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_stock_record = $m_stock_record->getInfo(array('stock_detail_id'=>$id));
        if(!empty($res_stock_record)){
            $this->output('已有入/出库记录,无法删除', 'stock/instockgoodslist',2,0);
        }
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $result = $m_stock_detail->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'stock/instockgoodslist',2);
        }else{
            $this->output('操作失败', 'stock/instockgoodslist',2,0);
        }
    }

    public function outlist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');
        $io_type = I('io_type',0,'intval');
        $department_id = I('department_id',0,'intval');
        $area_id = I('area_id',0,'intval');

        $io_types = C('STOCK_OUT_TYPES');
        $where = array('type'=>20);
        if(!empty($keyword)){
            $where['name'] = array('like',"%$keyword%");
        }
        if($department_id){
            $where['department_id'] = $department_id;
        }
        if($io_type){
            $where['io_type'] = $io_type;
        }
        if($area_id){
            $where['area_id'] = $area_id;
        }
        $department_arr = $departmentuser_arr = $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }

        $res_departments = $this->getDepartmentTree(2);
        foreach ($res_departments as $v){
            $department_arr[$v['id']]=$v;
        }
        $m_department = new \Admin\Model\DepartmentModel();
        $rp_departments = $m_department->getAll('id,name',array('status'=>1),0,1000,'id asc');
        foreach ($rp_departments as $v){
            $department_list[$v['id']]=$v;
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
            $m_user = new \Admin\Model\SmallappUserModel();
            foreach ($res_list['list'] as $v){
                $v['department'] = $department_list[$v['department_id']]['name'];
                $v['department_user'] = $departmentuser_arr[$v['department_user_id']]['name'];
                $v['io_type_str'] = $io_types[$v['io_type']];
                $receive_username = '';
                if(!empty($v['receive_openid'])){
                    $res_user = $m_user->getInfo(array('openid'=>$v['receive_openid']));
                    $receive_username = $res_user['nickname'];
                }
                $v['receive_username'] = $receive_username;
                $data_list[] = $v;
            }
        }
        $this->assign('area', $area_arr);
        $this->assign('area_id', $area_id);
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
            if(empty($io_date)){
                $this->output('请选择出库日期', 'stock/addoutstock',2,0);
            }

            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            $data = array('serial_number'=>$serial_number,'name'=>$name,'io_type'=>$io_type,'use_type'=>$use_type,'io_date'=>$io_date,
                'department_id'=>$department_id,'department_user_id'=>$department_user_id,'amount'=>$amount,'total_fee'=>$total_fee,
                'area_id'=>$area_id,'hotel_id'=>$hotel_id,'type'=>20,'sysuser_id'=>$sysuser_id
            );
            if(in_array($io_type,array(22,23)) && $hotel_id>0){
                $m_hotel = new \Admin\Model\HotelModel();
                $res_hotel = $m_hotel->getHotelById('ext.is_salehotel',array('hotel.id'=>$hotel_id));
                if($res_hotel['is_salehotel']==0){
                    $this->output('请先设置当前酒楼为售酒餐厅', 'stock/addoutstock',2,0);
                }
                $is_check_out = $m_stock->checkHotelThreshold($hotel_id);
                if($is_check_out==0){
                    $this->output('已超过出库阀值，不能出库', 'stock/addoutstock',2,0);
                }
            }
            if($id){
                /*
                $stock_info = $m_stock->getInfo(array('id'=>$id));
                if($stock_info['status']>=2 && $stock_info['io_type']!=$io_type){
                    $this->output('请勿修改出库类型', 'stock/addoutstock',2,0);
                }
                */
                $result = $m_stock->updateData(array('id'=>$id),$data);
            }else{
                $nowdate = date('Ymd');
                $field = 'count(id) as num';
                $where = array('type'=>20,'area_id'=>$area_id,'DATE_FORMAT(add_time, "%Y%m%d")'=>$nowdate);
                $res_stock = $m_stock->getAll($field,$where,0,1);
                if($res_stock[0]['num']>0){
                    $number = $res_stock[0]['num']+1;
                }else{
                    $number = 1;
                }
                $num_str = str_pad($number,3,'0',STR_PAD_LEFT);
                $serial_number = $this->stock_serial_number_prefix[$area_id]['out'].$nowdate.$num_str;
                $data['serial_number'] = $serial_number;
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
            /* $m_department = new \Admin\Model\DepartmentModel();
            $res_departments = $m_department->getAll('id,name',array('status'=>1),0,1000,'id asc');
            foreach ($res_departments as $v){
                $department_arr[$v['id']]=$v;
            } */
            $res_departments = $this->getDepartmentTree(2);
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
            $hotel_list = $m_hotel->getDataList('id,name,area_id',array('type'=>array('in',array(1,5)),'state'=>1,'flag'=>0),'area_id asc');
            foreach ($hotel_list as $k=>$v){
                $hotel_list[$k]['name'] = "{$area_arr[$v['area_id']]['region_name']}--".$v['name'];
            }

            $vinfo = array('status'=>1,'id'=>0);
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

    public function outstockdel(){
        $stock_id = I('get.stock_id',0,'intval');
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $res_data = $m_stock_detail->getAllData('count(id) as num', array('stock_id'=>$stock_id));
        $out_detail_num = intval($res_data[0]['num']);
        if($out_detail_num>0){
            $this->output('已有出库记录,无法删除', 'stock/outlist',2,0);
        }
        $m_stock = new \Admin\Model\StockModel();
        $result = $m_stock->delData(array('id'=>$stock_id));
        if($result){
            $this->output('操作成功!', 'stock/outlist',2);
        }else{
            $this->output('操作失败', 'stock/outlist',2,0);
        }

    }

    public function outstockgoodslist(){
        $stock_id = I('stock_id',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');

        $start = ($pageNum-1)*$size;
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $fields = 'a.id,goods.barcode,a.goods_id,a.amount,a.unit_id,a.stock_amount,goods.name,cate.name as category';
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

            $hwhere = array('stock_id'=>$stock_id,'goods_id'=>$goods_id,'unit_id'=>$unit_id);
            if($id){
                $hwhere['id']= array('neq',$id);
            }
            $res_has = $m_stock_detail->getInfo($hwhere);
            if(!empty($res_has)){
                $this->output('商品不能重复', "stock/outstockgoodsadd", 2, 0);
            }
            $m_unit = new \Admin\Model\UnitModel();
            $res_unit = $m_unit->getInfo(array('id'=>$unit_id));
            if($res_unit['convert_type']==1){//查询单瓶和整箱的总数是否够用
                $fields='sum(total_amount) as amount';
                $res_udetail = $m_stock_detail->getDataList($fields,array('goods_id'=>$goods_id),'');
                $now_stock_num = intval($res_udetail[0]['amount']);
            }else{
                $fields='sum(total_amount) as amount';
                $res_udetail = $m_stock_detail->getDataList($fields,array('goods_id'=>$goods_id,'unit_id'=>$unit_id),'');
                $now_stock_num = intval($res_udetail[0]['amount']);
            }
            $stock_total_amount = $res_unit['convert_type']*$stock_amount;
            if($stock_total_amount>$now_stock_num){
                $this->output('当前库存不能满足出库数量', "stock/outstockgoodsadd", 2, 0);
            }

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

    public function outstockgoodsdel(){
        $id = I('get.id',0,'intval');
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_stock_record = $m_stock_record->getInfo(array('stock_detail_id'=>$id));
        if(!empty($res_stock_record)){
            $this->output('已有入/出库记录,无法删除', 'stock/outstockgoodslist',2,0);
        }
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $result = $m_stock_detail->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'stock/outstockgoodslist',2);
        }else{
            $this->output('操作失败', 'stock/outstockgoodslist',2,0);
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
            $m_avg_price = new \Admin\Model\GoodsAvgpriceModel();
            $m_companystock = new \Admin\Model\CompanyStockModel();
            foreach ($res_list['list'] as $v){
                $goods_id = $v['goods_id'];

                $stock_where = array('goods_id'=>$goods_id);
                if($area_id>0){
                    $stock_where['area_id'] = $area_id;
                }
                $res_stock = $m_companystock->getRow('sum(num) as stock_num',$stock_where);
                $stock_num = intval($res_stock['stock_num']);

//                $fields = 'sum(a.total_fee) as total_fee,sum(a.total_amount) as total_amount';
//                $swhere = array('a.goods_id'=>$goods_id,'a.type'=>array('in',array(1,2)),'a.dstatus'=>1);
//                if($area_id){
//                    $swhere['stock.area_id'] = $area_id;
//                }
//                $res_goods_record = $m_stock_record->getAllStock($fields,$swhere,'a.id desc','');
//                $stock_num = intval($res_goods_record[0]['total_amount']);

                $res_price = $m_avg_price->getAll('price',array('goods_id'=>$goods_id),0,1,'id desc');
                $avg_price = $res_price[0]['price'];
                $total_fee = $avg_price*$stock_num;
                $v['price'] = $avg_price;
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

    public function stockidcodes(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $goods_id = I('goods_id',0,'intval');
        $area_id = I('area_id',0,'intval');
        $idcode = I('idcode','','trim');

        $where = array('goods_id'=>$goods_id);
        if($area_id){
            $where['area_id'] = $area_id;
        }
        if(!empty($idcode)){
            $where['idcode'] = $idcode;
        }
        $start = ($pageNum-1)*$size;
        $m_company_stock_detail = new \Admin\Model\CompanyStockDetailModel();
        $res_list = $m_company_stock_detail->getDataList('*',$where,'type asc',$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                if($v['type']==1){
                    $type_str = '箱码';
                }else{
                    $type_str = '瓶码';
                }
                $v['type_str'] = $type_str;
                $data_list[] = $v;
            }
        }
        $this->assign('idcode', $idcode);
        $this->assign('goods_id', $goods_id);
        $this->assign('area_id', $area_id);
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
        $area_id = I('area_id',0,'intval');
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $all_types = array('1'=>'入库','2'=>'出库','3'=>'拆箱');

        $where = array('a.goods_id'=>$goods_id,'a.dstatus'=>1);
        if($type){
            $where['a.type'] = $type;
        }else{
            $where['a.type'] = array('in',array_keys($all_types));
        }
        if($area_id){
            $where['stock.area_id'] = $area_id;
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
        $this->assign('area_id', $area_id);
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
        $area_id = I('area_id',0,'intval');

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
        if($area_id){
            $where['stock.area_id'] = $area_id;
        }
        $start = ($pageNum-1)*$size;
        $fields = 'a.id,goods.id as goods_id,goods.name,goods.barcode,goods.specification_id,a.unit_id,stock.department_id,stock.serial_number,a.amount,stock.io_date';
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $group = 'a.unit_id';
        $res_list = $m_stock_detail->getChangeList($fields,$where, 'a.id desc', $group,$start,$size);

        $data_list = array();
        if(!empty($res_list['list'])){
            $m_stock_record = new \Admin\Model\StockRecordModel();
            foreach ($res_list['list'] as $v){
                $v['department']=$departments[$v['department_id']]['name'];
                $v['specification']=$specifications[$v['specification_id']]['name'];
                $v['unit']=$units[$v['unit_id']]['name'];

                $fields = 'sum(a.total_amount) as total_amount';
                $rwhere = array('a.goods_id'=>$goods_id,'a.unit_id'=>$v['unit_id'],'a.type'=>array('in',array(1,2)),'a.dstatus'=>1);
                if($area_id){
                    $rwhere['stock.area_id'] = $area_id;
                }
                $res_goods_inrecord = $m_stock_record->getAllStock($fields,$rwhere,'','','0,1');
                $now_amount = 0;
                if(!empty($res_goods_inrecord)){
                    $now_amount = $res_goods_inrecord[0]['total_amount'];
                }
                $v['now_amount'] = $now_amount;
                $data_list[] = $v;
            }
        }
        $this->assign('area_id', $area_id);
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
        $area_id = I('area_id',0,'intval');
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

        $where = array('a.goods_id'=>$goods_id,'a.unit_id'=>$unit_id,'a.type'=>array('in',array(1,3)),'a.status'=>0,'a.dstatus'=>1);
        if($area_id){
            $where['stock.area_id'] = $area_id;
        }
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
        $this->assign('area_id',$area_id);
        $this->assign('goods_id',$goods_id);
        $this->assign('unit_id',$unit_id);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function hotelstocklist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $area_id = I('area_id',0,'intval');
        $hotel_name = I('hotel_name','','trim');

        $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }
        $where = array('stock.hotel_id'=>array('gt',0),'stock.type'=>20);
        $sysuserInfo = session('sysUserInfo');
        if(!in_array($sysuserInfo['id'],array(344,345,361,362,363,364))){
            $test_hotels = C('TEST_HOTEL');
            $test_hotels[]=0;
            $where['stock.hotel_id'] = array('not in',$test_hotels);
        }

        if(!empty($hotel_name)){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }
        if($area_id){
            $where['stock.area_id'] = $area_id;
        }

        $start = ($pageNum-1)*$size;
        $fileds = 'a.goods_id,stock.area_id,goods.name,goods.barcode,cate.name as cate_name,spec.name as sepc_name,a.unit_id,unit.name as unit_name,hotel.id as hotel_id,hotel.name as hotel_name';
        $group = 'stock.hotel_id,a.goods_id,a.unit_id';
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $res_list = $m_stock_detail->getHotelStockGoods($fileds,$where,$group,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_stock_record = new \Admin\Model\StockRecordModel();
            foreach ($res_list['list'] as $v){
                $out_num = $unpack_num = $wo_num = $report_num = 0;
                $price = 0;
                $goods_id = $v['goods_id'];
                $unit_id = $v['unit_id'];
                $rfileds = 'sum(a.total_amount) as total_amount,sum(a.total_fee) as total_fee,a.type';
                $rwhere = array('stock.hotel_id'=>$v['hotel_id'],'stock.type'=>20,'stock.io_type'=>22,'a.goods_id'=>$goods_id,'a.unit_id'=>$unit_id,'a.dstatus'=>1);
                $rwhere['a.type'] = array('in',array(2,3));
                $rgroup = 'a.type';
                $res_record = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','',$rgroup);
                foreach ($res_record as $rv){
                    switch ($rv['type']){
                        case 2:
                            $out_num = abs($rv['total_amount']);
                            $total_fee = abs($rv['total_fee']);
                            $price = intval($total_fee/$out_num);
                            break;
                        case 3:
                            $unpack_num = $rv['total_amount'];
                            break;
                    }
                }
                $rwhere['a.type']=7;
                $rwhere['a.wo_status']= array('in',array(1,2,4));
                $res_worecord = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','','');
                if(!empty($res_worecord[0]['total_amount'])){
                    $wo_num = $res_worecord[0]['total_amount'];
                }

                $rwhere['a.type']=6;
                unset($rwhere['a.wo_status']);
                $rwhere['a.status']= array('in',array(1,2));
                $res_worecord = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','','');
                if(!empty($res_worecord[0]['total_amount'])){
                    $report_num = $res_worecord[0]['total_amount'];
                }

//                $stock_num = $out_num+$unpack_num+$wo_num+$report_num;
                $stock_num = $out_num+$wo_num+$report_num;
                $m_price_template_hotel = new \Admin\Model\PriceTemplateHotelModel();
                $settlement_price = $m_price_template_hotel->getHotelGoodsPrice($v['hotel_id'],$goods_id,1);
                $v['settlement_price'] = $settlement_price;
                $v['price'] = $price;
                $v['stock_num'] = $stock_num;
                $v['total_fee'] = $price*$stock_num;
                $v['area_name'] = $area_arr[$v['area_id']]['region_name'];
                $data_list[] = $v;
            }
        }

        $this->assign('area',$area_arr);
        $this->assign('area_id',$area_id);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function hotelstockchangelist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $type = I('type',0,'intval');
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $goods_id = I('goods_id',0,'intval');
        $unit_id = I('unit_id',0,'intval');
        $hotel_id = I('hotel_id',0,'intval');

//        $all_types = array('2'=>'出库','3'=>'拆箱','6'=>'报损','7'=>'核销');
        $all_types = array('2'=>'出库','6'=>'报损','7'=>'核销');

        $where = array('stock.hotel_id'=>$hotel_id,'stock.type'=>20,'stock.io_type'=>22,'a.goods_id'=>$goods_id,'a.unit_id'=>$unit_id,'a.dstatus'=>1);
        if($type){
            $where['a.type'] = $type;
        }else{
            $where['a.type'] = array('in',array_keys($all_types));
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

        $start = ($pageNum-1)*$size;
        $fields = 'a.id,a.type,a.batch_no,goods.name,goods.specification_id,a.unit_id,stock.department_id,a.type,stock.serial_number,sum(a.amount) as amount,sum(a.total_amount) as total_amount,a.add_time';
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_list = $m_stock_record->getChangeList($fields,$where, 'a.id desc', 'a.batch_no',$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $v['department']=$departments[$v['department_id']]['name'];
                $v['specification']=$specifications[$v['specification_id']]['name'];
                $v['unit']=$units[$v['unit_id']]['name'];
                $v['type_str'] = $all_types[$v['type']];
                if($v['type']==2){
                    $v['total_amount'] = abs($v['total_amount']);
                }else{
                    if($v['type']==7){
                        $wowhere = array('stock.hotel_id'=>$hotel_id,'a.goods_id'=>$goods_id,'a.unit_id'=>$unit_id,
                            'a.type'=>7,'a.batch_no'=>$v['batch_no'],'a.dstatus'=>1);
                        $wowhere['a.wo_status']= array('in',array(1,2,4));
                        $res_wonum = $m_stock_record->getAllStock('sum(a.total_amount) as total_amount',$wowhere,'a.id desc');
                        if(!empty($res_wonum[0]['total_amount'])){
                            $v['total_amount'] = $res_wonum[0]['total_amount'];
                        }
                    }
                }

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
        $this->assign('unit_id', $unit_id);
        $this->assign('hotel_id', $hotel_id);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function stockhotelrecordlist(){
        $batch_no = I('batch_no',0,'intval');
        $goods_id = I('goods_id',0,'intval');
        $unit_id = I('unit_id',0,'intval');
        $hotel_id = I('hotel_id',0,'intval');
        $type = I('type',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $start = ($pageNum-1)*$size;
        $m_stock_reord = new \Admin\Model\StockRecordModel();
        $fields = 'a.id,a.idcode,a.price,goods.barcode,a.goods_id,a.unit_id,goods.name,cate.name as category';
        $where = array('a.batch_no'=>$batch_no,'a.goods_id'=>$goods_id,'a.unit_id'=>$unit_id,'a.dstatus'=>1,'stock.hotel_id'=>$hotel_id);
        if($type==7){
            $where['a.wo_status'] = array('in',array(1,2,4));
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

        $this->assign('batch_no',$batch_no);
        $this->assign('goods_id',$goods_id);
        $this->assign('unit_id',$unit_id);
        $this->assign('hotel_id',$hotel_id);
        $this->assign('type',$type);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function writeofflist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $wo_status = I('wo_status',0,'intval');
        $wo_reason_type = I('wo_reason_type',0,'intval');
        $area_id = I('area_id',0,'intval');
        $recycle_status = I('recycle_status',0,'intval');
        $push_u8_status13 = I('push_u8_status13',99,'intval');
        $idcode = I('idcode','','trim');
        $hotel_name = I('hotel_name','','trim');
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        $all_wo_status = C('STOCK_WRITEOFF_STATUS');
        $all_reason = C('STOCK_USE_TYPE');
        $all_recycle_status = C('STOCK_RECYLE_STATUS');
        $area_arr = $departmentuser_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }
        $m_department_user = new \Admin\Model\DepartmentUserModel();
        $res_department_users = $m_department_user->getAll('id,name',array('status'=>1),0,10000,'id asc');
        foreach ($res_department_users as $v){
            $departmentuser_arr[$v['id']]=$v;
        }

        $where = array('a.type'=>7);
        if($wo_status){
            $where['a.wo_status'] = $wo_status;
        }
        if($wo_reason_type){
            $where['a.wo_reason_type'] = $wo_reason_type;
        }
        if($recycle_status){
            $where['a.recycle_status'] = $recycle_status;
        }
        if($area_id){
            $where['hotel.area_id'] = $area_id;
        }
        if(!empty($idcode)){
            $where['a.idcode'] = $idcode;
        }
        if(!empty($hotel_name)){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }
        if(!empty($start_time) && !empty($end_time)){
            $now_start_time = date('Y-m-d 00:00:00',strtotime($start_time));
            $now_end_time = date('Y-m-d 23:59:59',strtotime($end_time));
            $where['a.add_time'] = array(array('egt',$now_start_time),array('elt',$now_end_time));
        }
        if($push_u8_status13<99){
            $where['sale.push_u8_status13'] = $push_u8_status13;
        }
        $userinfo = session('sysUserInfo');
        $sysuser_id = $userinfo['id'];
        $is_clean_writeoff = in_array($sysuser_id,$this->clean_writeoff_uid)?1:0;

        $start = ($pageNum-1)*$size;
        $fields = 'a.*,goods.name,goods.specification_id,unit.name as unit_name,stock.serial_number,sale.id as sale_id,
        stock.area_id,hotel.name as hotel_name,hotel.id as hotel_id,sale.settlement_price,su.remark as residenter_name';
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_list = $m_stock_record->getRecordList($fields,$where, 'a.id desc', $start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $u8_start_date = C('U8_START_DATE');
            $all_op_user = C('STOCK_MANAGER');
            $oss_host = get_oss_host();
            $m_user = new \Admin\Model\SmallappUserModel();
            $m_pushu8_record = new \Admin\Model\Pushu8RecordModel();
            foreach ($res_list['list'] as $v){
                $push_status = -1;
                $push_u8_url = '';
                if($v['add_time']>="$u8_start_date 00:00:00"  && $v['wo_status']==2 && in_array($v['wo_reason_type'],array(1,2))){
                    $res_pushu8 = $m_pushu8_record->getInfo(array('sale_id'=>$v['sale_id'],'type'=>21));
                    $push_status = intval($res_pushu8['status']);
                    if($v['wo_reason_type']==1){
                        $push_u8_url = 'u8cloud/sellvoucher1';
                    }else{
                        $push_u8_url = 'u8cloud/sellvoucher3';
                    }
                }

                $v['push_status'] = $push_status;
                $v['push_u8_url'] = $push_u8_url;
                $v['department_user'] = $departmentuser_arr[$v['department_user_id']]['name'];
                $v['op_user'] = $all_op_user[$v['op_openid']];
                $v['wo_reason_type_str'] = $all_reason[$v['wo_reason_type']];
                $imgs = array();
                if(!empty($v['wo_data_imgs'])){
                    $tmp_imgs = explode(',',$v['wo_data_imgs']);
                    foreach ($tmp_imgs as $iv){
                        if(!empty($iv)){
                            $imgs[]=$oss_host.$iv;
                        }
                    }
                }
                $recycle_status_str = '';
                if(isset($all_recycle_status[$v['recycle_status']])){
                    $recycle_status_str = $all_recycle_status[$v['recycle_status']];
                }
                $v['recycle_status_str']=$recycle_status_str;
                $v['wo_status_str']=$all_wo_status[$v['wo_status']];
                $v['imgs']=$imgs;
                $res_user = $m_user->getInfo(array('openid'=>$v['op_openid']));
                $v['username'] = $res_user['nickname'];
                $v['usermobile'] = $res_user['mobile'];
//                $price = abs($v['price']);
                $price = $v['avg_price'];
                if($price==0){
                    $map1['idcode']=$v['idcode'];
                    $map2['goods_id']=$v['goods_id'];
                    $avg_where['_complex'] = array(
                        $map1,
                        $map2,
                        '_logic' => 'or'
                    );
                    $avg_where['avg_price'] = array('gt',0);
                    $avg_where['add_time'] = array('elt',$v['add_time']);
                    $res_avg_price = $m_stock_record->getAll('avg_price',$avg_where,0,1,'id desc');
                    $price = $res_avg_price[0]['avg_price'];
                }
                $price = abs($price);
                $total_amount = abs($v['total_amount']);
                $settlement_price = $v['settlement_price'];
                $v['price'] = sprintf("%.2f",$price*$total_amount);
                $v['settlement_price'] = sprintf("%.2f",$settlement_price*$total_amount);
                $data_list[] = $v;
            }
        }
        $this->assign('push_u8_status13', $push_u8_status13);
        $this->assign('area_id', $area_id);
        $this->assign('area', $area_arr);
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('idcode',$idcode);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('wo_reason_type',$wo_reason_type);
        $this->assign('wo_status',$wo_status);
        $this->assign('recycle_status',$recycle_status);
        $this->assign('all_wo_status',$all_wo_status);
        $this->assign('all_wo_reason',$all_reason);
        $this->assign('is_clean_writeoff',$is_clean_writeoff);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function auditwriteoff(){
        $id = I('id',0,'intval');

        if(IS_POST){
            $wo_status = I('post.wo_status',0,'intval');
            $is_manual = I('post.is_manual',0,'intval');
            $integral = I('post.integral',0,'intval');
            $wo_reason_type = I('post.wo_reason_type',0,'intval');//'1'=>'餐厅售卖','2'=>'品鉴酒','3'=>'活动'
            if($is_manual==1 && $integral==0){
                $this->output('请输入积分', "stock/writeofflist", 2, 0);
            }

            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            $condition = array('id'=>$id);
            $data = array('audit_user_id'=>$sysuser_id,'wo_reason_type'=>$wo_reason_type,'wo_status'=>$wo_status,'update_time'=>date('Y-m-d H:i:s'));
            $m_stock_record = new \Admin\Model\StockRecordModel();
            $m_stock_record->updateData($condition, $data);
            if($wo_status==2){
                $res_record = $m_stock_record->getInfo(array('id'=>$id));
                $goods_id = $res_record['goods_id'];
                $idcode = $res_record['idcode'];
                $m_goodsconfig = new \Admin\Model\GoodsConfigModel();

                if($is_manual==1){
                    $res_goodsintegral = array('integral'=>$integral);
                }else{
                    $res_goodsintegral = $m_goodsconfig->getInfo(array('goods_id'=>$goods_id,'type'=>10));
                }
                if(!empty($res_goodsintegral) && $res_goodsintegral['integral']>0){
                    $now_integral = $res_goodsintegral['integral'];
                    if($is_manual==0){
                        $m_unit = new \Admin\Model\UnitModel();
                        $res_unit = $m_unit->getInfo(array('id'=>$res_record['unit_id']));
                        $unit_num = intval($res_unit['convert_type']);
                        $now_integral = $now_integral*$unit_num;
                    }
                    $integral_status = 1;
                    $is_recycle = 0;
                    /*
                    $res_goodsrecycle = $m_goodsconfig->getInfo(array('goods_id'=>$goods_id,'type'=>20,'status'=>1));
                    $auto_audit_start_time = '2023-10-08 00:00:00';
                    if($res_record['add_time']>=$auto_audit_start_time){
                        $res_goodsrecycle = '';
                        $m_stock_record->updateData($condition, array('recycle_status'=>2,'recycle_time'=>date('Y-m-d H:i:s')));
                    }
                    if(!empty($res_goodsrecycle)){
                        $is_recycle = 1;
                        $integral_status = 2;
                        $m_stock_record->updateData($condition, array('recycle_status'=>1));
                    }
                    */
                    $m_stock = new \Admin\Model\StockModel();
                    $res_stock = $m_stock->getInfo(array('id'=>$res_record['stock_id']));
                    if($res_stock['hotel_id']>0 && $wo_reason_type==1){
                        $m_staff = new \Admin\Model\StaffModel();
                        $m_merchant = new \Admin\Model\MerchantModel();
                        $m_userintegral = new \Admin\Model\UserIntegralModel();
                        $m_integralrecord = new \Admin\Model\UserIntegralrecordModel();
                        $m_hotel = new \Admin\Model\HotelModel();
                        $field = 'hotel.id as hotel_id,hotel.name as hotel_name,hotel.hotel_box_type,area.id as area_id,area.region_name as area_name';
                        $where = array('hotel.id'=>$res_stock['hotel_id']);
                        $res_hotel = $m_hotel->getHotelById($field,$where);

                        $where = array('a.openid'=>$res_record['op_openid'],'a.status'=>1,'merchant.status'=>1);
                        $field_staff = 'a.openid,a.level,merchant.type,merchant.id as merchant_id,merchant.is_integral,merchant.is_shareprofit,merchant.shareprofit_config';
                        $res_staff = $m_staff->getMerchantStaff($field_staff,$where);
                        $admin_integral = 0;
                        $admin_openid = '';
						
						$adminwhere = array('merchant_id'=>$res_staff[0]['merchant_id'],'level'=>1,'status'=>1);
                        $res_admin_staff = $m_staff->getAll('id,openid',$adminwhere,0,1,'id desc');
						$admin_openid = $res_admin_staff[0]['openid'];
                        if($res_staff[0]['is_integral']==1){
                            //开瓶费积分 增加分润
                            if($res_staff[0]['is_shareprofit']==1 && $res_staff[0]['level']==2){
                                $shareprofit_config = json_decode($res_staff[0]['shareprofit_config'],true);
                                if(!empty($shareprofit_config['kpf'])){
                                    $staff_integral = ($shareprofit_config['kpf'][1]/100)*$now_integral;
                                    if($staff_integral>1){
                                        $staff_integral = round($staff_integral);
                                    }else{
                                        $staff_integral = 1;
                                    }
                                    $admin_integral = $now_integral - $staff_integral;
                                    $now_integral = $staff_integral;
                                }
                            }
                            $integralrecord_openid = $res_record['op_openid'];
                            if($is_recycle==0){
                                if($admin_integral>0){
                                    if(!empty($res_admin_staff)){
                                        $m_userintegral = new \Admin\Model\Smallapp\UserIntegralModel();
                                        $res_integral = $m_userintegral->getInfo(array('openid'=>$admin_openid));
                                        if(!empty($res_integral)){
                                            $userintegral = $res_integral['integral']+$admin_integral;
                                            $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                                        }else{
                                            $m_userintegral->add(array('openid'=>$admin_openid,'integral'=>$admin_integral));
                                        }
                                    }
                                }
                                $res_integral = $m_userintegral->getInfo(array('openid'=>$res_record['op_openid']));
                                if(!empty($res_integral)){
                                    $userintegral = $res_integral['integral']+$now_integral;
                                    $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                                }else{
                                    $m_userintegral->add(array('openid'=>$res_record['op_openid'],'integral'=>$now_integral));
                                }
                            }
                        }else{
                            $integralrecord_openid = $res_stock['hotel_id'];
                            if($is_recycle==0){
                                $where = array('id'=>$res_staff[0]['merchant_id']);
                                $m_merchant->where($where)->setInc('integral',$now_integral);
                            }
                        }
                        if($admin_integral>0 && !empty($admin_openid)){
                            $integralrecord_data = array('openid'=>$admin_openid,'area_id'=>$res_hotel['area_id'],'area_name'=>$res_hotel['area_name'],
                                'hotel_id'=>$res_hotel['hotel_id'],'hotel_name'=>$res_hotel['hotel_name'],'hotel_box_type'=>$res_hotel['hotel_box_type'],
                                'integral'=>$admin_integral,'jdorder_id'=>$id,'content'=>1,'status'=>$integral_status,
                                'type'=>17,'integral_time'=>date('Y-m-d H:i:s'),'source'=>4);
                            $m_integralrecord->add($integralrecord_data);
                        }
                        $integralrecord_data = array('openid'=>$integralrecord_openid,'area_id'=>$res_hotel['area_id'],'area_name'=>$res_hotel['area_name'],
                            'hotel_id'=>$res_hotel['hotel_id'],'hotel_name'=>$res_hotel['hotel_name'],'hotel_box_type'=>$res_hotel['hotel_box_type'],
                            'integral'=>$now_integral,'jdorder_id'=>$id,'content'=>1,'status'=>$integral_status,'type'=>17,
                            'integral_time'=>date('Y-m-d H:i:s'));
                        $m_integralrecord->add($integralrecord_data);
                        //end

                        //邀请新会员(优惠券任务) 审核通过后立即发放积分
                        $res_recordinfo = $m_integralrecord->getAllData('*',array('jdorder_id'=>$idcode,'type'=>18,'status'=>2),'id desc');
                        if(!empty($res_recordinfo)){
                            $where = array('hotel_id'=>$res_recordinfo[0]['hotel_id'],'status'=>1);
                            $field_merchant = 'id as merchant_id,is_integral,is_shareprofit,shareprofit_config';
                            $res_merchant = $m_merchant->getRow($field_merchant,$where,'id desc');
                            $is_integral = $res_merchant['is_integral'];
                            foreach ($res_recordinfo as $v){
                                $m_integralrecord->updateData(array('id'=>$v['id']),array('status'=>1,'integral_time'=>date('Y-m-d H:i:s')));

                                $now_integral = $v['integral'];
                                if($is_integral==1){
                                    $res_integral = $m_userintegral->getInfo(array('openid'=>$v['openid']));
                                    if(!empty($res_integral)){
                                        $userintegral = $res_integral['integral']+$now_integral;
                                        $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                                    }else{
                                        $m_userintegral->add(array('openid'=>$v['openid'],'integral'=>$now_integral));
                                    }
                                }else{
                                    $where = array('id'=>$res_merchant['merchant_id']);
                                    $m_merchant->where($where)->setInc('integral',$now_integral);
                                }
                            }
                        }
                        //end
                        //会员复购奖励 增加分润
                        $res_recordinfo = $m_integralrecord->getAllData('*',array('jdorder_id'=>$idcode,'type'=>19,'status'=>2),'id desc');
                        if(!empty($res_recordinfo)){
                            $where = array('hotel_id'=>$res_recordinfo[0]['hotel_id'],'status'=>1);
                            $field_merchant = 'id as merchant_id,is_integral,is_shareprofit,shareprofit_config';
                            $res_merchant = $m_merchant->getRow($field_merchant,$where,'id desc');
                            $is_integral = $res_merchant['is_integral'];
                            foreach ($res_recordinfo as $v){
                                $m_integralrecord->updateData(array('id'=>$v['id']),array('status'=>1,'integral_time'=>date('Y-m-d H:i:s')));

                                $now_integral = $v['integral'];
                                if($is_integral==1){
                                    $res_integral = $m_userintegral->getInfo(array('openid'=>$v['openid']));
                                    if(!empty($res_integral)){
                                        $userintegral = $res_integral['integral']+$now_integral;
                                        $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                                    }else{
                                        $m_userintegral->add(array('openid'=>$v['openid'],'integral'=>$now_integral));
                                    }
                                }else{
                                    $where = array('id'=>$res_merchant['merchant_id']);
                                    $m_merchant->where($where)->setInc('integral',$now_integral);
                                }
                            }
                        }
                        //end
                    }
                }
            }else{
                if($wo_status==3){
                    $m_sale = new \Admin\Model\SaleModel();
                    $res_sale = $m_sale->getInfo(array('stock_record_id'=>$id));
                    if(!empty($res_sale)){
                        $m_sale->delData(array('id'=>$res_sale['id']));
                    }
                }
            }
            $this->output('操作完成', 'stock/writeofflist');
        }else{
            $condition = array('id'=>$id);
            $m_stock_record = new \Admin\Model\StockRecordModel();
            $res_info = $m_stock_record->getInfo($condition);
            $this->assign('vinfo',$res_info);
            $this->display();
        }
    }

    public function auditrecycle(){
        $this->output('审核物料回收功能暂停使用', "stock/writeofflist", 2, 0);
        $id = I('id',0,'intval');
        $condition = array('id'=>$id);
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_info = $m_stock_record->getInfo($condition);
        $idcode = $res_info['idcode'];
        if(IS_POST){
            if($res_info['wo_status']!=2){
                $this->output('请先完成审核核销状态', "stock/writeofflist", 2, 0);
            }
            if($res_info['recycle_status']==2){
                $this->output('请勿重复进行审核回收', "stock/writeofflist", 2, 0);
            }
            $status = I('post.recycle_status',0,'intval');
            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            $condition = array('id'=>$id);
            $data = array('recycle_audit_user_id'=>$sysuser_id,'recycle_status'=>$status);
            if($status==2){
                $data['recycle_time'] = date('Y-m-d H:i:s');
                $m_stock_record = new \Admin\Model\StockRecordModel();
                $m_stock_record->updateData($condition, $data);

                $m_integralrecord = new \Admin\Model\UserIntegralrecordModel();
                $m_userintegral = new \Admin\Model\UserIntegralModel();
                $m_merchant = new \Admin\Model\MerchantModel();
                $m_staff = new \Admin\Model\StaffModel();
                $res_recordinfo = $m_integralrecord->getAllData('*',array('jdorder_id'=>$id,'type'=>17,'status'=>2),'id desc');
                if(!empty($res_recordinfo)){
                    $where = array('hotel_id'=>$res_recordinfo[0]['hotel_id'],'status'=>1);
                    $field_merchant = 'id as merchant_id,is_integral,is_shareprofit,shareprofit_config';
                    $res_merchant = $m_merchant->getRow($field_merchant,$where,'id desc');
                    $is_integral = $res_merchant['is_integral'];
                    foreach ($res_recordinfo as $v){
                        $m_integralrecord->updateData(array('id'=>$v['id']),array('status'=>1,'integral_time'=>date('Y-m-d H:i:s')));
                        $now_integral = $v['integral'];
                        if($is_integral==1){
                            $res_integral = $m_userintegral->getInfo(array('openid'=>$v['openid']));
                            if(!empty($res_integral)){
                                $userintegral = $res_integral['integral']+$now_integral;
                                $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                            }else{
                                $m_userintegral->add(array('openid'=>$v['openid'],'integral'=>$now_integral));
                            }
                        }else{
                            $where = array('id'=>$res_merchant['merchant_id']);
                            $m_merchant->where($where)->setInc('integral',$now_integral);
                        }
                    }
                }

                $res_recordinfo = $m_integralrecord->getAllData('*',array('jdorder_id'=>$idcode,'type'=>18,'status'=>2),'id desc');
                if(!empty($res_recordinfo)){
                    $where = array('hotel_id'=>$res_recordinfo[0]['hotel_id'],'status'=>1);
                    $field_merchant = 'id as merchant_id,is_integral,is_shareprofit,shareprofit_config';
                    $res_merchant = $m_merchant->getRow($field_merchant,$where,'id desc');
                    $is_integral = $res_merchant['is_integral'];
                    foreach ($res_recordinfo as $v){
                        $m_integralrecord->updateData(array('id'=>$v['id']),array('status'=>1,'integral_time'=>date('Y-m-d H:i:s')));

                        $now_integral = $v['integral'];
                        if($is_integral==1){
                            $res_integral = $m_userintegral->getInfo(array('openid'=>$v['openid']));
                            if(!empty($res_integral)){
                                $userintegral = $res_integral['integral']+$now_integral;
                                $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                            }else{
                                $m_userintegral->add(array('openid'=>$v['openid'],'integral'=>$now_integral));
                            }
                        }else{
                            $where = array('id'=>$res_merchant['merchant_id']);
                            $m_merchant->where($where)->setInc('integral',$now_integral);
                        }
                    }
                }

                $res_recordinfo = $m_integralrecord->getAllData('*',array('jdorder_id'=>$idcode,'type'=>19,'status'=>2),'id desc');
                if(!empty($res_recordinfo)){
                    $where = array('hotel_id'=>$res_recordinfo[0]['hotel_id'],'status'=>1);
                    $field_merchant = 'id as merchant_id,is_integral,is_shareprofit,shareprofit_config';
                    $res_merchant = $m_merchant->getRow($field_merchant,$where,'id desc');
                    $is_integral = $res_merchant['is_integral'];
                    foreach ($res_recordinfo as $v){
                        $m_integralrecord->updateData(array('id'=>$v['id']),array('status'=>1,'integral_time'=>date('Y-m-d H:i:s')));

                        $now_integral = $v['integral'];
                        if($is_integral==1){
                            $res_integral = $m_userintegral->getInfo(array('openid'=>$v['openid']));
                            if(!empty($res_integral)){
                                $userintegral = $res_integral['integral']+$now_integral;
                                $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                            }else{
                                $m_userintegral->add(array('openid'=>$v['openid'],'integral'=>$now_integral));
                            }
                        }else{
                            $where = array('id'=>$res_merchant['merchant_id']);
                            $m_merchant->where($where)->setInc('integral',$now_integral);
                        }
                    }
                }
                $m_taskuser = new \Admin\Model\TaskUserModel();
                $m_taskuser->finishTastewine($idcode);
            }
            $this->output('操作完成', 'stock/writeofflist');
        }else{
            $this->assign('vinfo',$res_info);
            $this->display();
        }
    }

    public function reportedlosslist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $status = I('status',0,'intval');
        $start_time = I('start_time','');
        $end_time = I('end_time','');


        $all_status = C('STOCK_WRITEOFF_STATUS');
        $where = array('a.type'=>6);
        if($status){
            $where['a.status'] = $status;
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

        $start = ($pageNum-1)*$size;
        $fields = 'a.id,a.type,a.idcode,a.reason,a.status,goods.name,goods.specification_id,a.unit_id,stock.department_id,a.type,stock.serial_number,a.add_time';
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_list = $m_stock_record->getChangeList($fields,$where, 'a.id desc', '',$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $v['department']=$departments[$v['department_id']]['name'];
                $v['specification']=$specifications[$v['specification_id']]['name'];
                $v['unit']=$units[$v['unit_id']]['name'];
                $v['status_str'] = $all_status[$v['status']];
                $data_list[] = $v;
            }
        }

        $this->assign('start_time', $start_time);
        $this->assign('end_time', $end_time);
        $this->assign('status', $status);
        $this->assign('all_status',$all_status);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function auditreportedloss(){
        $id = I('id',0,'intval');
        if(IS_POST){
            $status = I('post.status',0,'intval');
            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            $condition = array('id'=>$id);
            $data = array('audit_user_id'=>$sysuser_id,'status'=>$status,'update_time'=>date('Y-m-d H:i:s'));
            $m_stock_record = new \Admin\Model\StockRecordModel();
            $m_stock_record->updateData($condition, $data);
            $this->output('操作完成', 'stock/writeofflist');
        }else{
            $condition = array('id'=>$id);
            $m_stock_record = new \Admin\Model\StockRecordModel();
            $res_info = $m_stock_record->getInfo($condition);
            $this->assign('vinfo',$res_info);
            $this->display();
        }
    }

    public function idcodesearch(){
        $idcode = I('idcode','','trim');

        $data_list = array();
        if(!empty($idcode)){
            $qrcontent = decrypt_data($idcode);
            $qr_id = intval($qrcontent);
            $m_qrcode_content = new \Admin\Model\QrcodeContentModel();
            $res_qrcontent = $m_qrcode_content->getInfo(array('id'=>$qr_id));
            $m_stock_record = new \Admin\Model\StockRecordModel();
            if(empty($res_qrcontent)){
                $res_vintner_code = $m_stock_record->getAll('idcode',array('vintner_code'=>$idcode),0,1,'id asc');
                if(!empty($res_vintner_code[0]['idcode'])){
                    $idcode = $res_vintner_code[0]['idcode'];
                    $qrcontent = decrypt_data($idcode);
                    $qr_id = intval($qrcontent);
                    $res_qrcontent = $m_qrcode_content->getInfo(array('id'=>$qr_id));
                }
            }
            if(!empty($res_qrcontent)){
                $all_type = C('STOCK_RECORD_TYPE');
                $wo_status = C('STOCK_WRITEOFF_STATUS');

                $fileds = 'a.id,a.type,a.idcode,goods.name as goods_name,unit.name as unit_name,a.wo_status,a.dstatus,a.add_time';
                if($res_qrcontent['type']==1){
                    $parent_id = $qr_id;
                    $res_list = $m_stock_record->getStockRecordList($fileds,array('a.idcode'=>$idcode),'a.id desc','0,1','');
                    if(!empty($res_list)){
                        $type_str = $all_type[$res_list[0]['type']];
                        if($res_list[0]['type']==7){
                            $type_str.="（{$wo_status[$res_list[0]['wo_status']]}）";
                        }
                        $res_list[0]['type_str']= $type_str;
                        if($res_list[0]['dstatus']==2){
                            $dstatus_str = '删除';
                        }else{
                            $dstatus_str = '正常';
                        }
                        $res_list[0]['dstatus_str']= $dstatus_str;
                        $data_list = $res_list;
                    }
                }else{
                    $parent_id = $res_qrcontent['parent_id'];
                    $parent_idcode = encrypt_data($parent_id);
                    $res_list = $m_stock_record->getStockRecordList($fileds,array('a.idcode'=>$parent_idcode),'a.id desc','0,1','');
                    if(!empty($res_list)){
                        $type_str = $all_type[$res_list[0]['type']];
                        if($res_list[0]['type']==7){
                            $type_str.="（{$wo_status[$res_list[0]['wo_status']]}）";
                        }
                        $res_list[0]['type_str']= $type_str;
                        if($res_list[0]['dstatus']==2){
                            $dstatus_str = '删除';
                        }else{
                            $dstatus_str = '正常';
                        }
                        $res_list[0]['idcode'] = $res_list[0]['idcode'].'(箱码)';
                        $res_list[0]['dstatus_str']= $dstatus_str;
                        $data_list = $res_list;
                    }
                }
                $res_allqrcode = $m_qrcode_content->getDataList('id',array('parent_id'=>$parent_id),'id asc');
                foreach ($res_allqrcode as $v){
                    $qrcontent = encrypt_data($v['id']);
                    $res_record = $m_stock_record->getStockRecordList($fileds,array('a.idcode'=>$qrcontent),'a.id desc','0,1','');
                    if(!empty($res_record)){
                        $info = $res_record[0];
                        $type_str = $all_type[$info['type']];
                        if($info['type']==7){
                            $type_str.="（{$wo_status[$info['wo_status']]}）";
                        }
                        if($info['dstatus']==2){
                            $dstatus_str = '删除';
                        }else{
                            $dstatus_str = '正常';
                        }
                        $info['dstatus_str']= $dstatus_str;
                        $info['type_str']= $type_str;
                        $data_list[] = $info;
                    }else{
                        $data_list[] = array('idcode'=>$qrcontent);
                    }
                }
            }
        }
        $this->assign('idcode',$idcode);
        $this->assign('datalist',$data_list);
        $this->display();
    }

    public function idcodehistory(){
        $idcode = I('idcode','','trim');

        $all_type = C('STOCK_RECORD_TYPE');
        $wo_status = C('STOCK_WRITEOFF_STATUS');
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $fileds = 'a.id,a.type,a.idcode,goods.name as goods_name,stock.hotel_id,stock.serial_number,unit.name as unit_name,a.wo_status,a.dstatus,a.add_time';
        $res_record = $m_stock_record->getStockRecordList($fileds,array('a.idcode'=>$idcode),'a.id desc','','');
        $data_list = array();
        $m_hotel = new \Admin\Model\HotelModel();
        foreach ($res_record as $v){
            $info = $v;
            $type_str = $all_type[$info['type']];
            if($info['type']==7){
                $type_str.="（{$wo_status[$info['wo_status']]}）";
            }
            if($info['dstatus']==2){
                $dstatus_str = '删除';
            }else{
                $dstatus_str = '正常';
            }
            $hotel_name = '';
            if($info['hotel_id']>0){
                $res_hotel = $m_hotel->getInfo(array('id'=>$info['hotel_id']));
                $hotel_name = $res_hotel['name'];
            }
            $info['hotel_name']= $hotel_name;
            $info['dstatus_str']= $dstatus_str;
            $info['type_str']= $type_str;
            $data_list[] = $info;
        }
        $this->assign('idcode',$idcode);
        $this->assign('datalist',$data_list);
        $this->display();
    }

    public function cleanwriteoff(){
        $stock_record_id = I('stock_record_id',0,'intval');

        $m_stock_record = new \Admin\Model\StockRecordModel();
        $m_integralrecord = new \Admin\Model\UserIntegralrecordModel();
        $m_userintegral = new \Admin\Model\UserIntegralModel();

        $vinfo = $m_stock_record->getInfo(array('id'=>$stock_record_id));
        $idcode = $vinfo['idcode'];
        $openid = $vinfo['op_openid'];
        $integral_where = array('jdorder_id'=>$stock_record_id,'type'=>array('in','17,25'));
        if(IS_POST){
            $is_delintegral = I('post.is_delintegral',0,'intval');
            $m_sale = new \Admin\Model\SaleModel();
            $res_salerecord = $m_sale->getInfo(array('stock_record_id'=>$stock_record_id));
            if(in_array($res_salerecord['ptype'],array(1,2))){
                $this->output("当前核销记录已收款,不能删除", 'stock/writeofflist');
            }

            $message = '';
            if($is_delintegral==1){
                $ifields = 'id,integral,status,openid,source';
                $res_integral = $m_integralrecord->getDataList($ifields,$integral_where,'id asc');
                if(!empty($res_integral)){
                    $integral_record = array();
                    $clean_record_ids = array();
                    foreach ($res_integral as $v){
                        if($v['status']==1){
                            $integral_record[$v['openid']][] = array('id'=>$v['id'],'integral'=>$v['integral']);
                        }
                        $clean_record_ids[]=$v['id'];
                    }
                    $m_merchant = new \Admin\Model\MerchantModel();
                    $clean_user_integral = array();
                    foreach ($integral_record as $rk=>$rv){
                        $integral_openid = $rk;
                        $merchant_id=0;
                        $user_integral_id=0;
                        if(is_numeric($integral_openid)){
                            $res_merchant = $m_merchant->getInfo(array('hotel_id'=>$integral_openid,'status'=>1));
                            $now_integral = $res_merchant['integral'];
                            $merchant_id = $res_merchant['id'];
                        }else{
                            $res_userintegral = $m_userintegral->getInfo(array('openid'=>$integral_openid));
                            $now_integral = $res_userintegral['integral'];
                            $user_integral_id = $res_userintegral['id'];
                        }
                        $record_integral = 0;
                        foreach ($rv as $rrv){
                            $record_integral+=$rrv['integral'];
                        }
                        if($record_integral>$now_integral){
                            $this->output("用户:$integral_openid,积分不足。$now_integral<$record_integral", 'stock/writeofflist');
                        }
                        $clean_user_integral[]=array('now_integral'=>$now_integral,'record_integral'=>$record_integral,
                            'merchant_id'=>$merchant_id,'user_integral_id'=>$user_integral_id);
                    }
                    foreach ($clean_user_integral as $uv){
                        $last_integral = $uv['now_integral']-$uv['record_integral'];
                        $updata = array('integral'=>$last_integral,'update_time'=>date('Y-m-d H:i:s'));
                        if($uv['user_integral_id']>0){
                            $m_userintegral->updateData(array('id'=>$uv['user_integral_id']),$updata);
                            $log_content = "[idcode]{$idcode}[table]savor_smallapp_user_integral[content]id:{$uv['user_integral_id']},record_integral:{$uv['record_integral']},now_integral:{$uv['now_integral']},last_integral:$last_integral";
                            $this->record_log($log_content);
                        }
                        if($uv['merchant_id']>0){
                            $m_merchant->updateData(array('id'=>$uv['merchant_id']),$updata);
                            $log_content = "[idcode]{$idcode}[table]savor_integral_merchant[content]id:{$uv['merchant_id']},record_integral:{$uv['record_integral']},now_integral:{$uv['now_integral']},last_integral:$last_integral";
                            $this->record_log($log_content);
                        }
                    }

                    $res_irecord = $m_integralrecord->getDataList('*',array('id'=>array('in',$clean_record_ids)),'id asc');
                    $log_content = "[idcode]{$idcode}[table]savor_smallapp_user_integralrecord[content]".json_encode($res_irecord);
                    $this->record_log($log_content);
                    $m_integralrecord->delData(array('id'=>array('in',$clean_record_ids)));
                    $message.='积分已清理';
                }
            }

            $res_record = $m_stock_record->getInfo(array('id'=>$stock_record_id));
            $log_content = "[idcode]{$idcode}[table]savor_finance_stock_record[content]".json_encode($res_record);
            $this->record_log($log_content);
            $m_stock_record->delData(array('id'=>$stock_record_id));
            $message.=',核销记录已清理';

            $log_content = "[idcode]{$idcode}[table]savor_finance_sale[content]".json_encode($res_salerecord);
            $this->record_log($log_content);
            $m_sale->delData(array('stock_record_id'=>$stock_record_id));
            $message.=',销售出库单已清理';

            $m_u8record = new \Admin\Model\Pushu8RecordModel();
            $res_record = $m_u8record->getDataList('*',array('stock_record_id'=>$stock_record_id),'id asc');
            $log_content = "[idcode]{$idcode}[table]savor_finance_pushu8_record[content]".json_encode($res_record);
            $this->record_log($log_content);
            $m_u8record->delData(array('stock_record_id'=>$stock_record_id));
            $message.=',U8推送记录已清理';

            $this->output($message, 'stock/writeofflist');

        }else{
            $res_userintegral = $m_userintegral->getInfo(array('openid'=>$openid));
            $user_integral = $res_userintegral['integral'];

            $ifields = 'sum(integral) as total_integral';
            $integral_where['status'] = 1;
            $res_integral = $m_integralrecord->getDataList($ifields,$integral_where,'id desc');
            $integral = intval($res_integral[0]['total_integral']);

            $integral_where['status'] = 2;
            $res_integral = $m_integralrecord->getDataList($ifields,$integral_where,'id desc');
            $freeze_integral = intval($res_integral[0]['total_integral']);

            $this->assign('vinfo',$vinfo);
            $this->assign('user_integral',$user_integral);
            $this->assign('integral',$integral);
            $this->assign('freeze_integral',$freeze_integral);
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

    private function record_log($log_content){
        $log_file_name = C('REPORT_LOG_PATH').'cleanwriteoff_'.date("Ymd").".log";
        $now_time = date("Y-m-d H:i:s");
        $log_content = "[time]$now_time{$log_content} \n";
        @file_put_contents($log_file_name, $log_content, FILE_APPEND);
        return true;
    }


}