<?php
namespace Datacenter\Controller;
use Admin\Controller\BaseController;
class HotelstockController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
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

        $start = ($pageNum-1)*$size;
        $fileds = 'a.goods_id,stock.area_id,goods.name,goods.barcode,cate.name as cate_name,spec.name as sepc_name,a.unit_id,unit.name as unit_name,hotel.id as hotel_id,hotel.name as hotel_name';
        $group = 'stock.hotel_id,a.goods_id';
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $res_list = $m_stock_detail->getHotelStockGoods($fileds,$where,$group,$start,$size);
        $data_list = array();
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
                $rwhere['a.type'] = array('in',array(2,4));
                $rgroup = 'a.type';
                $res_record = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','',$rgroup);
                foreach ($res_record as $rv){
                    switch ($rv['type']){
                        case 2:
                            $in_num = abs($rv['total_amount']);
                            $in_total_fee = $in_num*$settlement_price;
                            break;
                        case 4:
                            $out_num = abs($rv['total_amount']);
                            $out_total_fee = $out_num*$settlement_price;
                            break;
                    }
                }
                $surplus_num = $surplus_total_fee = 0;
                if($in_num-$out_num>0){
                    $surplus_num = $in_num-$out_num;
                    $surplus_total_fee = $surplus_num*$settlement_price;
                }

                $v['in_num'] = $in_num;
                $v['in_total_fee'] = $in_total_fee;
                $v['out_num'] = $out_num;
                $v['out_total_fee'] = $out_total_fee;
                $v['surplus_num'] = $surplus_num;
                $v['surplus_total_fee'] = $surplus_total_fee;

                $v['settlement_price'] = $settlement_price;
                $v['area_name'] = $area_arr[$v['area_id']]['region_name'];
                $data_list[] = $v;
            }
        }

        $this->assign('area',$area_arr);
        $this->assign('area_id',$area_id);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

}