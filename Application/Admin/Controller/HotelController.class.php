<?php
namespace Admin\Controller;

class HotelController extends BaseController {


    public function getCountyInfo(){
        $area_id = I('area_id');
        $parent_id = $this->getParentAreaid($area_id);
        $m_area_info =  new \Admin\Model\AreaModel();
        $fields = 'id,region_name';
        $where = array();
        $where['parent_id'] = $parent_id;
        $list = $m_area_info->getWhere($fields, $where);
        echo json_encode($list);
    }

    public function getCityList(){
        $province_id = I('province_id',0,'intval');
        $city_id = I('city_id',0,'intval');

        $where = array('parent_id'=>$province_id);
        $m_area_info =  new \Admin\Model\AreaModel();
        $list = $m_area_info->getDataList('id,region_name',$where,'id desc');
        if(!empty($list)){
            foreach ($list as $k=>$v){
                $is_select = '';
                if($v['id']==$city_id){
                    $is_select = 'selected';
                }
                $list[$k]['is_select'] = $is_select;
            }
        }
        echo json_encode($list);
    }


}