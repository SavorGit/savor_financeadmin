<?php
namespace Admin\Model;
class StaffModel extends BaseModel{
    protected $tableName = 'integral_merchant_staff';

    public function getMerchantStaff($fileds,$where){
        $res = $this->alias('a')
            ->field($fileds)
            ->join('savor_integral_merchant merchant on a.merchant_id=merchant.id','left')
            ->where($where)
            ->select();
        return $res;
    }
}