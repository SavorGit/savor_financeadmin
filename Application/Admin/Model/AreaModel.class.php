<?php
namespace Admin\Model;

class AreaModel extends BaseModel{
	protected $tableName='area_info';

    public function getHotelAreaList(){
        $where['is_in_hotel'] = 1;
        $data = $this->field('id,region_name')->where($where)->select();
        return $data;
    }

}