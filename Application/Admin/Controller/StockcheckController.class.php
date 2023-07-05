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
        $fileds = 'a.goods_id,stock.area_id,goods.name,goods.barcode,cate.name as cate_name,spec.name as sepc_name,a.unit_id,unit.name as unit_name,hotel.id as hotel_id,hotel.name as hotel_name';
        $group = 'stock.hotel_id,a.goods_id';
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $res_list = $m_stock_detail->getHotelStockGoods($fileds,$where,$group,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_stock_record = new \Admin\Model\StockRecordModel();
            $m_sale_record = new \Admin\Model\SaleRecordModel();
            $m_check_record = new \Admin\Model\StockCheckRecordModel();
            foreach ($res_list['list'] as $v){
                $out_num = $unpack_num = $wo_num = $report_num = 0;
                $goods_id = $v['goods_id'];
                $rfileds = 'sum(a.total_amount) as total_amount,sum(a.total_fee) as total_fee,a.type';
                $rwhere = array('stock.hotel_id'=>$v['hotel_id'],'a.goods_id'=>$goods_id,'a.dstatus'=>1);
                $rwhere['a.type'] = 2;
                $res_record = $m_stock_record->getStockRecordList($rfileds,$rwhere,'','','');
                if(!empty($res_record[0]['total_amount'])){
                    $out_num = abs($res_record[0]['total_amount']);
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
                $stock_num = $out_num+$wo_num+$report_num;

                $sale_fields = 'record.id,record.add_time,record.stock_check_status,staff.id as staff_id,staff.job,sysuser.remark as staff_name';
                $salewhere = array('record.signin_hotel_id'=>$v['hotel_id'],'record.type'=>2);
                $salewhere["date_format(record.add_time,'%Y-%m')"] = $now_stat_date;
                $res_sale = $m_sale_record->getRecordList($sale_fields,$salewhere,'record.id desc','0,1');
                $check_num=$diff_check_num=0;
                $check_uname=$check_time='';
                if(!empty($res_sale) && $res_sale[0]['stock_check_status']==2){
                    $check_uname = $res_sale[0]['staff_name'];
                    $check_time = $res_sale[0]['add_time'];
                    $res_check = $m_check_record->getAllData('count(id) as num,is_check',array('salerecord_id'=>$res_sale[0]['id'],'goods_id'=>$goods_id,'type'=>1),'','is_check');
                    if(!empty($res_check)){
                        foreach ($res_check as $cv){
                            $check_num+=$cv['num'];
                            if($cv['is_check']==0){
                                $diff_check_num=$cv['num'];
                            }
                        }
                    }
                }
                $v['stock_num'] = $stock_num;
                $v['area_name'] = $area_arr[$v['area_id']]['region_name'];
                $v['check_num'] = $check_num;
                $v['check_had_num'] = $check_num-$diff_check_num;
                $v['diff_check_num'] = $diff_check_num;
                $v['check_uname'] = $check_uname;
                $v['check_time'] = $check_time;
                $data_list[] = $v;
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
        $all_check_status = array('21'=>'正常','22'=>'盘赢','23'=>'盘亏','24'=>'盘赢+盘亏');
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

}