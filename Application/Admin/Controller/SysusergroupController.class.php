<?php
namespace Admin\Controller;
/**
 * @desc 系统菜单管理类
 *
 */
use Common\Lib\Tree;
class SysusergroupController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function sysusergroupList() {
        $sysusergroup  = new \Admin\Model\SysusergroupModel();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where = " where 1";
        $searchTitle= I('searchTitle');
        if($searchTitle) {
            $where .= " and name like '%$searchTitle%'";
            $this->assign('searchTitle',  $searchTitle);
        }
        
        $result = $sysusergroup->getList($where, $orders, $start, $size);
        
        $this->assign('sysusergrouplist', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('index');
    }

    //新增用户测试
    public function sysusergroupAddTest(){
        $sysNode = new \Admin\Model\SysnodeModel();
        $rolePrivModel = new \Admin\Model\RolePrivModel();
        $sysusergroup = new \Admin\Model\SysusergroupModel();
        $acttype = I('acttype', 0, 'int');
        $name = I('post.name');
        //处理提交数据
        $manage_city = I('post.manage_city');
        foreach($manage_city as $key=>$v){
            $manage_city_str .= $separator . $v;
            $separator         = ',';
        }
        if(IS_POST) {
            //新增
            $id   = I('post.id', '', 'int');
            if(empty($_POST['menuid'])){
                $this->error('请选择权限');
            }
            if($acttype == 0){
                //判断分组名是否存在
                $name = trim($name);
                $count = $sysusergroup->getgroupCount(array('name'=>$name));
                if($count > 0){
                    $this->error('用户组已经存在');
                }
                $userInfo = session('sysUserInfo');
                $username = $userInfo['username'];
                $data['userName']= $username;
                $data['createtime']= date("Y-m-d H:i:s");
                $data['name']   = $name;
                $data['area_city'] = $manage_city_str;
                $result = $sysusergroup->addData($data, $acttype);
                $roleid = $sysusergroup->getLastInsID();
            }elseif($acttype == 1){
                //删除已经存在的
                $roleid = $id;
                $user_arr = $sysusergroup->getInfo($roleid);
                $sq_name = $user_arr['name'];
                if($sq_name != $name){
                    $count = $sysusergroup->getgroupCount(array('name'=>$name));
                    if($count > 0){
                        $this->error('用户组名称已经存在');
                    }
                }else{
                    $dat['area_city'] = $manage_city_str;
                    $dat['id'] = $user_arr['id'];
                    $sysusergroup->addData($dat, 1);
                }
            }
            if (is_array($_POST['menuid']) && count($_POST['menuid']) > 0) {
                $rolePrivModel->delData($roleid);
                $menuinfo = $sysNode->field('`id`,`ertype`,`m`,`c`,`a`,`menulevel`')->where('isenable=1')->select();
                foreach ($menuinfo as $_v) $menu_info[$_v[id]] = $_v;
                foreach($_POST['menuid'] as $menuid){
                    $info = array();
                    $info = $rolePrivModel->get_menuinfo(intval($menuid),$menu_info);
                    $info['nodeid'] = intval($menuid);
                    $info['roleid'] = $roleid;
                    $rolePrivModel->add($info);
                }
               $this->output('操作成功','sysusergroup/sysusergroupList');
            }else{
                $rolePrivModel->delData($roleid);
            }
        }
        //非提交处理
        if(1 === $acttype) {
            $gid = I('id', 0, 'int');
            if(!$gid) {
                $this->output('当前信息不存在!', 'sysusergroupList');
            }
            $resulta = $sysusergroup->getInfo($gid);
            $this->assign('vinfo', $resulta);
            $this->assign('acttype', 1);
        } else {
            $this->assign('acttype', 0);
        }
        //获取树形结构
        $matre = new Tree();
        $matre->icon = array('│ ','├─ ','└─ ');
        $matre->nbsp = '&nbsp;&nbsp;&nbsp;';
        //获取所有节点
        $result = $sysNode->getAllList();
        //获取权限表数据
        $priv_data = $rolePrivModel->getInfoByroleid($gid);
        foreach ($result as $n=>$t) {
            $result[$n]['cname'] = $t['name'];
            $result[$n]['checked'] = $rolePrivModel->is_checked($t,$gid,$priv_data)? ' checked' : '';
            $result[$n]['level'] = $rolePrivModel->get_level($t['id'],$result);
            $result[$n]['parentid_node'] = ($t['parentid'])? ' class="child-of-node-'.$t['parentid'].'"' : '';

        }
        $str  = "<tr id='node-\$id' \$parentid_node>
							<td style='padding-left:30px;'>\$spacer<input type='checkbox' name='menuid[]' value='\$id' level='\$level' \$checked onclick='javascript:checknode(this);'> \$cname</td>
						</tr>";
        $matre->init($result);
        $categorys = $matre->get_tree(0, $str);
        $ra['temp'] = $categorys;
        //获取省份
        $m_area_info = new \Admin\Model\AreaModel();
        $areaList = $m_area_info->getHotelAreaList();
        $nationwide = array('id'=>0,'region_name'=>'全国');
        array_unshift($areaList, $nationwide);
        $this->assign('areaList',$areaList);
        $this->assign('categor', $ra);
        $this->display('sysusergroupaddtest');
    }
    //删除 记录
    public function sysusergroupDel() {
        $gid = I('get.id', 0, 'int');
        if($gid) {
            $delete    = new \Admin\Model\SysusergroupModel();
            $rolePrivModel = new \Admin\Model\RolePrivModel();
            $rolePrivModel->delData($gid);
            $result = $delete -> delData($gid);
            if($result) {
                $this->output('删除成功', 'sysusergroupList',2);
            } else {
                $this->output('删除失败', 'sysusergroupList',2);
            }
        } else {
            $this->error('删除失败,缺少参数!');
        }
    }
}