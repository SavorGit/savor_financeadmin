<?php
namespace Dataexport\Controller;
class HotelstockController extends BaseController {

    public function datalist(){
        $area_id = I('area_id',0,'intval');
        $category_id = I('category_id',0,'intval');
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        $where = array();
        if($area_id){
            $where['area_id'] = $area_id;
        }
        if($category_id){
            $where['category_id'] = $category_id;
        }
        $where['static_date']= array(array('EGT',$start_time),array('ELT',$end_time));
        $m_hotelstock_archivedata = new \Admin\Model\HotelStockArchivedataModel();
        $datalist = $m_hotelstock_archivedata->getDataList('*',$where, 'id desc');
        $cell = array(
            array('goods_name','商品名称'),
            array('goods_id','商品编码'),
            array('category_name','商品类型'),
            array('settlement_price','商品单价'),
            array('hotel_id','仓库编码'),
            array('hotel_name','仓库名称'),
            array('begin_num','期初数量'),
            array('begin_total_fee','期初金额'),
            array('stock_num','库存数量'),
            array('stock_total_fee','库存金额'),
            array('in_num','入库数量'),
            array('in_total_fee','入库金额'),
            array('out_num','出库数量'),
            array('out_total_fee','出库金额'),
            array('static_date','统计日期'),

        );
        $filename = '酒楼库存管理';
        $this->exportToExcel($cell,$datalist,$filename,1);

    }

    public function checklist(){
        $area_id = I('area_id',0,'intval');
        $stat_date = I('stat_date','');

        $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }

        $where = array('stock.hotel_id'=>array('gt',0),'stock.type'=>20);
        $where['hotel.id'] = array('not in',C('TEST_HOTEL'));
        if($area_id){
            $where['stock.area_id'] = $area_id;
        }
        if(empty($stat_date)){
            $now_stat_date = date('Y-m');
        }else{
            $now_stat_date = date('Y-m',strtotime($stat_date));
        }
        $fileds = 'a.goods_id,stock.area_id,goods.name,goods.barcode,cate.name as cate_name,spec.name as sepc_name,a.unit_id,
        unit.name as unit_name,hotel.id as hotel_id,hotel.name as hotel_name,sur.remark as residenter_name';
        $group = 'stock.hotel_id,a.goods_id';
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $res_list = $m_stock_detail->getHotelStockGoods($fileds,$where,$group,0,0);
        $data_list = array();
        if(!empty($res_list)){
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
        $cell = array(
            array('area_name','城市'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('residenter_name','驻店人'),
            array('goods_id','商品ID'),
            array('name','商品名称'),
            array('sepc_name','商品规格'),
            array('stock_num','当前库存(瓶)'),
            array('check_stock_num','盘点时库存(瓶)'),
            array('check_had_num','盘点量'),
            array('diff_check_num','差异值'),
            array('check_uname','盘点人'),
            array('check_time','盘点日期'),
        );
        $filename = '酒楼盘点记录';
        $this->exportToExcel($cell,$data_list,$filename,1);

    }

}