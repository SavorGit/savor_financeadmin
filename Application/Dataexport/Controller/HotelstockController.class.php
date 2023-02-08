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

}