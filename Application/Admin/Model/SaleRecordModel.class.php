<?php
namespace Admin\Model;
use Common\Lib\Page;
class SaleRecordModel extends BaseModel{
	protected $tableName='crm_salerecord';

    public function getRecordList($fields,$where,$orderby,$limit='',$group=''){
        $data = $this->alias('record')
            ->field($fields)
            ->join('savor_ops_staff staff on record.ops_staff_id=staff.id','left')
            ->join('savor_sysuser sysuser on staff.sysuser_id=sysuser.id','left')
            ->where($where)
            ->order($orderby)
            ->limit($limit)
            ->group($group)
            ->select();
        return $data;
    }

    public function getCheckDataList($fields,$where,$orderby,$start=0,$size=0){
        if($start >= 0 && $size){
            $list = $this->alias('record')
                ->field($fields)
                ->join('savor_hotel hotel on record.signin_hotel_id=hotel.id','left')
                ->join('savor_ops_staff staff on record.ops_staff_id=staff.id','left')
                ->join('savor_sysuser sysuser on staff.sysuser_id=sysuser.id','left')
                ->where($where)
                ->order($orderby)
                ->limit($start,$size)
                ->select();
            $count = $this->alias('record')
                ->field($fields)
                ->join('savor_hotel hotel on record.signin_hotel_id=hotel.id','left')
                ->join('savor_ops_staff staff on record.ops_staff_id=staff.id','left')
                ->join('savor_sysuser sysuser on staff.sysuser_id=sysuser.id','left')
                ->where($where)->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        }else{
            $data = $this->field($fields)->where($where)->order($orderby)->select();
        }
        return $data;
    }
}