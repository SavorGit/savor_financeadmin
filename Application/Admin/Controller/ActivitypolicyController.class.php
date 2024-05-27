<?php
namespace Admin\Controller;

class ActivitypolicyController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $type = I('type',0,'intval');
        $status = I('status',0,'intval');

        $m_activity_policy = new \Admin\Model\ActivityPolicyModel();
        $where = array();
        if($type){
            $where['type'] = $type;
        }
        if($status){
            $where['status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_activity_policy->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_sysuser = new \Admin\Model\SysuserModel();
            $m_ap_hotel = new \Admin\Model\ActivityPolicyHotelModel();
            $m_ap_goods = new \Admin\Model\ActivityPolicyGoodsModel();
            $all_status = C('TEMPLATE_STATUS');
            $all_types = C('TEMPLATE_TYPES');
            $activity_policy_types = C('ACTIVITY_POLICY_TYPES');
            foreach ($res_list['list'] as $v){
                $res_uinfo = $m_sysuser->getUserInfo($v['sysuser_id']);
                $v['sys_username'] = $res_uinfo['remark'];
                $hotel_num = 0;
                if($v['aptype']==1){
                    $hotel_num = '全部售酒餐厅';
                }else {
                    $all_hotels = $m_ap_hotel->getAllData('COUNT(DISTINCT hotel_id) as hotel_num', array('policy_id'=>$v['id']), '', '');
                    if(!empty($all_hotels[0]['hotel_num'])){
                        $hotel_num = $all_hotels[0]['hotel_num'];
                    }
                }
                $goods_num = 0;
                $all_goods = $m_ap_goods->getAllData('COUNT(id) as goods_num', array('policy_id'=>$v['id']), '', '');
                if(!empty($all_goods[0]['goods_num'])){
                    $goods_num = $all_goods[0]['goods_num'];
                }
                $v['goods_num'] = $goods_num;
                $v['hotel_num'] = $hotel_num;
                $v['aptype_str'] = $all_types[$v['aptype']];
                $v['type_str'] = $activity_policy_types[$v['type']];
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

    public function policyadd(){
        $id = I('id',0,'intval');
        $type = I('type',0,'intval');//1单瓶激励,2开瓶阶梯激励
        $m_activity_policy = new \Admin\Model\ActivityPolicyModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $area_id = I('post.area_id',0,'intval');
            $status = I('post.status',0,'intval');
            $aptype = I('post.aptype',0,'intval');//1通用政策,2特殊政策
            $num = I('post.num');
            $integral = I('post.integral');

            if($aptype==1){
                if(empty($area_id)){
                    $this->output('请选择城市', 'activitypolicy/policyadd',2,0);
                }
                $rwhere = array('type'=>$type,'aptype'=>1,'area_id'=>$area_id,'status'=>1);
                if($id){
                    $rwhere['id'] = array('neq',$id);
                }
                $res_policy = $m_activity_policy->getInfo($rwhere);
                if(!empty($res_policy)){
                    $this->output('一个城市只能有一个通用政策', 'activitypolicy/policyadd',2,0);
                }
            }
            $userInfo = session('sysUserInfo');
            $add_data = array('name'=>$name,'type'=>$type,'area_id'=>$area_id,'status'=>$status,'aptype'=>$aptype,'sysuser_id'=>$userInfo['id']);
            $m_ap_hotel = new \Admin\Model\ActivityPolicyHotelModel();
            if($id){
                $add_data['update_time'] = date('Y-m-d H:i:s');
                $dinfo = $m_activity_policy->getInfo(array('id'=>$id));
                if($aptype==1 && $dinfo['aptype']!=$aptype){
                    $m_ap_hotel->delData(array('policy_id'=>$id));
                }
            }
            if($type==2){
                if(empty($num) || empty($integral)){
                    $this->output('请输入激励配置', 'activitypolicy/policyadd',2,0);
                }
                $integral_config = array();
                foreach ($num as $k=>$v){
                    $integral_config[]=array('n'=>$v,'i'=>$integral[$k]);
                }
                $add_data['integral_config'] = json_encode($integral_config);
            }

            if($status==1){
                $m_ap_goods = new \Admin\Model\ActivityPolicyGoodsModel();
                $all_goods = $m_ap_goods->getAllData('COUNT(id) as goods_num', array('policy_id'=>$id), '', '');
                $goods_num = $all_goods[0]['goods_num'];
                if($goods_num==0){
                    $this->output('状态为执行中,需添加商品', 'activitypolicy/policyadd',2,0);
                }

                $all_hotels = $m_ap_hotel->getAllData('COUNT(DISTINCT hotel_id) as hotel_num', array('policy_id'=>$id), '', '');
                $hotel_num = intval($all_hotels[0]['hotel_num']);
                if($hotel_num==0){
                    $this->output('状态为执行中，需发布酒楼', 'activitypolicy/policyadd',2,0);
                }
            }
            if($id){
                $m_activity_policy->updateData(array('id'=>$id),$add_data);
            }else{
                $policy_id = $m_activity_policy->add($add_data);
                if($aptype==1){
                    $m_ap_hotel->add(array('policy_id'=>$policy_id,'area_id'=>$area_id,'hotel_id'=>0));
                }

            }

            $this->output('操作成功', 'activitypolicy/datalist');
        }else{
            $m_area = new \Admin\Model\AreaModel();
            $city_arr = $m_area->getHotelAreaList();
            array_unshift($city_arr,array('id'=>0,'region_name'=>'全部'));
            $dinfo = array('status'=>2);
            $integral_config = C('ACTIVITY_POLICY_INTEGRAL_CONFIG');
            if($id){
                $dinfo = $m_activity_policy->getInfo(array('id'=>$id));
                if(!empty($dinfo['integral_config'])){
                    $integral_config = json_decode($dinfo['integral_config'],true);
                }
            }
            $this->assign('integral_config',$integral_config);
            $this->assign('dinfo',$dinfo);
            $this->assign('city_arr',$city_arr);
            $display_html = 'policyadd'.$type;
            $this->display($display_html);
        }
    }

    public function goodslist(){
        $policy_id = I('policy_id',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $m_activity_policy = new \Admin\Model\ActivityPolicyModel();
        $res_apinfo = $m_activity_policy->getInfo(array('id'=>$policy_id));
        $ap_name = '活动政策--'.$policy_id.'--'.$res_apinfo['name'];
        $start = ($pageNum-1)*$size;
        $m_ap_goods = new \Admin\Model\ActivityPolicyGoodsModel();
        $fields = 'a.*,g.name as goods_name';
        $res_list = $m_ap_goods->getList($fields,array('a.policy_id'=>$policy_id),'a.id desc', $start,$size);
        $this->assign('datalist',$res_list['list']);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->assign('policy_id',$policy_id);
        $this->assign('ap_name',$ap_name);
        $this->display();
    }

    public function goodsadd(){
        $id = I('id',0,'intval');
        $policy_id = I('policy_id',0,'intval');

        $m_activity_policy = new \Admin\Model\ActivityPolicyModel();
        $res_apinfo = $m_activity_policy->getInfo(array('id'=>$policy_id));
        $m_ap_goods = new \Admin\Model\ActivityPolicyGoodsModel();
        if(IS_POST){
            $goods_id = I('post.goods_id',0,'intval');
            $coefficient = I('post.coefficient',0);
            $integral = I('post.integral',0,'intval');
            if($res_apinfo['type']==1){
                if(empty($integral)){
                    $this->output('请输入积分', 'activitypolicy/goodslist',2,0);
                }
            }
            $rwhere = array('policy_id'=>$policy_id,'goods_id'=>$goods_id);
            if($id){
                $rwhere['id'] = array('neq',$id);
            }
            $res_apgoods = $m_ap_goods->getInfo($rwhere);
            if(!empty($res_apgoods)){
                $this->output('请勿添加重复商品', 'activitypolicy/goodslist',2,0);
            }
            $userInfo = session('sysUserInfo');
            $add_data = array('policy_id'=>$policy_id,'goods_id'=>$goods_id,'coefficient'=>$coefficient,'integral'=>$integral,'sysuser_id'=>$userInfo['id']);

            if($id){
                $add_data['update_time'] = date('Y-m-d H:i:s');
                $m_ap_goods->updateData(array('id'=>$id),$add_data);
            }else{
                $m_ap_goods->add($add_data);
            }
            $this->output('操作成功!', 'activitypolicy/goodslist');
        }else{
            $vinfo = array('coefficient'=>1);
            if($id){
                $vinfo = $m_ap_goods->getInfo(array('id'=>$id));
                $policy_id = $vinfo['policy_id'];
            }
            $m_goods = new \Admin\Model\GoodsModel();
            $finance_goods = $m_goods->getDataList('id,name',array('status'=>1),'brand_id asc,id asc');
            $this->assign('policy_id',$policy_id);
            $this->assign('finance_goods',$finance_goods);
            $this->assign('vinfo',$vinfo);
            $this->assign('apinfo',$res_apinfo);
            $this->display('goodsadd');
        }
    }

    public function goodsdel(){
        $id = I('get.id',0,'intval');
        $m_ap_goods = new \Admin\Model\ActivityPolicyGoodsModel();
        $result = $m_ap_goods->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'activitypolicy/goodslist',2);
        }else{
            $this->output('操作失败', 'activitypolicy/goodslist',2,0);
        }
    }

    public function hoteladd(){
        $policy_id = I('policy_id',0,'intval');
        $m_activity_policy = new \Admin\Model\ActivityPolicyModel();
        $dinfo = $m_activity_policy->getInfo(array('id'=>$policy_id));
        if(IS_POST){
            $hbarr = $_POST['hbarr'];
            if(empty($hbarr)){
                $this->output('请选择酒楼','activitypolicy/hoteladd',2,0);
            }
            $hotel_arr = json_decode($hbarr, true);
            if(empty($hotel_arr)){
                $this->output('请选择酒楼','activitypolicy/hoteladd',2,0);
            }
            $m_activity_policy_hotel = new \Admin\Model\ActivityPolicyHotelModel();
            $fields = 'a.hotel_id,hotel.name as hotel_name,ap.id as ap_id,ap.name';
            $where = array('ap.type'=>$dinfo['type'],'ap.status'=>1);
            $where['a.hotel_id'] = array('in',$hotel_arr);
            $res_aphotels = $m_activity_policy_hotel->getActivityPolicyHotels($fields,$where,'a.id desc','0,1');
            if(!empty($res_aphotels[0]['hotel_id'])){
                $msg = "酒楼:{$res_aphotels[0]['hotel_id']}-$res_aphotels[0]['hotel_name'],已有政策:{$res_aphotels[0]['ap_id']}-{$res_aphotels[0]['name']}";
                $this->output($msg,'activitypolicy/datalist',2,0);
            }
            $m_hotel = new \Admin\Model\HotelModel();
            $hotel_data = array();
            foreach ($hotel_arr as $hv){
                $hotel_id = intval($hv['hotel_id']);
                if($hotel_id>0){
                    $res_price_hotel = $m_activity_policy_hotel->getInfo(array('policy_id'=>$policy_id,'hotel_id'=>$hotel_id));
                    if(!empty($res_price_hotel)){
                        continue;
                    }
                    $res_hotel = $m_hotel->getRow('area_id',array('id'=>$hotel_id));
                    $hotel_data[]=array('policy_id'=>$policy_id,'hotel_id'=>$hotel_id,'area_id'=>$res_hotel['area_id']);
                }
            }
            if(!empty($hotel_data)){
                $m_activity_policy_hotel->addAll($hotel_data);
            }
            $this->output('添加成功','activitypolicy/datalist');
        }else{
            $areaModel  = new \Admin\Model\AreaModel();
            $area_arr = $areaModel->getHotelAreaList();
            $this->assign('areainfo', $area_arr);
            $this->assign('vinfo', $dinfo);
            $this->display('hoteladd');
        }
    }

    public function hotellist() {
        $policy_id = I('policy_id',0,'intval');
        $keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array('a.policy_id'=>$policy_id);
        if(!empty($keyword)){
            $where['h.name'] = array('like',"%$keyword%");
        }
        $start  = ($page-1) * $size;
        $fields = 'a.id,a.add_time,h.id as hotel_id,h.name as hotel_name';
        $m_activity_policy_hotel = new \Admin\Model\ActivityPolicyHotelModel();
        $result = $m_activity_policy_hotel->getHotelDatas($fields,$where,'a.hotel_id desc', 'a.hotel_id',$start,$size);
        $datalist = $result['list'];

        $this->assign('policy_id',$policy_id);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function hoteldel(){
        $id = I('get.id',0,'intval');
        $m_activity_policy_hotel = new \Admin\Model\ActivityPolicyHotelModel();
        $result = $m_activity_policy_hotel->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'activitypolicy/hotellist',2);
        }else{
            $this->output('操作失败', 'activitypolicy/hotellist',2,0);
        }
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