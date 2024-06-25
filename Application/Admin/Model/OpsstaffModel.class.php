<?php
namespace Admin\Model;
class OpsstaffModel extends BaseModel{
    protected $tableName = 'ops_staff';

    public function getStaffInfo($fields,$where){
        $data = $this->alias('a')
            ->join('savor_sysuser u on a.sysuser_id=u.id','left')
            ->field($fields)
            ->where($where)
            ->find();
        return $data;
    }
}