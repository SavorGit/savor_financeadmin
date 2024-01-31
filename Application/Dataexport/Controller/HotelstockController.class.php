<?php
namespace Dataexport\Controller;
class HotelstockController extends BaseController {

    public function datalist(){
        $area_id = I('area_id',0,'intval');

        $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }

        $where = array('stock.type'=>20);
        $where['stock.hotel_id'] = array(array('gt',0),array('not in',C('TEST_HOTEL')));
        if($area_id){
            $where['stock.area_id'] = $area_id;
        }
        $pageNum = 1;
        $size = 10000;
        $start = ($pageNum-1)*$size;
        $fileds = 'a.goods_id,stock.area_id,goods.name,goods.barcode,cate.name as cate_name,spec.name as sepc_name,a.unit_id,unit.name as unit_name,hotel.id as hotel_id,hotel.name as hotel_name';
        $group = 'stock.hotel_id,a.goods_id';
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $res_list = $m_stock_detail->getHotelStockGoods($fileds,$where,$group,$start,$size);
        $datalist = array();
        if(!empty($res_list['list'])){
            $m_stock_record = new \Admin\Model\StockRecordModel();
            $m_price_template_hotel = new \Admin\Model\PriceTemplateHotelModel();
            foreach ($res_list['list'] as $v){
                $in_num = $out_num = 0;
                $in_total_fee = $out_total_fee = $price = 0;
                $goods_id = $v['goods_id'];
                $settlement_price = $m_price_template_hotel->getHotelGoodsPrice($v['hotel_id'],$goods_id,1);

                $rfileds = 'sum(a.total_amount) as total_amount,sum(a.total_fee) as total_fee,a.type';
                $rwhere = array('stock.hotel_id'=>$v['hotel_id'],'a.goods_id'=>$goods_id,'a.dstatus'=>1);
                $rwhere['a.type'] = 2;
                $res_record = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','','');
                if(!empty($res_record[0]['total_amount'])){
                    $in_num = abs($res_record[0]['total_amount']);
                    $in_total_fee = $in_num*$settlement_price;
                }

                $rwhere['a.type']=7;
                $rwhere['a.wo_status']= array('in',array(1,2,4));
                $res_worecord = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','','');
                $wo_num = 0;
                if(!empty($res_worecord[0]['total_amount'])){
                    $wo_num = abs($res_worecord[0]['total_amount']);
                }
                $rwhere['a.type']=6;
                unset($rwhere['a.wo_status']);
                $rwhere['a.status']= array('in',array(1,2));
                $res_worecord = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','','');
                $report_num = 0;
                if(!empty($res_worecord[0]['total_amount'])){
                    $report_num = abs($res_worecord[0]['total_amount']);
                }
                $out_num = $wo_num+$report_num;
                $out_total_fee = $out_num*$settlement_price;

                $surplus_num = $surplus_total_fee = 0;
                if($in_num-$out_num>0){
                    $surplus_num = $in_num-$out_num;
                    $surplus_total_fee = $surplus_num*$settlement_price;
                }

                $v['begin_num'] = 0;
                $v['begin_total_fee'] = 0;
                $v['in_num'] = $in_num;
                $v['in_total_fee'] = $in_total_fee;
                $v['out_num'] = $out_num;
                $v['out_total_fee'] = $out_total_fee;
                $v['surplus_num'] = $surplus_num;
                $v['surplus_total_fee'] = $surplus_total_fee;

                $v['settlement_price'] = $settlement_price;
                $v['area_name'] = $area_arr[$v['area_id']]['region_name'];
                $datalist[] = $v;
            }
        }
        $cell = array(
            array('name','商品名称'),
            array('goods_id','商品编码'),
            array('cate_name','商品类型'),
            array('settlement_price','商品单价'),
            array('hotel_id','仓库编码'),
            array('hotel_name','仓库名称'),
            array('begin_num','期初数量'),
            array('begin_total_fee','期初金额'),
            array('surplus_num','结余数量'),
            array('surplus_total_fee','结余金额'),
            array('in_num','入库数量'),
            array('in_total_fee','入库金额'),
            array('out_num','出库数量'),
            array('out_total_fee','出库金额'),

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