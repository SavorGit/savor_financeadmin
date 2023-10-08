<?php
namespace Admin\Controller;

class HotelblacklistController extends BaseController {

    public function __construct() {
        parent::__construct();
    }

    public function datalist() {
    	$keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数

        $where = array();
        if(!empty($keyword)){
            $where['hotel_name'] = array('like',"%$keyword%");
        }

        $start  = ($page-1) * $size;
        $m_blacklist  = new \Admin\Model\HotelBlacklistModel();
        $result = $m_blacklist->getDataList('*',$where, 'id desc', $start, $size);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $result['list']);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('datalist');
    }

    public function addhotel(){
        $m_hotel = new \Admin\Model\HotelModel();
        if(IS_GET){
            $field = 'hotel.id,hotel.name,area.region_name as area_name';
            $where = array('hotel.state'=>1,'hotel.flag'=>0,'ext.is_salehotel'=>1);
            $hotel_list = $m_hotel->getHotelDatas($field,$where,'hotel.area_id asc,hotel.pinyin asc');
            $this->assign('hotel_list',$hotel_list);
            $this->display('addhotel');
        }else{
            $hotel_id = I('post.hotel_id', 0, 'intval');

            $m_blacklist  = new \Admin\Model\HotelBlacklistModel();
            $res_blacklist = $m_blacklist->getInfo(array('hotel_id'=>$hotel_id));
            if(!empty($res_blacklist)){
                $this->output('酒楼不能重复添加', 'hotelblacklist/addhotel', 2, 0);
            }

            $res_hotel = $m_hotel->getInfo(array('id'=>$hotel_id));
            $data = array('hotel_id'=>$hotel_id,'hotel_name'=>$res_hotel['name']);
            $result = $m_blacklist->add($data);
            if($result){
                $this->output('操作成功', 'hotelblacklist/datalist');
            }else{
                $this->output('操作失败', 'hotelblacklist/addhotel',2,0);
            }
        }
    }

    public function delhotel(){
    	$id = I('get.id', 0, 'intval');
        $m_blacklist  = new \Admin\Model\HotelBlacklistModel();
        $condition = array('id'=>$id);
        $result = $m_blacklist->delData($condition);
        if($result){
    		$this->output('删除成功', '',2);
    	}else{
    		$this->output('删除失败', '',2);
    	}
    }
}