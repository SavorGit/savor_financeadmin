<?php
namespace Datacenter\Controller;
use Admin\Controller\BaseController;
class AwardhoteldataController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $area_id = I('area_id',0,'intval');
        $status = I('status',0,'intval');
        $stat_date = I('stat_date','');
        $hotel_name = I('hotel_name','','trim');

        $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }
        if(empty($stat_date)){
            $now_stat_date = date('Ym');
            $stat_date = date('Y-m-01');
        }else{
            $now_stat_date = date('Ym',strtotime($stat_date));
        }

        $all_status = C('ACTIVITY_AWARD_STATUS');
        $where = array('a.static_date'=>$now_stat_date);
        $where['a.hotel_id'] = array('not in',C('TEST_HOTEL'));
        if(!empty($hotel_name)){
            $where['a.hotel_name'] = array('like',"%$hotel_name%");
        }
        if($area_id){
            $where['a.area_id'] = $area_id;
        }
        if($status){
            $where['a.status'] = $status;
        }

        $start = ($pageNum-1)*$size;
        $fileds = 'a.*,user.nickName,user.name,user.idnumber,user.mobile';
        $m_awardhoteldata = new \Admin\Model\AwardHoteldataModel();
        $res_list = $m_awardhoteldata->getHotelDatas($fileds,$where,'',$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $user_name = !empty($v['name'])?$v['name']:$v['nickname'];
                $v['user_name'] = $user_name;
                $v['status_str'] = $all_status[$v['status']];
                $data_list[]=$v;
            }
        }

        $this->assign('stat_date',$stat_date);
        $this->assign('area',$area_arr);
        $this->assign('area_id',$area_id);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function thawintegral(){
        $id = I('id',0,'intval');
        $m_awardhoteldata = new \Admin\Model\AwardHoteldataModel();
        $vinfo = $m_awardhoteldata->getInfo(array('id'=>$id));
        if(IS_POST){
            $status = I('post.status',0,'intval');
            if($status==1){
                $userinfo = session('sysUserInfo');
                $handle_user_id = $userinfo['id'];
                $updata = array('status'=>1,'handle_user_id'=>$handle_user_id,'handle_time'=>date('Y-m-d H:i:s'));
                $m_awardhoteldata->updateData(array('id'=>$id),$updata);

                $m_userintegral = new \Admin\Model\UserIntegralModel();
                $m_integralrecord = new \Admin\Model\UserIntegralrecordModel();
                $rwhere = array('jdorder_id'=>$id,'type'=>array('in','26,27'));
                $res_recordinfo = $m_integralrecord->getAll('id,openid,integral,hotel_id,status',$rwhere,0,2,'id desc');
                if(!empty($res_recordinfo[0]['id']) && $res_recordinfo[0]['status']==2){
                    foreach ($res_recordinfo as $rv){
                        $record_id = $rv['id'];
                        $m_integralrecord->updateData(array('id'=>$record_id),array('status'=>1,'integral_time'=>date('Y-m-d H:i:s')));
                        $now_integral = $rv['integral'];
                        $res_integral = $m_userintegral->getInfo(array('openid'=>$rv['openid']));
                        if(!empty($res_integral)){
                            $userintegral = $res_integral['integral']+$now_integral;
                            $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                        }else{
                            $m_userintegral->add(array('openid'=>$rv['openid'],'integral'=>$now_integral));
                        }
                    }
                }

                $this->output('操作成功', 'awardhoteldata/datalist');
            }else{
                $this->output('无更改数据', 'awardhoteldata/datalist');
            }
        }else{
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }
}