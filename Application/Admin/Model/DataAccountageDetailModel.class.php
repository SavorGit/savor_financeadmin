<?php
namespace Admin\Model;
use Common\Lib\Page;

class DataAccountageDetailModel extends BaseModel{
    
    protected $tableName='finance_data_accountage_detail';
    
    public function getList($fields,$where,$orderby,$start=0,$size=0){
        if($start >= 0 && $size){
            $list = $this->field($fields)
            ->where($where)
            ->order($orderby)
            ->limit($start,$size)
            ->select();
            $count = $this->where($where)
            ->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        }else{
            $data = $this->field($fields)
                         ->where($where)
                         ->order($orderby)
                         ->select();
        }
        return $data;
    }
}
