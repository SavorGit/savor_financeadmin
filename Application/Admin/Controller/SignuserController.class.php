<?php
namespace Admin\Controller;

class SignuserController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function datalist() {
    	$keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $status = I('status',0,'intval');

        $where = array();
        if($status){
            $where['status'] = $status;
        }
        if(!empty($keyword)){
            $where['name'] = array('like',"%$keyword%");
        }

        $start  = ($page-1) * $size;
        $m_signuser  = new \Admin\Model\SignuserModel();
        $result = $m_signuser->getDataList('*',$where, 'id desc', $start, $size);
        $all_status = array('1'=>'正常','2'=>'禁用');
        foreach ($result['list'] as $k=>$v){
        	$result['list'][$k]['statusstr'] = $all_status[$v['status']];
        }
        $this->assign('allstatus',$all_status);
        $this->assign('status',$status);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $result['list']);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('datalist');
    }
    
    public function adduser(){
        $id = I('id', 0, 'intval');
        $m_signuser  = new \Admin\Model\SignuserModel();
        if(IS_GET){
        	$dinfo = array('status'=>1);
        	if($id){
                $dinfo = $m_signuser->getInfo(array('id'=>$id));
        	}
        	$this->assign('dinfo',$dinfo);
        	$this->display('adduser');
        }else{
        	$name = I('post.name','','trim');
        	$status = I('post.status',1,'intval');
        	$where = array('name'=>$name);
        	if($id){
                $where['id']= array('neq',$id);
        		$res_user = $m_signuser->getInfo($where);
        	}else{
                $res_user = $m_signuser->getInfo($where);
        	}
        	if(!empty($res_user)){
        		$this->output('名称不能重复', 'signuser/adduser', 2, 0);
        	}

        	$data = array('name'=>$name,'status'=>$status);
        	if($id){
                $result = $m_signuser->updateData(array('id'=>$id),$data);
        	}else{
                $result = $m_signuser->add($data);
        	}
        	if($result){
        		$this->output('操作成功', 'signuser/datalist');
        	}else{
        		$this->output('操作失败', 'signuser/adduser',2,0);
        	}
        }
    }

    public function userdel(){
    	$id = I('get.id', 0, 'intval');
        $m_signuser  = new \Admin\Model\SignuserModel();
    	$condition = array('id'=>$id);
    	$result = $m_signuser->delData($condition);
    	if($result){
    		$this->output('删除成功', '',2);
    	}else{
    		$this->output('删除失败', '',2);
    	}
    }
}