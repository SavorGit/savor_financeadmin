<?php
namespace Admin\Model;
use Common\Lib\Page;

class SappuserModel extends BaseModel{
	protected $tableName='finance_sappuser';

    public function getList($fields,$where, $order='a.id desc', $start=0,$size=0){
        $list = $this->alias('a')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();
        if($start>=0 && $size>0){
            $count = $this->alias('a')
                ->join('savor_smallapp_user user on a.openid=user.openid','left')
                ->where($where)
                ->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show);
        }else{
            $data = $list;
        }
        return $data;
    }

}