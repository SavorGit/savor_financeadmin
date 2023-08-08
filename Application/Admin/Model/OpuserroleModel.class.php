<?php
namespace Admin\Model;
class OpuserroleModel extends BaseModel{
    protected $tableName = 'opuser_role';
    public function getAllRole($fields,$where,$order,$limit=''){
        $data = $this->alias('a')
        ->join('savor_sysuser as user on user.id=a.user_id','left')
        ->field($fields)
        ->where($where)
        ->order($order)
        ->limit($limit)
        ->select();
        return $data;
    }
}