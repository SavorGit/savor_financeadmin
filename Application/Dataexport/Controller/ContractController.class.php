<?php
namespace Dataexport\Controller;

class ContractController extends BaseController {
    
    public function proxysale() {
        
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $area_id    = I('area_id',0,'intval');
        $ctype      = I('ctype',0,'intval');
        $status     = I('status',0,'intval');
        $sign_user_id = I('sign_user_id',0,'intval');
        $name       = I('name','','trim');
        $orders     = "a.id desc";
        
        $where = [];
        if($start_date){
            $where['a.sign_time']= array('EGT',$start_date);
        }
        if($end_date){
            $where['a.sign_time']= array('ELT',$end_date);
        }
        if($area_id){
            $where['a.area_id'] = $area_id;
        }
        if($ctype){
            $where['a.ctype'] = $ctype;
        }
        if($status){
            $now_date = date('Y-m-d');
            if($status==1){
                //$s_time>=$now_date
                $where['a.contract_stime'] = array('gt',$now_date);
                $where['a.status'] = array('neq',4);
                
            }else if($status ==2){
                $where['a.contract_stime'] = array('ELT',$now_date);
                $where['a.contract_etime']  = array('GT',$now_date);
                $where['a.status'] = array('neq',4);
            }else if($status==3){
                $where['a.contract_etime'] = array('ELT',$now_date);
                $where['a.status'] = array('neq',4);
            }else if($status==4){
                $where['a.status']=4;
            }
        }
        if($sign_user_id){
            $where['a.sign_user_id'] = $sign_user_id;
        }
        if($name){
            $where['a.name'] = array('like',"%".$name."%");
        }
        $where['a.type'] = 20;
        $m_contract = new \Admin\Model\ContractModel();
        $fileds = "a.*,b.uname";
        
        $result = $m_contract->getAllList($fileds,$where, $orders);
        $m_contract_hotel = new \Admin\Model\ContracthotelModel();
        
        
        $m_signuser = new \Admin\Model\SignuserModel();
        $result_signuser = $m_signuser->getDataList('id,uname as name',array('status'=>1),'id asc');
        $sign_users = array();
        foreach ($result_signuser as $v){
            $sign_users[$v['id']] = $v;
        }
        $config_proxy_sale_contract = C('FINACE_CONTRACT');
        $contract_ctype_arr   = $config_proxy_sale_contract['contract_ctype']['proxysale'];
        
        $ctypes = [];
        
        foreach($contract_ctype_arr as $key=>$v){
            $ctypes[$v['id']] = $v;
        }
        
        foreach($result as $key=>$v){
            $nums = $m_contract_hotel->where(array('contract_id'=>$v['id']))->count();
            $result[$key]['contract_hotel_nums'] = $nums;
            $result[$key]['sign_user'] = $sign_users[$v['sign_user_id']]['name'];
            
            $status_str = '';
            if($v['status']==4){
                $status_str =  "已终止";
            }else{
                $s_time  = strtotime($v['contract_stime']." 00:00:00");
                $e_time  = strtotime($v['contract_etime']." 23:59:59");
                $now_date  = time();
                if($s_time>=$now_date){
                    $status_str =  '待生效';
                }else if($s_time<$now_date && $e_time>now_date){
                    $status_str =  '进行中';
                }else if($e_time<=$now_date){
                    $status_str =  '已到期';
                }
                
            }
            $result[$key]['status_str'] = $status_str;
            $result[$key]['ctype_str'] = $ctypes[$v['ctype']]['name'];
            $expire_time = '';
            if($v['contract_stime']!='0000-00-00' && $v['contract_etime']!='0000-00-00'){
                $expire_time = $v['contract_stime'].'~'.$v['contract_etime'];
            }
            $result[$key]['expire_time'] = $expire_time;
        }
        
        $cell = array(
            array('id','ID'),
            array('serial_number','合同编号'),
            array('name','合同名称'),
            array('sign_user','签约人'),
            array('ctype_str','合同类型'),
            array('archive_time','合同归档日期'),
            array('sign_time','签约时间'),
            array('expire_time','合同有效期'),
            array('status_str','合作状态'),
            array('contract_hotel_nums','关联酒楼'),
        );
        $filename = '商品代销合同列表';
        $this->exportToExcel($cell,$result,$filename,1);
        
    }
    public function purchase(){
        
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $area_id    = I('area_id',0,'intval');
        $ctype      = I('ctype',0,'intval');
        $status     = I('status',0,'intval');
        $sign_user_id = I('sign_user_id',0,'intval');
        $name       = I('name','','trim');
        $orders     = "a.id desc";
        $where = [];
        if($name){
            $map1['a.name']= array('like',"%".$name."%");
            $map2['a.purchased_item'] = array('like',"%".$name."%");
            $where['_complex'] = array(
                $map1,
                $map2,
                '_logic' => 'or'
            );
        }
        if($start_date && $end_date){
            $where['a.sign_time']= array(array('EGT',$start_date),array('ELT',$end_date));
            
        }else if(empty($start_date) && !empty($end_date)){
            $where['a.sign_time']= array( array('NEQ','0000-00-00'),array('ELT',$end_date));
        }
        
        if(!empty($start_date)&& empty($end_date)){
            $where['a.sign_time']= array('EGT',$start_date);
        }
        if($area_id){
            $where['a.area_id'] = $area_id;
        }
        if($ctype){
            $where['a.ctype'] = $ctype;
        }
        if($status){
            $now_date = date('Y-m-d');
            if($status==1){
                
                //$s_time>=$now_date
                $where['a.contract_stime'] = array('gt',$now_date);
                $where['a.status'] = array('neq',4);
                
            }else if($status ==2){
                $where['a.contract_stime'] = array('ELT',$now_date);
                $where['a.contract_etime']  = array('GT',$now_date);
                $where['a.status'] = array('neq',4);
            }else if($status==3){
                $where['a.contract_etime'] = array('ELT',$now_date);
                $where['a.status'] = array('neq',4);
            }else if($status==4){
                $where['a.status']=4;
            }
        }
        if($sign_user_id){
            $where['a.sign_user_id'] = $sign_user_id;
        }
        
        $where['a.type'] = 40;
        $m_contract = new \Admin\Model\ContractModel();
        $fileds = "a.*,b.uname";
        
        $result = $m_contract->getAllList($fileds,$where, $orders);
        
        
        
        
        $m_signuser = new \Admin\Model\SignuserModel();
        $result_signuser = $m_signuser->getDataList('id,uname as name',array('status'=>1),'id asc');
        $sign_users = array();
        foreach ($result_signuser as $v){
            $sign_users[$v['id']] = $v;
        }
        $config_proxy_sale_contract = C('FINACE_CONTRACT');
        $contract_ctype_arr   = $config_proxy_sale_contract['contract_ctype']['purchase'];
        
        $ctypes = [];
        
        foreach($contract_ctype_arr as $key=>$v){
            $ctypes[$v['id']] = $v;
        }
        foreach($result as $key=>$v){
            
            $result[$key]['sign_user'] = $sign_users[$v['sign_user_id']]['name'];
            
            
            $status_str = '';
            if($v['status']==4){
                $status_str =  "已终止";
            }else{
                $s_time  = strtotime($v['contract_stime']." 00:00:00");
                $e_time  = strtotime($v['contract_etime']." 23:59:59");
                $now_date  = time();
                if($s_time>=$now_date){
                    $status_str =  '待生效';
                }else if($s_time<$now_date && $e_time>now_date){
                    $status_str =  '进行中';
                }else if($e_time<=$now_date){
                    $status_str =  '已到期';
                }
                
            }
            $result[$key]['status_str'] = $status_str;
            $result[$key]['ctype_str'] = $ctypes[$v['ctype']]['name'];
            $expire_time = '';
            if($v['contract_stime']!='0000-00-00' && $v['contract_etime']!='0000-00-00'){
                $expire_time = $v['contract_stime'].'~'.$v['contract_etime'];
            }
            $result[$key]['expire_time'] = $expire_time;
            
        }
        $cell = array(
            array('id','ID'),
            array('serial_number','合同编号'),
            array('name','合同名称'),
            array('sign_user','签约人'),
            array('ctype_str','合同类型'),
            array('sign_time','签约时间'),
            array('expire_time','合同有效期'),
            array('status_str','合作状态'),
        );
        $filename = '采购合同列表';
        $this->exportToExcel($cell,$result,$filename,1);
    }
    public function adsale(){
        
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $area_id    = I('area_id',0,'intval');
        $ctype      = I('ctype',0,'intval');
        $status     = I('status',0,'intval');
        $sign_user_id = I('sign_user_id',0,'intval');
        $name       = I('name','','trim');
        $orders     = "a.id desc";
        
        $where = [];
        if($name){
            $map1['a.name']= array('like',"%".$name."%");
            $map2['a.purchased_item'] = array('like',"%".$name."%");
            $where['_complex'] = array(
                $map1,
                $map2,
                '_logic' => 'or'
            );
        }
        
        
        if($start_date){
            $where['a.sign_time']= array('EGT',$start_date);
            $this->assign('start_date',$start_date);
        }
        if($end_date){
            $where['a.sign_time']= array('ELT',$end_date);
        }
        if($area_id){
            $where['a.area_id'] = $area_id;
        }
        if($ctype){
            $where['a.ctype'] = $ctype;
        }
        if($status){
            $now_date = date('Y-m-d');
            if($status==1){
                
                //$s_time>=$now_date
                $where['a.contract_stime'] = array('gt',$now_date);
                $where['a.status'] = array('neq',4);
                
            }else if($status ==2){
                $where['a.contract_stime'] = array('ELT',$now_date);
                $where['a.contract_etime']  = array('GT',$now_date);
                $where['a.status'] = array('neq',4);
            }else if($status==3){
                $where['a.contract_etime'] = array('ELT',$now_date);
                $where['a.status'] = array('neq',4);
            }else if($status==4){
                $where['a.status']=4;
            }
        }
        if($sign_user_id){
            $where['a.sign_user_id'] = $sign_user_id;
        }
        
        $where['a.type'] = 30;
        $m_contract = new \Admin\Model\ContractModel();
        $fileds = "a.*,b.uname";
        
        $result = $m_contract->getAllList($fileds,$where, $orders);
        
        //print_r($this->contract_ctype_arr);exit;
        
        $m_signuser = new \Admin\Model\SignuserModel();
        $result_signuser = $m_signuser->getDataList('id,uname as name',array('status'=>1),'id asc');
        $sign_users = array();
        foreach ($result_signuser as $v){
            $sign_users[$v['id']] = $v;
        }
        $config_proxy_sale_contract = C('FINACE_CONTRACT');
        $contract_ctype_arr   = $config_proxy_sale_contract['contract_ctype']['adsale'];
        
        $ctypes = [];
        
        foreach($contract_ctype_arr as $key=>$v){
            $ctypes[$v['id']] = $v;
        }
        
        foreach($result as $key=>$v){
            
            
            $result[$key]['sign_user'] = $sign_users[$v['sign_user_id']]['name'];
            
            
            $status_str = '';
            if($v['status']==4){
                $status_str =  "已终止";
            }else{
                $s_time  = strtotime($v['contract_stime']." 00:00:00");
                $e_time  = strtotime($v['contract_etime']." 23:59:59");
                $now_date  = time();
                if($s_time>=$now_date){
                    $status_str =  '待生效';
                }else if($s_time<$now_date && $e_time>now_date){
                    $status_str =  '进行中';
                }else if($e_time<=$now_date){
                    $status_str =  '已到期';
                }
                
            }
            $result[$key]['status_str'] = $status_str;
            $result[$key]['ctype_str'] = $ctypes[$v['ctype']]['name'];
            $expire_time = '';
            if($v['contract_stime']!='0000-00-00' && $v['contract_etime']!='0000-00-00'){
                $expire_time = $v['contract_stime'].'~'.$v['contract_etime'];
            }
            $result[$key]['expire_time'] = $expire_time;
            
        }
        $cell = array(
            array('id','ID'),
            array('serial_number','合同编号'),
            array('name','合同名称'),
            array('sign_user','签约人'),
            array('ctype_str','合同类型'),
            array('sign_time','签约时间'),
            array('expire_time','合同有效期'),
            array('status_str','合作状态'),
        );
        $filename = '广告销售合同列表';
        $this->exportToExcel($cell,$result,$filename,1);
    }
    
}