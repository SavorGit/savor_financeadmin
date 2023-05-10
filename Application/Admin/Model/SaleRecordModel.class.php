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

}