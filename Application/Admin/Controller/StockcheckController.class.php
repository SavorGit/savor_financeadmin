<?php
namespace Admin\Controller;
class StockcheckController extends BaseController {

    public function checklist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $area_id = I('area_id',0,'intval');
        $stat_date = I('stat_date','');
        $hotel_name = I('hotel_name','','trim');

        $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }

        $where = array('stock.hotel_id'=>array('gt',0),'stock.type'=>20);
        $where['hotel.id'] = array('not in',C('TEST_HOTEL'));
        if(!empty($hotel_name)){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }
        if($area_id){
            $where['stock.area_id'] = $area_id;
        }
        if(empty($stat_date)){
            $now_stat_date = date('Y-m');
            $stat_date = date('Y-m-01');
        }else{
            $now_stat_date = date('Y-m',strtotime($stat_date));
        }
        $start = ($pageNum-1)*$size;
        $fileds = 'a.goods_id,stock.area_id,goods.name,goods.barcode,cate.name as cate_name,spec.name as sepc_name,a.unit_id,
        unit.name as unit_name,hotel.id as hotel_id,hotel.name as hotel_name,sur.remark as residenter_name';
        $group = 'stock.hotel_id,a.goods_id';
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $res_list = $m_stock_detail->getHotelStockGoods($fileds,$where,$group,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_sale_record = new \Admin\Model\SaleRecordModel();
            $m_check_record = new \Admin\Model\StockCheckRecordModel();
            $m_hotel_stock = new \Admin\Model\HotelStockModel();
            $hotel_ids = array();
            $hotel_salerecords = array();
            foreach ($res_list as $v){
                $hotel_id = $v['hotel_id'];

                $hotel_ids[]=$hotel_id;
                $salerecord_id = 0;
                $check_uname=$check_time='';
                if(isset($hotel_salerecords[$hotel_id])){
                    $salerecord_id = $hotel_salerecords[$hotel_id]['id'];
                    $check_uname = $hotel_salerecords[$hotel_id]['name'];
                    $check_time = $hotel_salerecords[$hotel_id]['time'];
                }else{
                    $sale_fields = 'record.id,record.add_time,record.stock_check_status,staff.id as staff_id,staff.job,sysuser.remark as staff_name';
                    $salewhere = array('record.signin_hotel_id'=>$v['hotel_id'],'record.type'=>2);
                    $salewhere["date_format(record.add_time,'%Y-%m')"] = $now_stat_date;
                    $res_salerecord = $m_sale_record->getRecordList($sale_fields,$salewhere,'record.id desc','0,1');
                    if(!empty($res_salerecord) && $res_salerecord[0]['stock_check_status']==2){
                        $salerecord_id = $res_salerecord[0]['id'];
                        $check_uname = $res_salerecord[0]['staff_name'];
                        $check_time = $res_salerecord[0]['add_time'];
                        $hotel_salerecords[$hotel_id] = array('id'=>$salerecord_id,'name'=>$check_uname,'time'=>$check_time);
                    }
                }

                $v['check_uname'] = $check_uname;
                $v['check_time'] = $check_time;
                $v['salerecord_id'] = $salerecord_id;
                $v['area_name'] = $area_arr[$v['area_id']]['region_name'];
                $data_list[] = $v;
            }
            $res_hotel_stock = $m_hotel_stock->getDataList('hotel_id,goods_id,num',array('hotel_id'=>array('in',$hotel_ids)),'id desc');
            $hotel_stocks = array();
            foreach ($res_hotel_stock as $v){
                $hotel_stocks[$v['hotel_id'].$v['goods_id']]=$v['num'];
            }
            $check_stocks = array();
            if(!empty($hotel_salerecords)){
                $salerecord_ids = array();
                foreach ($hotel_salerecords as $hsr){
                    $salerecord_ids[]=$hsr['id'];
                }
                $checkfields = 'salerecord_id,goods_id,count(id) as num,is_check';
                $res_check = $m_check_record->getAllData($checkfields,array('salerecord_id'=>array('in',$salerecord_ids),'type'=>1),'','salerecord_id,goods_id,is_check');
                if(!empty($res_check)){
                    foreach ($res_check as $v){
                        $check_stocks[$v['salerecord_id'].$v['goods_id']][]=array('num'=>$v['num'],'is_check'=>$v['is_check']);
                    }
                }
            }

            foreach ($data_list as $k=>$v){
                $stock_num = isset($hotel_stocks[$v['hotel_id'].$v['goods_id']])?$hotel_stocks[$v['hotel_id'].$v['goods_id']]:0;
                $check_stock_num=$diff_check_num=0;
                if(isset($check_stocks[$v['salerecord_id'].$v['goods_id']])){
                    foreach ($check_stocks[$v['salerecord_id'].$v['goods_id']] as $ctv){
                        $check_stock_num+=$ctv['num'];
                        if($ctv['is_check']==0){
                            $diff_check_num+=$ctv['num'];
                        }
                    }
                }
                $check_had_num = $check_stock_num-$diff_check_num;

                $data_list[$k]['stock_num'] = $stock_num;
                $data_list[$k]['check_stock_num'] = $check_stock_num;
                $data_list[$k]['check_had_num'] = $check_had_num;
                $data_list[$k]['diff_check_num'] = $diff_check_num;
            }
        }

        $this->assign('stat_date',$stat_date);
        $this->assign('area',$area_arr);
        $this->assign('area_id',$area_id);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function checkerrorlist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $stat_date = I('stat_date','');
        $hotel_name = I('hotel_name','','trim');

        $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }
        $all_check_status = C('STOCKCHECK_STATUS');
        if(empty($stat_date)){
            $now_stat_date = date('Y-m');
            $stat_date = date('Y-m-01');
        }else{
            $now_stat_date = date('Y-m',strtotime($stat_date));
        }
        $start = ($pageNum-1)*$size;
        $m_sale_record = new \Admin\Model\SaleRecordModel();
        $fields = 'record.id,record.add_time,record.stock_check_status,hotel.id as hotel_id,hotel.name as hotel_name,hotel.area_id,
        record.stock_check_num,record.stock_check_hadnum,record.stock_check_success_status,record.is_handle_stock_check,
        staff.id as staff_id,staff.job,sysuser.remark as staff_name';
        $where = array('record.type'=>2,'record.stock_check_status'=>2,'record.stock_check_success_status'=>array('in','22,23,24'));
        $where["date_format(record.add_time,'%Y-%m')"] = $now_stat_date;
        if(!empty($hotel_name)){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }
        $res_list = $m_sale_record->getCheckDataList($fields,$where,'record.id desc',$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $v['check_status_str'] = $all_check_status[$v['stock_check_success_status']];
                $v['area_name'] = $area_arr[$v['area_id']]['region_name'];
                if($v['is_handle_stock_check']==1){
                    $handle_str = '是';
                }else{
                    $handle_str = '否';
                }
                $v['handle_str'] = $handle_str;
                $data_list[]=$v;
            }
        }

        $this->assign('stat_date',$stat_date);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function handlecheckerror(){
        $id = I('id',0,'intval');
        $m_sale_record = new \Admin\Model\SaleRecordModel();
        $vinfo = $m_sale_record->getInfo(array('id'=>$id));
        if(IS_POST){
            $is_handle_stock_check = I('post.is_handle_stock_check',0,'intval');
            $userinfo = session('sysUserInfo');
            $handle_user_id = $userinfo['id'];
            $updata = array('is_handle_stock_check'=>$is_handle_stock_check,'handle_user_id'=>$handle_user_id,'handle_time'=>date('Y-m-d H:i:s'));
            $m_sale_record->updateData(array('id'=>$id),$updata);
            $this->output('操作成功!', 'hotelstock/checkerrorlist');
        }else{
            $hotel_name = I('hotel_name','');
            $this->assign('hotel_name',$hotel_name);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function checkcodelist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $salerecord_id = I('salerecord_id',0,'intval');
        $type = I('type',0,'intval');

        $m_check_record = new \Admin\Model\StockCheckRecordModel();
        $start = ($pageNum-1)*$size;
        $where = array('salerecord_id'=>$salerecord_id);
        $orderby = 'type desc,is_check desc';
        if($type){
            $where['type'] = $type;
        }
        $res_list = $m_check_record->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        $all_types = array('1'=>'盘点商品码','2'=>'未入系统商品码');
        $m_goods = new \Admin\Model\GoodsModel();
        $goods_info = array();
        foreach ($res_list['list'] as $v){
            $is_check_str = '';
            if($v['type']==1){
                if($v['is_check']){
                    $is_check_str = '是';
                }else{
                    $is_check_str = '否';
                }
            }
            if(isset($goods_info[$v['goods_id']])){
                $goods_name = $goods_info[$v['goods_id']]['name'];
            }else{
                $res_goods = $m_goods->getInfo(array('id'=>$v['goods_id']));
                $goods_info[$v['goods_id']] = $res_goods;
                $goods_name = $res_goods['name'];
            }
            $v['goods_name'] = $goods_name;
            $v['is_check_str'] = $is_check_str;
            $v['type_str'] = $all_types[$v['type']];
            $data_list[]=$v;
        }
        $this->assign('salerecord_id',$salerecord_id);
        $this->assign('all_types',$all_types);
        $this->assign('type',$type);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function taskerrorlist(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $hotel_name = I('hotel_name','','trim');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $check_type = I('check_type',2,'intval');//盘点原因 1正常2异常

        $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }
        if(empty($start_date) || empty($end_date)){
            $start_date = date('Y-m-d',strtotime("-1 month"));
            $end_date = date('Y-m-d');
        }
        $fields = 'a.id,a.task_id,a.integral,a.is_get_integral,a.add_time,a.stock_check_status,
        a.stock_check_num,a.stock_check_hadnum,a.stock_check_success_status,a.is_handle_stock_check,
        hotel.id as hotel_id,hotel.name as hotel_name,hotel.area_id,sysuser.remark as maintainer_name,
        user.nickName as check_username,user.mobile';
        $where = array();
        if($check_type==1){
            $where = array('a.stock_check_success_status'=>21);
        }elseif($check_type==2){
            $where = array('a.stock_check_success_status'=>array('in','22,23,24'));
        }
        $where['a.add_time']= array(array('EGT',$start_date.' 00:00:00'),array('ELT',$end_date.' 23:59:59'));
        if(!empty($hotel_name)){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }
        $m_stockcheck = new \Admin\Model\SmallappStockCheckModel();
        $start = ($pageNum-1)*$size;
        $res_list = $m_stockcheck->getCheckDataList($fields,$where,'a.id desc',$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $all_check_status = C('STOCKCHECK_STATUS');
            foreach ($res_list['list'] as $v){
                $v['check_username'] = $v['check_username']."({$v['mobile']})";
                $v['check_status_str'] = $all_check_status[$v['stock_check_success_status']];
                $v['area_name'] = $area_arr[$v['area_id']]['region_name'];
                if($v['is_handle_stock_check']==1){
                    $handle_str = '是';
                }else{
                    $handle_str = '否';
                }
                $v['handle_str'] = $handle_str;
                $data_list[]=$v;
            }
        }
        $this->assign('check_type',$check_type);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function handletaskerror(){
        $id = I('id',0,'intval');
        $m_stockcheck = new \Admin\Model\SmallappStockCheckModel();
        $vinfo = $m_stockcheck->getInfo(array('id'=>$id));
        if(IS_POST){
            $is_handle_stock_check = I('post.is_handle_stock_check',0,'intval');
            $userinfo = session('sysUserInfo');
            $handle_user_id = $userinfo['id'];
            $updata = array('is_handle_stock_check'=>$is_handle_stock_check,'handle_user_id'=>$handle_user_id,'handle_time'=>date('Y-m-d H:i:s'));

            $res_check = $m_stockcheck->getInfo(array('hotel_id'=>$vinfo['hotel_id'],'task_id'=>$vinfo['task_id'],'is_get_integral'=>1));
            if(empty($res_check)){
                $m_staff = new \Admin\Model\StaffModel();
                $res_staff = $m_staff->getInfo(array('id'=>$vinfo['staff_id']));
                $m_taskuser = new \Admin\Model\TaskUserModel();
                $res_integral = $m_taskuser->finishStockCheckTask($res_staff['openid'],$id,$vinfo['task_user_id']);
                if($res_integral>0){
                    $updata['integral'] = $res_integral;
                    $updata['is_get_integral'] = 1;
                    $updata['get_time'] = date('Y-m-d H:i:s');

                    $m_taskuser->updateData(array('id'=>$vinfo['task_user_id']),array('status'=>3));
                }
            }
            $m_stockcheck->updateData(array('id'=>$id),$updata);
            $this->output('操作成功!', 'stockcheck/taskerrorlist');
        }else{
            $hotel_name = I('hotel_name','');
            $this->assign('hotel_name',$hotel_name);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function taskcodelist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $salerecord_id = I('stockcheck_id',0,'intval');
        $type = I('type',0,'intval');

        $m_check_record = new \Admin\Model\SmallappStockCheckRecordModel();
        $start = ($pageNum-1)*$size;
        $where = array('stockcheck_id'=>$salerecord_id);
        $orderby = 'type desc,is_check desc';
        if($type){
            $where['type'] = $type;
        }
        $res_list = $m_check_record->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        $all_types = array('1'=>'盘点商品码','2'=>'未入系统商品码');
        $m_goods = new \Admin\Model\GoodsModel();
        $goods_info = array();
        foreach ($res_list['list'] as $v){
            $is_check_str = '';
            if($v['type']==1){
                if($v['is_check']){
                    $is_check_str = '是';
                }else{
                    $is_check_str = '否';
                }
            }
            if(isset($goods_info[$v['goods_id']])){
                $goods_name = $goods_info[$v['goods_id']]['name'];
            }else{
                $res_goods = $m_goods->getInfo(array('id'=>$v['goods_id']));
                $goods_info[$v['goods_id']] = $res_goods;
                $goods_name = $res_goods['name'];
            }
            $v['goods_name'] = $goods_name;
            $v['is_check_str'] = $is_check_str;
            $v['type_str'] = $all_types[$v['type']];
            $data_list[]=$v;
        }
        $this->assign('stockcheck_id',$salerecord_id);
        $this->assign('all_types',$all_types);
        $this->assign('type',$type);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }
}