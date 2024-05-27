<?php
namespace Admin\Model;
use Common\Lib\Page;
class AwardHoteldataModel extends BaseModel{
	protected $tableName='finance_award_hoteldata';

    public function getHotelDatas($fileds,$where,$group='',$start=0,$size=5){
        if($start>=0 && $size>0){
            $list = $this->alias('a')
                ->field($fileds)
                ->join('savor_smallapp_user user on a.award_openid=user.openid','left')
                ->where($where)
                ->limit($start,$size)
                ->group($group)
                ->select();
            $count = $this->alias('a')
                ->field($fileds)
                ->join('savor_smallapp_user user on a.award_openid=user.openid','left')
                ->where($where)
                ->group($group)
                ->select();
            $count = count($count);
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show);
        }else{
            $data = $this->alias('a')
                ->field($fileds)
                ->join('savor_smallapp_user user on a.award_openid=user.openid','left')
                ->where($where)
                ->group($group)
                ->select();
        }
        return $data;
    }
}