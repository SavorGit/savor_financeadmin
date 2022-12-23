<?php
namespace Admin\Controller;

class PricetemplateController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $type = I('type',0,'intval');
        $status = I('status',0,'intval');

        $m_pricetemplate = new \Admin\Model\PriceTemplateModel();
        $where = array();
        if($type){
            $where['type'] = $type;
        }
        if($status){
            $where['status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_pricetemplate->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_sysuser = new \Admin\Model\SysuserModel();
            $all_status = C('TEMPLATE_STATUS');
            $all_types = C('TEMPLATE_TYPES');
            foreach ($res_list['list'] as $v){
                $res_uinfo = $m_sysuser->getUserInfo($v['sysuser_id']);
                $v['sys_username'] = $res_uinfo['remark'];
                $hotel_num = 0;
                if($v['type']==1){
                    $hotel_num = '全部售酒餐厅';
                }else {
                    $m_pricehotel = new \Admin\Model\PriceTemplateHotelModel();
                    $all_hotels = $m_pricehotel->getAllData('COUNT(DISTINCT hotel_id) as hotel_num', array('template_id'=>$v['id']), '', '');
                    if(!empty($all_hotels[0]['hotel_num'])){
                        $hotel_num = $all_hotels[0]['hotel_num'];
                    }
                }
                $v['hotel_num'] = $hotel_num;
                $v['type_str'] = $all_types[$v['type']];
                $v['status_str'] = $all_status[$v['status']];
                $data_list[] = $v;
            }
        }
        $this->assign('data',$data_list);
        $this->assign('type',$type);
        $this->assign('status',$status);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function templateadd(){
        $id = I('id',0,'intval');
        $m_pricetemplate = new \Admin\Model\PriceTemplateModel();
        $m_pricegoods = new \Admin\Model\PriceTemplateGoodsModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $type = I('post.type',0,'intval');//1通用政策,2特殊政策
            $status = I('post.status',0,'intval');
            $goods_ids = I('post.goods_ids');
            $price = I('post.price');
            $goods_price = array();
            foreach ($goods_ids as $k=>$v){
                if(!empty($price[$k])){
                    $goods_price[$v]=array('goods_id'=>$v,'settlement_price'=>$price[$k]);
                }
            }
            if($status==1){
                if(empty($goods_price)){
                    $this->output('请输入商品的结算价', 'pricetemplate/templateadd',2,0);
                }
            }
            $userInfo = session('sysUserInfo');
            $add_data = array('name'=>$name,'type'=>$type,'status'=>$status);
            $is_uphotel = 0;
            if($id){
                $old_templ = $m_pricetemplate->getInfo(array('id'=>$id));
                if($old_templ['type']!=$type){
                    $is_uphotel = 1;
                }
                $m_log  = new \Admin\Model\PriceTemplateLogModel();
                $userInfo = session('sysUserInfo');
                if($old_templ['name']!=$name){
                    $log_data = array('template_id'=>$id,'name'=>$name,'sysuser_id'=>$userInfo['id'],'action'=>10);
                    $m_log->add($log_data);
                }
                if($old_templ['type']!=$type){
                    $log_data = array('template_id'=>$id,'type'=>$type,'sysuser_id'=>$userInfo['id'],'action'=>11);
                    $m_log->add($log_data);
                }
                if($old_templ['status']!=$status){
                    $log_data = array('template_id'=>$id,'status'=>$status,'sysuser_id'=>$userInfo['id'],'action'=>12);
                    $m_log->add($log_data);
                }

                $template_id = $id;
                $add_data['update_time'] = date('Y-m-d H:i:s');
                $m_pricetemplate->updateData(array('id'=>$template_id),$add_data);
                $field = 'id,goods_id,settlement_price';
                $res_pgoods = $m_pricegoods->getDataList($field,array('template_id'=>$template_id),'id desc');
                $old_goods_price = array();
                foreach ($res_pgoods as $v){
                    $old_goods_price[$v['goods_id']] = $v;
                }
                if(count($res_pgoods) != count($goods_price)){
                    $is_uphotel = 1;
                }
                foreach ($goods_price as $gv){
                    if(isset($old_goods_price[$gv['goods_id']])){
                        $old_price = $old_goods_price[$gv['goods_id']]['settlement_price'];
                        if($gv['settlement_price']!=$old_price){
                            $updata = array('settlement_price'=>$gv['settlement_price'],'update_time'=>date('Y-m-d H:i:s'));
                            $m_pricegoods->updateData(array('id'=>$old_goods_price[$gv['goods_id']]['id']),$updata);

                            $log_data = array('template_id'=>$template_id,'goods_id'=>$gv['goods_id'],'settlement_price'=>$gv['settlement_price'],'sysuser_id'=>$userInfo['id'],'action'=>20);
                            $m_log->add($log_data);
                        }
                    }else{
                        $is_uphotel = 1;
                        $gv['template_id'] = $template_id;
                        $gv['sysuser_id'] = $userInfo['id'];
                        $m_pricegoods->add($gv);
                    }
                }
                foreach ($old_goods_price as $ov){
                    if(!isset($goods_price[$ov['goods_id']])){
                        $is_uphotel = 1;
                        $del_where = array('template_id'=>$template_id,'goods_id'=>$ov['goods_id']);
                        $m_pricegoods->delData($del_where);

                        $log_data = array('template_id'=>$template_id,'goods_id'=>$ov['goods_id'],'sysuser_id'=>$userInfo['id'],'action'=>21);
                        $m_log->add($log_data);
                    }
                }
            }else{
                $add_data['sysuser_id'] = $userInfo['id'];
                $template_id = $m_pricetemplate->add($add_data);
                $goods_datas = array();
                foreach ($goods_price as $v){
                    $v['template_id'] = $template_id;
                    $v['sysuser_id'] = $userInfo['id'];
                    $goods_datas[]=$v;
                }
                $m_pricegoods->addAll($goods_datas);
                if($type==1){
                    $is_uphotel = 1;
                }
            }
            $m_pricehotel = new \Admin\Model\PriceTemplateHotelModel();
            if($is_uphotel==1){
                $all_hotel_ids = $m_pricehotel->getAllData('hotel_id',array('template_id'=>$template_id),'','hotel_id');
                if(!empty($all_hotel_ids)){
                    $m_pricehotel->delData(array('template_id'=>$template_id));
                    if($type==1){
                        $hotel_goods = array();
                        foreach ($goods_price as $v){
                            $hotel_goods[]=array('template_id'=>$template_id,'goods_id'=>$v['goods_id'],'hotel_id'=>0);
                        }
                        $m_pricehotel->addAll($hotel_goods);
                    }else{
                        foreach ($goods_price as $v){
                            $hotel_goods = array();
                            foreach ($all_hotel_ids as $hv){
                                $hotel_goods[]=array('template_id'=>$template_id,'goods_id'=>$v['goods_id'],'hotel_id'=>$hv['hotel_id']);
                            }
                            $m_pricehotel->addAll($hotel_goods);
                        }
                    }
                }else{
                    if($type==1){
                        $hotel_goods = array();
                        foreach ($goods_price as $v){
                            $hotel_goods[]=array('template_id'=>$template_id,'goods_id'=>$v['goods_id'],'hotel_id'=>0);
                        }
                        $m_pricehotel->addAll($hotel_goods);
                    }
                }
            }
            $this->output('操作成功', 'pricetemplate/datalist');
        }else{
            $m_goods = new \Admin\Model\GoodsModel();
            $res_goods = $m_goods->getAllData('id,name,barcode',array('status'=>1),'brand_id asc');

            $dinfo = array();
            if($id){
                $dinfo = $m_pricetemplate->getInfo(array('id'=>$id));
                $field = 'id,goods_id,settlement_price';
                $res_pgoods = $m_pricegoods->getDataList($field,array('template_id'=>$id),'id desc');
                $price_goods = array();
                foreach ($res_pgoods as $v){
                    $price_goods[$v['goods_id']] = $v;
                }
                $tmp_price_goods = $tmp_noprice_goods = array();
                foreach ($res_goods as $v){
                    if(isset($price_goods[$v['id']])){
                        $v['settlement_price'] = $price_goods[$v['id']]['settlement_price'];
                        $tmp_price_goods[]=$v;
                    }else{
                        $v['settlement_price'] = '';
                        $tmp_noprice_goods[]=$v;
                    }
                }
                $res_goods = array_merge($tmp_price_goods,$tmp_noprice_goods);
            }

            $this->assign('dinfo',$dinfo);
            $this->assign('goods',$res_goods);
            $this->display();
        }
    }

    public function hoteladd(){
        $template_id = I('template_id',0,'intval');
        $m_pricetemplate = new \Admin\Model\PriceTemplateModel();
        if(IS_POST){
            $hbarr = $_POST['hbarr'];
            if(empty($hbarr)){
                $this->output('请选择酒楼','pricetemplate/hoteladd',2,0);
            }
            $hotel_arr = json_decode($hbarr, true);
            if(empty($hotel_arr)){
                $this->output('请选择酒楼','pricetemplate/hoteladd',2,0);
            }
            $m_pricegoods = new \Admin\Model\PriceTemplateGoodsModel();
            $field = 'id,goods_id,template_id';
            $res_pgoods = $m_pricegoods->getDataList($field,array('template_id'=>$template_id),'id desc');
            $m_pricehotel = new \Admin\Model\PriceTemplateHotelModel();
            foreach ($hotel_arr as $hv){
                $hotel_id = intval($hv['hotel_id']);
                if($hotel_id>0){
                    $res_price_hotel = $m_pricehotel->getInfo(array('template_id'=>$template_id,'hotel_id'=>$hotel_id));
                    if(!empty($res_price_hotel)){
                        continue;
                    }
                    $hotel_goods = array();
                    foreach ($res_pgoods as $v){
                        $hotel_goods[]=array('template_id'=>$template_id,'goods_id'=>$v['goods_id'],'hotel_id'=>$hotel_id);
                    }
                    if(!empty($hotel_goods)){
                        $m_pricehotel->addAll($hotel_goods);
                    }
                }
            }
            $this->output('添加成功','pricetemplate/datalist');
        }else{
            $dinfo = $m_pricetemplate->getInfo(array('id'=>$template_id));
            $areaModel  = new \Admin\Model\AreaModel();
            $area_arr = $areaModel->getHotelAreaList();
            $this->assign('areainfo', $area_arr);
            $this->assign('vinfo', $dinfo);
            $this->display('hoteladd');
        }
    }

    public function hotellist() {
        $template_id = I('template_id',0,'intval');
        $keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array('a.template_id'=>$template_id);
        if(!empty($keyword)){
            $where['h.name'] = array('like',"%$keyword%");
        }
        $start  = ($page-1) * $size;
        $fields = 'a.add_time,h.id as hotel_id,h.name as hotel_name';
        $m_couponhotel = new \Admin\Model\PriceTemplateHotelModel();
        $result = $m_couponhotel->getHotelDatas($fields,$where,'a.hotel_id desc', 'a.hotel_id',$start,$size);
        $datalist = $result['list'];

        $this->assign('template_id',$template_id);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function hoteldel(){
        $template_id = I('get.tid',0,'intval');
        $hotel_id = I('get.hid',0,'intval');
        $m_templatehotel = new \Admin\Model\PriceTemplateHotelModel();
        $where = array('template_id'=>$template_id,'hotel_id'=>$hotel_id);
        $result = $m_templatehotel->delData($where);
        if($result){
            $m_log  = new \Admin\Model\PriceTemplateLogModel();
            $userInfo = session('sysUserInfo');
            $log_data = array('template_id'=>$template_id,'hotel_id'=>$hotel_id,'sysuser_id'=>$userInfo['id'],'action'=>31);
            $m_log->add($log_data);
            $this->output('操作成功!', 'pricetemplate/hotellist',2);
        }else{
            $this->output('操作失败', 'pricetemplate/hotellist',2,0);
        }
    }

    public function history(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $template_id = I('template_id',0,'intval');

        $m_pricetemplate = new \Admin\Model\PriceTemplateModel();
        $res_info = $m_pricetemplate->getInfo(array('id'=>$template_id));

        $where = array('template_id'=>$template_id);
        $start  = ($page-1) * $size;
        $m_log  = new \Admin\Model\PriceTemplateLogModel();
        $result = $m_log->getDataList('*',$where,'id desc',$start,$size);
        $datalist = array();
        if(!empty($result)){
            $all_status = C('TEMPLATE_STATUS');
            $all_types = C('TEMPLATE_TYPES');
            $log_types = array('10'=>'修改模板名称','11'=>'修改模板类型','12'=>'修改模板状态',
                '20'=>'修改商品结算价','21'=>'去除商品结算价','30'=>'新增酒楼','31'=>'删除酒楼');
            $datalist = $result['list'];
            $m_sysuser = new \Admin\Model\SysuserModel();
            $m_goods = new \Admin\Model\GoodsModel();
            $m_hotel = new \Admin\Model\HotelModel();
            foreach ($datalist as $k=>$v){
                $res_user = $m_sysuser->getUserInfo($v['sysuser_id']);
                switch ($v['action']){
                    case 10:
                        $content = $v['name'];
                        break;
                    case 11:
                        $content = $all_types[$v['type']];
                        break;
                    case 12:
                        $content = $all_status[$v['status']];
                        break;
                    case 20:
                        $res_goods = $m_goods->getInfo(array('id'=>$v['goods_id']));
                        $content = "{$res_goods['name']},结算价:{$v['settlement_price']}";
                        break;
                    case 21:
                        $res_goods = $m_goods->getInfo(array('id'=>$v['goods_id']));
                        $content = "{$res_goods['name']}";
                        break;
                    case 30:
                    case 31:
                        $res_hotel = $m_hotel->getInfo(array('id'=>$v['hotel_id']));
                        $content = "{$res_hotel['name']}";
                        break;
                    default:
                        $content = '';
                }
                $change_content = $log_types[$v['action']].': '.$content;
                $datalist[$k]['change_content'] = $change_content;
                $datalist[$k]['username'] = $res_user['remark'];
            }
        }
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('template_id',$template_id);
        $this->assign('name',$res_info['name']);
        $this->display('history');
    }

    public function getSaleHotel() {
        $area_id = I('area_id',0,'intval');
        $hotel_name = I('hotel_name', '','trim');
        $m_hotel = new \Admin\Model\HotelModel();
        $where = array('hotel.state'=>1,'hotel.flag'=>0,'ext.is_salehotel'=>1);
        if($area_id){
            $where['hotel.area_id'] = $area_id;
        }
        if(!empty($hotel_name)){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }
        $field = 'hotel.id as hid,hotel.name as hname';
        $result = $m_hotel->getHotelDatas($field,$where,'hotel.pinyin asc');
        $res = array('code'=>1,'msg'=>'','data'=>$result);
        echo json_encode($res);
    }


}