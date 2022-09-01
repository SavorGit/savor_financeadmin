<?php
namespace Dataexport\Controller;

class StockController extends BaseController {
    
    public function writeofflist() {
        $wo_status = I('wo_status',0,'intval');
        $area_id = I('area_id',0,'intval');
        $hotel_name = I('hotel_name','','trim');
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        $where = array('a.type'=>7);
        if($wo_status){
            $where['a.wo_status'] = $wo_status;
        }
        if($area_id){
            $where['hotel.area_id'] = $area_id;
        }
        if(!empty($hotel_name)){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }
        if(!empty($start_time) && !empty($end_time)){
            $now_start_time = date('Y-m-d 00:00:00',strtotime($start_time));
            $now_end_time = date('Y-m-d 23:59:59',strtotime($end_time));
            $where['a.add_time'] = array(array('egt',$now_start_time),array('elt',$now_end_time));
        }
        $fields = 'a.id,a.idcode,a.goods_id,a.op_openid,a.wo_status,a.wo_reason_type,a.add_time,goods.name,goods.specification_id,
        unit.name as unit_name,hotel.name as hotel_name,hotel.id as hotel_id';
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_list = $m_stock_record->getRecordList($fields,$where, 'a.id desc', 0,0);
        $data_list = array();
        if(!empty($res_list)){
            $all_wo_status = C('STOCK_WRITEOFF_STATUS');
            $all_reason = C('STOCK_USE_TYPE');
            $m_user = new \Admin\Model\SmallappUserModel();
            foreach ($res_list as $v){
                $v['wo_reason_type_str'] = $all_reason[$v['wo_reason_type']];
                $v['wo_status_str'] = $all_wo_status[$v['wo_status']];
                $res_user = $m_user->getInfo(array('openid'=>$v['op_openid']));
                $v['username'] = $res_user['nickname'];
                $v['usermobile'] = $res_user['mobile'];
                $data_list[] = $v;
            }
        }

        $cell = array(
            array('id','ID'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('idcode','唯一识别码'),
            array('name','商品名称'),
            array('goods_id','商品编号'),
            array('unit_name','单位'),
            array('wo_reason_type_str','核销原因'),
            array('wo_status_str','状态'),
            array('username','核销人'),
            array('usermobile','核销人手机号码'),
            array('add_time','核销时间'),
        );
        $filename = '核销管理';
        $this->exportToExcel($cell,$data_list,$filename,1);
    }


}