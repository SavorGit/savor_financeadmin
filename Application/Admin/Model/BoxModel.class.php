<?php
namespace Admin\Model;

class BoxModel extends BaseModel{
    protected $tableName  ='box';

    public function getBoxByCondition($fields='box.*',$where,$group=''){
        $res = $this->alias('box')
            ->join('savor_room room on room.id= box.room_id','left')
            ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            ->join('savor_hotel_ext ext on hotel.id=ext.hotel_id','left')
            ->field($fields)
            ->where($where)
            ->group($group)
            ->select();
        return $res;
    }
}
