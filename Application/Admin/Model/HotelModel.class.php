<?php
namespace Admin\Model;

class HotelModel extends BaseModel{
	protected $tableName='hotel';

    public function getStatisticalNumByHotelId($hotel_id,$type=''){
        $sql = "select id as room_id,hotel_id from savor_room where hotel_id='$hotel_id'";
        $res = $this->query($sql);
        $room_num = $box_num = $tv_num = 0;
        $all_rooms = array();
        foreach ($res as $k=>$v){
            $room_num++;
            $all_rooms[] = $v['room_id'];
        }
        if($type == 'room'){
            $nums = array('room_num'=>$room_num,'room'=>$all_rooms);
            return $nums;
        }
        if($room_num){
            $rooms_str = join(',', $all_rooms);
            $sql = "select id as box_id,room_id from savor_box where room_id in ($rooms_str) and state!=3 and flag=0";
            $res = $this->query($sql);
            $all_box = array();
            foreach ($res as $k=>$v){
                $box_num++;
                $all_box[] = $v['box_id'];
            }
            if($type == 'box'){
                $nums = array('box_num'=>$box_num,'box'=>$all_box);
                return $nums;
            }
            if($box_num){
                $box_str = join(',', $all_box);
                $sql = "select count(id) as tv_num from savor_tv where box_id in ($box_str)";
                $res = $this->query($sql);
                $tv_num = $res[0]['tv_num'];
                if($type == 'tv'){
                    $nums = array('tv_num'=>$tv_num);
                    return $nums;
                }
            }
        }
        $nums = array('room_num'=>$room_num,'box_num'=>$box_num,'tv_num'=>$tv_num);
        return $nums;
    }

}