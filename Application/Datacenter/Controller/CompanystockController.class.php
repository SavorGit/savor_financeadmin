<?php
namespace Datacenter\Controller;
use Admin\Controller\BaseController;
class CompanystockController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $area_id = I('area_id',0,'intval');
        $category_id = I('category_id',0,'intval');

        $where = array('goods.brand_id'=>array('neq',11));
        if($category_id){
            $where['goods.category_id'] = $category_id;
        }
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
        $start = ($pageNum-1)*$size;
        $fields = 'goods.id as goods_id,goods.barcode,goods.name,cate.name as category';
        $m_goods = new \Admin\Model\GoodsModel();
        $res_list = $m_goods->getList($fields,$where, 'goods.id desc', $start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_stock_record = new \Admin\Model\StockRecordModel();
            $m_avg_price = new \Admin\Model\GoodsAvgpriceModel();
            foreach ($res_list['list'] as $v){
                $in_num = $out_num = 0;
                $in_total_fee = $out_total_fee = $price = 0;

                $goods_id = $v['goods_id'];
                $res_price = $m_avg_price->getAll('price',array('goods_id'=>$goods_id),0,1,'id desc');
                $avg_price = $res_price[0]['price'];

                $fields = 'sum(a.total_amount) as total_amount,a.type';
                $swhere = array('a.goods_id'=>$goods_id,'a.type'=>array('in',array(1,2)),'a.dstatus'=>1);
                if($area_id){
                    $all_areas = array(array('id'=>$area_id));
                }else{
                    $all_areas = $res_area;
                }
                foreach ($all_areas as $av){
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
                    $v['in_num'] = $in_num;
                    $v['in_total_fee'] = $in_total_fee;
                    $v['out_num'] = $out_num;
                    $v['out_total_fee'] = $out_total_fee;
                    $v['surplus_num'] = $surplus_num;
                    $v['surplus_total_fee'] = $surplus_total_fee;
                    $v['area_id'] = $now_area_id;
                    $v['area_name'] = $area_arr[$now_area_id]['region_name'];
                    $data_list[] = $v;
                }
            }
        }

        $this->assign('area_id', $area_id);
        $this->assign('category_id', $category_id);
        $this->assign('area', $area_arr);
        $this->assign('category', $category_arr);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

}