<?php
namespace Dataexport\Controller;
class CompanystockController extends BaseController {

    public function datalist(){
        $area_id = I('area_id',0,'intval');
        $category_id = I('category_id',0,'intval');

        $where = array('goods.brand_id'=>array('neq',11));
        if($category_id){
            $where['goods.category_id'] = $category_id;
        }
        $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            if($v['id']!=246){
                $area_arr[$v['id']]=$v;
            }
        }
        $fields = 'goods.id as goods_id,goods.barcode,goods.name,cate.name as category';
        $m_goods = new \Admin\Model\GoodsModel();
        $res_list = $m_goods->getList($fields,$where, 'goods.id desc', 0,1000);
        $datalist = array();
        if(!empty($res_list['list'])){
            $m_stock_record = new \Admin\Model\StockRecordModel();
            $m_avg_price = new \Admin\Model\GoodsAvgpriceModel();
            foreach ($res_list['list'] as $v){
                $goods_id = $v['goods_id'];
                $res_price = $m_avg_price->getAll('price',array('goods_id'=>$goods_id),0,1,'id desc');
                $avg_price = $res_price[0]['price'];

                $fields = 'sum(a.total_amount) as total_amount,a.type';
                $swhere = array('a.goods_id'=>$goods_id,'a.type'=>array('in',array(1,2)),'a.dstatus'=>1);
                if($area_id){
                    $all_areas = array(array('id'=>$area_id));
                }else{
                    $all_areas = $area_arr;
                }
                foreach ($all_areas as $av){
                    $in_num = $out_num = 0;
                    $in_total_fee = $out_total_fee = $price = 0;
                    $now_area_id = $av['id'];
                    $swhere['stock.area_id'] = $now_area_id;
                    $res_goods_record = $m_stock_record->getAllStock($fields,$swhere,'a.id desc','a.type');
                    foreach ($res_goods_record as $rv){
                        switch ($rv['type']){
                            case 1:
                                $in_num = abs($rv['total_amount']);
                                $in_total_fee = $in_num*$avg_price;
                                break;
                            case 2:
                                $out_num = abs($rv['total_amount']);
                                $out_total_fee = $out_num*$avg_price;
                                break;
                        }
                    }
                    $surplus_num = $surplus_total_fee = 0;
                    if($in_num-$out_num>0){
                        $surplus_num = $in_num-$out_num;
                        $surplus_total_fee = $surplus_num*$avg_price;
                    }
                    $v['avg_price'] = $avg_price;
                    $v['begin_num'] = 0;
                    $v['begin_total_fee'] = 0;
                    $v['in_num'] = $in_num;
                    $v['in_total_fee'] = $in_total_fee;
                    $v['out_num'] = $out_num;
                    $v['out_total_fee'] = $out_total_fee;
                    $v['surplus_num'] = $surplus_num;
                    $v['surplus_total_fee'] = $surplus_total_fee;
                    $v['area_id'] = $now_area_id;
                    $v['area_name'] = $area_arr[$now_area_id]['region_name'];
                    $datalist[] = $v;
                }
            }
        }
        $cell = array(
            array('name','商品名称'),
            array('goods_id','商品编码'),
            array('category','商品类型'),
            array('avg_price','商品单价'),
            array('area_id','仓库编码'),
            array('area_name','仓库名称'),
            array('begin_num','期初数量'),
            array('begin_total_fee','期初金额'),
            array('surplus_num','结余数量'),
            array('surplus_total_fee','结余金额'),
            array('in_num','入库数量'),
            array('in_total_fee','入库金额'),
            array('out_num','出库数量'),
            array('out_total_fee','出库金额'),

        );
        $filename = '公司库存管理';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }

}