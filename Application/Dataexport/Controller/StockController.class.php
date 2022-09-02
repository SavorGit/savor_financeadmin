<?php
namespace Dataexport\Controller;

class StockController extends BaseController {
    
    public function writeofflist() {
        $wo_status = I('wo_status',0,'intval');
        $area_id = I('area_id',0,'intval');
        $hotel_name = I('hotel_name','','trim');
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        $where = array('a.type'=>7);
        if($wo_status){
            $where['a.wo_status'] = $wo_status;
        }
        if($area_id){
            $where['hotel.area_id'] = $area_id;
        }
        if(!empty($hotel_name)){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }
        if(!empty($start_time) && !empty($end_time)){
            $now_start_time = date('Y-m-d 00:00:00',strtotime($start_time));
            $now_end_time = date('Y-m-d 23:59:59',strtotime($end_time));
            $where['a.add_time'] = array(array('egt',$now_start_time),array('elt',$now_end_time));
        }
        $fields = 'a.id,a.idcode,a.goods_id,a.op_openid,a.wo_status,a.wo_reason_type,a.add_time,goods.name,goods.specification_id,
        unit.name as unit_name,hotel.name as hotel_name,hotel.id as hotel_id';
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_list = $m_stock_record->getRecordList($fields,$where, 'a.id desc', 0,0);
        $data_list = array();
        if(!empty($res_list)){
            $all_wo_status = C('STOCK_WRITEOFF_STATUS');
            $all_reason = C('STOCK_USE_TYPE');
            $m_user = new \Admin\Model\SmallappUserModel();
            foreach ($res_list as $v){
                $v['wo_reason_type_str'] = $all_reason[$v['wo_reason_type']];
                $v['wo_status_str'] = $all_wo_status[$v['wo_status']];
                $res_user = $m_user->getInfo(array('openid'=>$v['op_openid']));
                $v['username'] = $res_user['nickname'];
                $v['usermobile'] = $res_user['mobile'];
                $data_list[] = $v;
            }
        }

        $cell = array(
            array('id','ID'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('idcode','唯一识别码'),
            array('name','商品名称'),
            array('goods_id','商品编号'),
            array('unit_name','单位'),
            array('wo_reason_type_str','核销原因'),
            array('wo_status_str','状态'),
            array('username','核销人'),
            array('usermobile','核销人手机号码'),
            array('add_time','核销时间'),
        );
        $filename = '核销管理';
        $this->exportToExcel($cell,$data_list,$filename,1);
    }

    public function allidcodeinfo(){
        $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }
        $io_types = C('STOCK_OUT_TYPES');
        $all_wo_status = C('STOCK_WRITEOFF_STATUS');
        $all_reason = C('STOCK_USE_TYPE');

        $fileds = 'a.id,a.idcode,a.goods_id,a.op_openid,a.amount,a.total_amount,a.add_time,goods.name as goods_name,goods.barcode,unit.name as unit_name,
        hotel.id as hotel_id,hotel.name as hotel_name,stock.area_id';
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $where = array('a.type'=>1,'a.dstatus'=>1);
        $res_record = $m_stock_record->getRecordList($fileds,$where, 'a.id desc', 0,0);
        $data_list = array();
        $m_user = new \Admin\Model\SmallappUserModel();
        $m_qrcode_content = new \Admin\Model\QrcodeContentModel();
        $m_hotel = new \Admin\Model\HotelModel();
        $unpack_idcodes = array();
        $ic = 0;
        foreach ($res_record as $v){
            $area_name = $area_arr[$v['area_id']]['region_name'];
            $hotel_id = intval($v['hotel_id']);
            $hotel_name = $v['hotel_name'];
            $idcode = $v['idcode'];
            $barcode = $v['barcode'];
            $goods_name = $v['goods_name'];
            $in_time = $v['add_time'];
            $in_num = $v['total_amount'];
            $res_user = $m_user->getInfo(array('openid'=>$v['op_openid']));
            $in_username = $res_user['nickname'];

            if($in_num>$v['amount']){//整箱
                $unpack_where = array('a.idcode'=>$idcode,'a.type'=>3,'a.dstatus'=>1);
                $res_unpackrecord = $m_stock_record->getAllStock('a.id',$unpack_where,'a.id desc');
                if(empty($res_unpackrecord)){
                    $all_idcodes = array(array('idcode'=>$idcode,'type'=>1));
                }else{
                    $qrcontent = decrypt_data($idcode);
                    $qr_id = intval($qrcontent);
                    $res_allqrcode = $m_qrcode_content->getDataList('id',array('parent_id'=>$qr_id),'id asc');
                    $all_idcodes = array();
                    foreach ($res_allqrcode as $qv){
                        $qrcontent = encrypt_data($qv['id']);
                        $all_idcodes[]=array('idcode'=>$qrcontent,'type'=>2);
                    }
                    $in_num = 1;
                }
            }else{
                $all_idcodes = array(array('idcode'=>$idcode,'type'=>1));
            }
            foreach ($all_idcodes as $iv){
                $idcode = $iv['idcode'];
                if(in_array($idcode,$unpack_idcodes)){
                    continue;
                }
                if($iv['type']==2){
                    $in_where = array('idcode'=>$idcode,'type'=>1,'dstatus'=>1);
                    $res_inrecord = $m_stock_record->getAll('id',$in_where,0,1,'id asc');
                    if(!empty($res_inrecord)){
                        continue;
                    }
                    $unpack_idcodes[]=$idcode;
                }

                $out_fileds = 'a.op_openid,a.total_amount,a.add_time,stock.io_type,stock.hotel_id';
                $out_where = array('a.idcode'=>$idcode,'a.type'=>2,'a.dstatus'=>1);
                $res_outrecord = $m_stock_record->getAllStock($out_fileds,$out_where,'a.id desc');
                $out_type_str = $out_time = $out_num = $out_username = '';
                if(!empty($res_outrecord)){
                    $hotel_id = intval($res_outrecord[0]['hotel_id']);
                    if($hotel_id>0){
                        $res_hotel = $m_hotel->getInfo(array('id'=>$hotel_id));
                        $hotel_name = $res_hotel['name'];
                    }

                    $out_type_str = $io_types[$res_outrecord[0]['io_type']];
                    $out_time = $res_outrecord[0]['add_time'];
                    $out_num = abs($res_outrecord[0]['total_amount']);
                    $res_user = $m_user->getInfo(array('openid'=>$res_outrecord[0]['op_openid']));
                    $out_username = $res_user['nickname'];
                }

                $wo_time = $wo_status_str = $wo_reason_type_str = $wo_username = $wo_mobile = '';
                $wo_fields = 'a.op_openid,a.wo_status,a.wo_reason_type,a.add_time';
                $wo_where = array('a.idcode'=>$idcode,'a.type'=>7,'a.dstatus'=>1);
                $res_worecord = $m_stock_record->getAllStock($wo_fields,$wo_where,'a.id desc');
                if(!empty($res_worecord)){
                    $wo_reason_type_str = $all_reason[$res_worecord[0]['wo_reason_type']];
                    $wo_status_str = $all_wo_status[$res_worecord[0]['wo_status']];
                    $wo_time = $res_worecord[0]['add_time'];
                    $res_user = $m_user->getInfo(array('openid'=>$res_worecord[0]['op_openid']));
                    $wo_username = $res_user['nickname'];
                    $wo_mobile = $res_user['mobile'];
                }
                if($hotel_id==0){
                    $hotel_id = '';
                    $hotel_name = '';
                }
                $info = array('area_name'=>$area_name,'hotel_id'=>$hotel_id,'hotel_name'=>$hotel_name,'idcode'=>$idcode,'barcode'=>$barcode,
                    'goods_name'=>$goods_name,'out_type_str'=>$out_type_str,'in_time'=>$in_time,'in_num'=>$in_num,'in_username'=>$in_username,
                    'out_time'=>$out_time,'out_num'=>$out_num,'out_username'=>$out_username,
                    'wo_reason_type_str'=>$wo_reason_type_str,'wo_status_str'=>$wo_status_str,'wo_username'=>$wo_username,
                    'wo_mobile'=>$wo_mobile,'wo_time'=>$wo_time
                );
                echo $ic++."\n\r";
                $data_list[]=$info;
            }
        }

        $cell = array(
            array('area_name','仓库名称'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('idcode','唯一识别码'),
            array('barcode','商品条形码'),
            array('goods_name','商品名称'),
            array('out_type_str','出库类型'),
            array('in_time','入库时间'),
            array('in_num','入库数量'),
            array('in_username','入库人'),
            array('out_time','出库时间'),
            array('out_num','出库数量'),
            array('out_username','出库人'),
            array('wo_reason_type_str','核销原因'),
            array('wo_status_str','核销状态'),
            array('wo_username','核销人'),
            array('wo_mobile','核销人手机号码'),
            array('wo_time','核销时间'),
        );
        $filename = '全部唯一码数据';
        $this->exportToExcel($cell,$data_list,$filename,2);
    }


}