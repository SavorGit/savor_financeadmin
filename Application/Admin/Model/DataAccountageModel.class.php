<?php
namespace Admin\Model;
use Common\Lib\Page;

class DataAccountageModel extends BaseModel{
    
    protected $tableName='finance_data_accountage';
    
    public function getList($fields,$where,$orderby,$start=0,$size=0){
        if($start >= 0 && $size){
            $list = $this->alias('a')
            
            ->field($fields)
            ->where($where)
            ->order($orderby)
            ->limit($start,$size)
            ->select();
            $count = $this->alias('a')
            
            ->where($where)
            ->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        }else{
            $data = $this->alias('a')
            
            ->field($fields)
            ->where($where)
            ->order($orderby)
            ->select();
        }
        return $data;
    }
}
