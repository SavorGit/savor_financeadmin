<?php
namespace Admin\Controller;

class SappuserController extends BaseController {

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
            $where['a.status'] = $status;
        }
        if(!empty($keyword)){
            $where['user.nickName'] = array('like',"%$keyword%");
        }

        $start  = ($page-1) * $size;
        $m_user  = new \Admin\Model\SappuserModel();
        $fields = 'a.*,user.nickName,user.avatarUrl';
        $result = $m_user->getList($fields,$where, 'a.id desc', $start,$size);
        $all_status = array('1'=>'正常','2'=>'禁用');
        $all_permission = C('SAPP_STOCK_PERMISSION');
        foreach ($result['list'] as $k=>$v){
        	$result['list'][$k]['statusstr'] = $all_status[$v['status']];
        	$result['list'][$k]['permission_type_str'] = $all_permission[$v['permission_type']];
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
        $m_user  = new \Admin\Model\SappuserModel();
        if(IS_GET){
        	$dinfo = array('status'=>1);
        	$openid = '';
        	if($id){
                $dinfo = $m_user->getInfo(array('id'=>$id));
                $openid = $dinfo['openid'];
        	}
            $m_staff = new \Admin\Model\StaffModel();
            $where = array('a.merchant_id'=>3,'a.status'=>1);
            $fileds = 'a.openid,user.nickName as name,user.mobile';
            $all_user = $m_staff->getMerchantStaff($fileds,$where);
            foreach ($all_user as $k=>$v){
                $is_select = '';
                if($v['openid']==$openid){
                    $is_select = 'selected';
                }
                $all_user[$k]['is_select'] = $is_select;
                $all_user[$k]['name'] = $v['name']."({$v['mobile']})";
            }

        	$this->assign('all_user',$all_user);
        	$this->assign('vinfo',$dinfo);
        	$this->display('adduser');
        }else{
        	$openid = I('post.openid','','trim');
        	$status = I('post.status',1,'intval');
        	$permission_type = I('post.permission_type',1,'intval');
        	$where = array('openid'=>$openid);
        	if($id){
                $where['id']= array('neq',$id);
        		$res_user = $m_user->getInfo($where);
        	}else{
                $res_user = $m_user->getInfo($where);
        	}
        	if(!empty($res_user)){
        		$this->output('请勿重复添加', 'sappuser/adduser', 2, 0);
        	}
            $sysuserInfo = session('sysUserInfo');

        	$data = array('openid'=>$openid,'status'=>$status,'permission_type'=>$permission_type,'sysuser_id'=>$sysuserInfo['id']);
        	if($id){
                $data['update_time'] = date('Y-m-d H:i:s');
                $result = $m_user->updateData(array('id'=>$id),$data);
        	}else{
                $result = $m_user->add($data);
        	}
        	if($result){
        		$this->output('操作成功', 'sappuser/datalist');
        	}else{
        		$this->output('操作失败', 'sappuser/adduser',2,0);
        	}
        }
    }

    public function deluser(){
    	$id = I('get.id', 0, 'intval');
        $m_user  = new \Admin\Model\SappuserModel();
        $condition = array('id'=>$id);
        $result = $m_user->delData($condition);
        if($result){
    		$this->output('删除成功', '',2);
    	}else{
    		$this->output('删除失败', '',2);
    	}
    }
}