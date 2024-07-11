<?php
namespace Dataexport\Controller;
class GoodsController extends BaseController {

    public function currentdata(){
        $awhere = "is_in_hotel=1 and id not in (246)";//深圳无售酒
        $sql_area = "select id as area_id,region_name as area_name from savor_area_info where {$awhere} order by id asc ";
        $res_area = M()->query($sql_area);

        $fields = 'goods.id as goods_id,goods.name as goods_name,brand.name as brand_name';
        $where = array('brand.id'=>2);
        $m_goods = new \Admin\Model\GoodsModel();
        $res_goods = $m_goods->alias('goods')
            ->join('savor_finance_brand brand on goods.brand_id=brand.id','left')
            ->field($fields)
            ->where($where)
            ->order('goods.id asc')
            ->select();
        $goods_list = array();
        foreach ($res_goods as $v){
            $goods_list[$v['goods_id']]=$v;
        }
        $goods_ids = array_keys($goods_list);
        $goods_ids_str = join(',',$goods_ids);

        $sql_zzc = "select area_id,goods_id,sum(num) as num from savor_finance_company_stock where goods_id in ({$goods_ids_str}) group by area_id,goods_id";
        $res_zzc = M()->query($sql_zzc);
        $zzc_data = array();
        foreach ($res_zzc as $v){
            $zzc_data[$v['goods_id']][$v['area_id']] = $v['num'];
        }
        $sql_qzc = "select area_id,goods_id,count(id) as hotel_num,sum(num) as num from savor_finance_hotel_stock where goods_id in ({$goods_ids_str}) group by area_id,goods_id";
        $res_qzc = M()->query($sql_qzc);
        $qzc_hotel_data=$qzc_stock_data=array();
        foreach ($res_qzc as $v){
            $qzc_hotel_data[$v['goods_id']][$v['area_id']] = $v['hotel_num'];
            $qzc_stock_data[$v['goods_id']][$v['area_id']] = $v['num'];
        }

        $datalist = array();
        foreach ($goods_list as $v){
            $zzc_stock_num=$qzc_stock_num=$qzc_hotel_num=0;
            if(isset($zzc_data[$v['goods_id']])){
                $zzc_stock_num = array_sum(array_values($zzc_data[$v['goods_id']]));
            }
            if(isset($qzc_stock_data[$v['goods_id']])){
                $qzc_stock_num = array_sum(array_values($qzc_stock_data[$v['goods_id']]));
            }
            if(isset($qzc_hotel_data[$v['goods_id']])){
                $qzc_hotel_num = array_sum(array_values($qzc_hotel_data[$v['goods_id']]));
            }
            $info = array('id'=>$v['goods_id'],'name'=>$v['goods_name'],'zzc_stock_num'=>$zzc_stock_num,'qzc_stock_num'=>$qzc_stock_num,
                'qzc_hotel_num'=>$qzc_hotel_num);

            foreach ($res_area as $av){
                $area_id = $av['area_id'];
                $info["zzc_stock_num_$area_id"] = isset($zzc_data[$v['goods_id']][$area_id])?$zzc_data[$v['goods_id']][$area_id]:0;
                $info["qzc_stock_num_$area_id"] = isset($qzc_stock_data[$v['goods_id']][$area_id])?$qzc_stock_data[$v['goods_id']][$area_id]:0;
                $info["qzc_hotel_num_$area_id"] = isset($qzc_hotel_data[$v['goods_id']][$area_id])?$qzc_hotel_data[$v['goods_id']][$area_id]:0;
            }
            $datalist[]=$info;
        }

        $cell = array(
            array('id','商品ID'),
            array('name','商品名称'),
            array('zzc_stock_num','中转仓总库存'),
            array('qzc_stock_num','前置仓总库存'),
            array('qzc_hotel_num','前置仓总数'),
        );
        foreach ($res_area as $v){
            $area_id = $v['area_id'];
            $area_name = $v['area_name'];
            $cell[]=array("zzc_stock_num_$area_id","{$area_name}中转仓库存");
            $cell[]=array("qzc_stock_num_$area_id","{$area_name}前置仓库存");
            $cell[]=array("qzc_hotel_num_$area_id","{$area_name}前置仓总数");
        }
        $filename = '现状表';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }

    public function trenddata(){
        $awhere = "is_in_hotel=1 and id not in (246)";//深圳无售酒
        $sql_area = "select id as area_id,region_name as area_name from savor_area_info where {$awhere} order by id asc ";
        $res_area = M()->query($sql_area);

        $m_static_goodstrend = new \Admin\Model\StaticGoodstrendModel();
        $m_static_goodstrend_area = new \Admin\Model\StaticGoodstrendAreadetailModel();
        $res_data = $m_static_goodstrend->getDataList('*',array(),'goods_id asc,week_number asc');
        $datalist = array();
        foreach ($res_data as $v){
            $week_time = "第{$v['week_number']}周({$v['week_start_date']}-{$v['week_end_date']})";
            $info = array('goods_id'=>$v['goods_id'],'goods_name'=>$v['goods_name'],'week_time'=>$week_time,'purchase_num'=>$v['purchase_num'],
                'zzc_stock_allnum'=>$v['zzc_stock_allnum'],'qzc_stock_allnum'=>$v['qzc_stock_allnum'],
                'qzc_hotel_allnum'=>$v['qzc_hotel_allnum'],'qzc_wo_allnum'=>$v['qzc_wo_allnum'],'groupby_num'=>$v['groupby_num']
            );
            $goodstrend_id = $v['id'];
            $res_area_detail = $m_static_goodstrend_area->getDataList('*',array('goodstrend_id'=>$goodstrend_id),'area_id asc');
            $area_data = array();
            foreach ($res_area_detail as $adv){
                $area_data[$adv['area_id']] = $adv;
            }
            foreach ($area_data as $arv){
                $area_id = $arv['area_id'];
                $info["zzc_stock_num_$area_id"] = $arv['zzc_stock_num'];
                $info["qzc_stock_num_$area_id"] = $arv['qzc_stock_num'];
                $info["qzc_hotel_num_$area_id"] = $arv['qzc_hotel_num'];
                $info["qzc_wo_num_$area_id"] = $arv['qzc_wo_num'];
            }
            $datalist[]=$info;
        }

        $cell = array(
            array('goods_id','商品ID'),
            array('goods_name','商品名称'),
            array('week_time','时间'),
            array('purchase_num','采购总量'),
            array('zzc_stock_allnum','中转仓总库存'),
            array('qzc_stock_allnum','前置仓总库存'),
            array('qzc_hotel_allnum','前置仓总数'),
            array('qzc_wo_allnum','前置仓总销量'),
            array('groupby_num','团购总销量'),
        );
        foreach ($res_area as $v){
            $area_id = $v['area_id'];
            $area_name = $v['area_name'];
            $cell[]=array("zzc_stock_num_$area_id","{$area_name}中转仓库存");
            $cell[]=array("qzc_stock_num_$area_id","{$area_name}前置仓库存");
            $cell[]=array("qzc_hotel_num_$area_id","{$area_name}前置仓总数");
            $cell[]=array("qzc_wo_num_$area_id","{$area_name}前置仓总销量");
        }
        $filename = '趋势表';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }

    public function uppolicy(){
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        $area_arr = array();
        foreach ($res_area as $v){
            if($v['id']==246){
                continue;
            }
            $area_arr[$v['id']]=$v;
        }
        $sql_goods = 'select * from savor_finance_goods where status=1';
        $res_goods = $m_area->query($sql_goods);
        $m_goods_policy = new \Admin\Model\GoodsPolicyModel();
        $m_goods_policy_hotel = new \Admin\Model\GoodsPolicyHotelModel();
        $m_goods_policy_wodata = new \Admin\Model\GoodsPolicyWodataModel();
        foreach ($res_goods as $v){
            $goods_id = $v['id'];
            $sql_config = "select * from savor_finance_goods_config where goods_id={$goods_id} and type=10 order by id desc";
            $res_config = $m_area->query($sql_config);
            if(empty($res_config[0]['id'])){
                echo "goods_id:$goods_id no data \r\n";
                continue;
            }
            $open_area_ids = explode(',',$res_config[0]['open_area_ids']);
            foreach ($area_arr as $av){
                $name = $v['name'].'-'.$av['region_name'].'-'.'通用政策';
                $open_integral = 0;
                if(in_array($av['id'],$open_area_ids)){
                    $open_integral = $res_config[0]['open_integral'];
                }
                $pdata = array('name'=>$name,'goods_id'=>$goods_id,'integral'=>$res_config[0]['integral'],'open_integral'=>$open_integral,
                    'media_id'=>$res_config[0]['media_id'],'area_id'=>$av['id'],'type'=>1,'status'=>1);
                $policy_id = $m_goods_policy->add($pdata);

                echo "goods_id:$goods_id savor_finance_goods_policy \r\n";

                $m_goods_policy_hotel->add(array('policy_id'=>$policy_id,'area_id'=>$av['id'],'hotel_id'=>0));

                echo "goods_id:$goods_id savor_finance_goods_policy_hotel \r\n";

                $sql_wodata = "select * from savor_finance_goods_config where goods_id={$goods_id} and type in (1,2,3,20,21) and status=1 order by id asc";
                $res_wodata = $m_area->query($sql_wodata);
                if(!empty($res_wodata[0]['id'])){
                    $wo_data = array();
                    foreach ($res_wodata as $wov){
                        $wo_data[]=array('policy_id'=>$policy_id,'name'=>$wov['name'],'is_required'=>$wov['is_required'],
                            'media_id'=>$wov['media_id'],'status'=>1,'type'=>$wov['type']);
                    }
                    $m_goods_policy_wodata->addAll($wo_data);

                    echo "goods_id:$goods_id savor_finance_goods_policy_wodata \r\n";
                }
            }

        }


    }

}