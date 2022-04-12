<?php
namespace Admin\Model;
use Think\Model;
use Common\Lib\Page;

class BaseModel extends Model{

	public function getRow($field='*',$filter='',$order='',$group=''){
		$res = $this->field($field)
				    ->where($filter)
				    ->order($order)
				    ->group($group)
				    ->find();
		return $res;
	}

	public function getAll($field='*',$filter='',$offset=0,$limit=10,$order='',$group=''){
		$res = $this->field($field)
					->where($filter)
					->limit($offset,$limit)
					->order($order)
					->group($group)
					->select();
		return $res;
	}

    public function getAllData($field='*',$filter='',$order='',$group=''){
        $res = $this->field($field)
            ->where($filter)
            ->order($order)
            ->group($group)
            ->select();
        return $res;
    }

    public function getDataList($fields,$where,$orderby,$start=0,$size=0){
        if($start >= 0 && $size){
            $list = $this->field($fields)->where($where)->order($orderby)->limit($start,$size)->select();
            $count = $this->where($where)->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        }else{
            $data = $this->field($fields)->where($where)->order($orderby)->select();
        }
        return $data;
    }

    public function getInfo($condition){
        $result = $this->where($condition)->find();
        return $result;
    }

    public function addData($data){
        $result = $this->add($data);
        return $result;
    }

    public function updateData($condition,$data){
        $result = $this->where($condition)->save($data);
        return $result;
    }

    public function delData($condition){
        $result = $this->where($condition)->delete();
        return  $result;
    }
}

