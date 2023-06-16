<?php
namespace Admin\Model;
use Common\Lib\Page;
use Think\Model;
class SignhotelModel extends BaseModel{
	protected $tableName='crm_signhotel';

	public function getAllList($fields,$where, $order='a.id desc'){
	    $list = $this->alias('a')
            	     ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            	     ->field($fields)
            	     ->where($where)
            	     ->order($order)
            	     ->select();
	    return $list;
	}

}