<?php
namespace Admin\Controller;
class IndexController extends BaseController {
    public function index(){
        $m_sysmenu = new \Admin\Model\SysmenuModel();
        $result = $this->getMyMenuList();
        $menuList = $result['myMenuList'];
        $ico_arr = $result['ico_arr'];
        $version = $m_sysmenu->getMysqlVersion();
        $this->assign('ico_arr',$ico_arr);
        $this->assign('menuList', $menuList);
        $this->assign('VerMysql', $version);
        $this->assign('VerPHP', PHP_VERSION);
        $this->display();
    }

    //记录日志
    public function receiveLogs($val, $username){
        $path = '/tmp/savor_logs';
        $time = date('Y-m-d h:i:s',time());
        $file = $path."/".date('Y-m-d',time()).".log";
        if ( !(is_dir($path)) ) {
            mkdir ( $path, 0777, true );
        }
        $fp = fopen($file,"a+");
        $content ="";
        $start="time:".$time."\r\n"."username:".$username."\r\n"."---------- content start ----------"."\r\n";
        $end ="\r\n"."---------- content end ----------"."\r\n\n";
        $content=$start."".$val."".$end;
        fwrite($fp,$content);
        fclose($fp);
    }

    public function getMyMenuList(){
        $userInfo = session('sysUserInfo');
        $username = $userInfo['username'];
        $uid = $userInfo['id'];
        $groupid = $userInfo['groupid'];//权限组

        $mediaModel = new \Admin\Model\MediaModel();
        $ico_arr = array();
        if($groupid ==1){
            $m_node_menu = new \Admin\Model\SysnodeModel();
            $where = array();
            $where['isenable'] = 1;
            $where['menulevel'] = 0;
            $first_menu = $m_node_menu->getWhere($where,'id,name,media_id,select_media_id,m,c,a','displayorder asc');
            foreach($first_menu as $key=>$val){
                $where = array();
                $where['isenable'] = 1;
                $where['menulevel'] = 1;
                $where['parentid'] = $val['id'];
                $secend_menu = $m_node_menu->getWhere($where,'id,name,media_id,select_media_id,m,c,a','displayorder,id asc');
                $first_menu[$key]['child'] = $secend_menu;
                if($val['select_media_id']){
                    $mediainfo = $mediaModel->getMediaInfoById($val['select_media_id']);
                    $ico_arr[$key]['img_url'] = $mediainfo['oss_addr'];
                    $ico_arr[$key]['id'] = $val['id'];
                }
            }
        }else {
            //获取当前用户组的一级菜单
            $m_role_priv = new \Admin\Model\RolePrivModel();
            $where = " and rp.roleid=".$groupid." and n.menulevel=0 and n.isenable=1";
            $order = " n.displayorder,n.id asc ";
            $first_menu = $m_role_priv->getMenuList($where,$order);

            foreach($first_menu as $key=>$val){
                $where = ' and rp.roleid='.$groupid.' and n.parentid='.$val['id'].' and n.menulevel=1 and n.isenable=1 ';
                $secend_menu = $m_role_priv->getMenuList($where,$order);
            
                if(empty($secend_menu)){
                    unset($first_menu[$key]);
                }else {
                    $first_menu[$key]['child'] = $secend_menu;
                }
                if($val['select_media_id']){
                    $mediainfo = $mediaModel->getMediaInfoById($val['select_media_id']);
                    $ico_arr[$key]['img_url'] = $mediainfo['oss_addr'];
                    $ico_arr[$key]['id'] = $val['id'];
                }
            }
        }
        foreach($first_menu as $k=>$v){
            if($v['media_id']){
                $mediainfo = $mediaModel->getMediaInfoById($v['media_id']);
            }
            $parent_s = 
                        '<li  id="'.$v['id'].'">
                            <a href="#menu'.$v['c'].'" data-index="'.$v['c'].'">
                                <img class="menulist_ico" style="height:14px;margin-right: 5px;" src="'.$mediainfo['oss_addr'].'">'
                                    .$v['name']
                                .'<img style="float:right;margin-right:4px;margin-top:7px" src="/Public/admin/assets/img/sysmenuico/more.png" />
                                    </a>
                            <ul id="menu'.$v['c'].'" class="collapse in">
                                <input type="hidden" id="mo_'.$v['id'].'" value="'.$mediainfo['oss_addr'].'"  />';
            $child = '';
            if($v['child']) {
                $menu_list = '';
                foreach ($v['child'] as $key => $vv){
                    $display='';
                    if($vv['id']==21){
                        $display='style="display:none;"';
                    }
                    
                    $back_url = $vv['c'].'/'.$vv['a'];
                    $child .= '<li '.$display.'><a href="'.$vv[m].'/'.$back_url.'" target="navTab" rel="'.$back_url.'">'.$vv['name'].'</a></li>';
                    
                }
            }
            $parent_e = '</ul></li>';
            $menu_list = $parent_s.$child.$parent_e;
            $myMenuList .= $menu_list;
        }
        $result['myMenuList'] = $myMenuList;
        $result['ico_arr'] = $ico_arr;
        return $result;
    }
}