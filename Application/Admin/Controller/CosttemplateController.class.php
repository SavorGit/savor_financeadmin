<?php
namespace Admin\Controller;

class CosttemplateController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function datalist() {
    	$type = I('type',0,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数

        $where = array();
        if($type){
            $where['type'] = $type;
        }
        $start  = ($page-1) * $size;
        $m_costtemplate  = new \Admin\Model\CosttemplateModel();
        $result = $m_costtemplate->getDataList('*',$where,'type asc',$start,$size);
        $datalist = array();
        $all_types = array(0=>'全部',1=>'开机费模板',2=>'续约条款模板');
        if(!empty($result)){
            $datalist = $result['list'];
            foreach ($datalist as $k=>$v){
                $datalist[$k]['typestr'] = $all_types[$v['type']];
                if($v['type']==1 && !empty($v['content'])){
                    $content = json_decode($v['content'],true);
                    $now_content = '';
                    foreach ($content as $cv){
                        $now_content.="开机率 {$cv['min']}-{$cv['max']}:{$cv['cost']}元/屏;";
                    }
                    $datalist[$k]['content'] = rtrim($now_content,';');
                }
                $is_standardstr = '否';
                if($v['is_standard']==1){
                    $is_standardstr = '是';
                }
                $datalist[$k]['is_standardstr'] = $is_standardstr;
            }
        }

        $this->assign('alltype',$all_types);
        $this->assign('type',$type);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('datalist');
    }
    
    public function addtemplate(){
        $id = I('id', 0, 'intval');
        $type = I('type',0,'intval');
        $m_costtemplate  = new \Admin\Model\CosttemplateModel();
        if(IS_GET){
        	$dinfo = array('type'=>$type);
        	$content = array();
        	if($id){
                $dinfo = $m_costtemplate->getInfo(array('id'=>$id));
                if($dinfo['type']==1){
                    $content = json_decode($dinfo['content'],true);
                }
        	}
        	$now_content = array();
            for($i=1;$i<=5;$i++){
                $key_num = $i-1;
                $min = $max = $cost = '';
                if(isset($content[$key_num])){
                    $min = $content[$key_num]['min'];
                    $max = $content[$key_num]['max'];
                    $cost = $content[$key_num]['cost'];
                }
                $now_content[] = array('name'=>'开机率'.$i,'min'=>$min,'max'=>$max,'cost'=>$cost);
            }
        	$this->assign('content',$now_content);
        	$this->assign('dinfo',$dinfo);
        	if($type==1){
        	    $html = 'addpaytemplate';
            }else{
        	    $html = 'addrenewtemplate';
            }
        	$this->display($html);
        }else{
        	$name = I('post.name','','trim');
        	$type = I('post.type',0,'intval');//类型1开机费模板,2续约条款模板
        	$content = I('post.content','');
        	$is_standard = I('post.is_standard',0,'intval');

        	if($type==1){
        	    $now_content = array();
        	    $mins = I('min');
        	    $maxs = I('max');
        	    $costs = I('cost');
                for($i=1;$i<=5;$i++){
                    $key_num = $i-1;
                    if($mins[$key_num]>=0 && $maxs[$key_num]>0 && $costs[$key_num]>=0){
                        $now_content[] = array('min'=>$mins[$key_num],'max'=>$maxs[$key_num],'cost'=>$costs[$key_num]);
                    }
                }
                if(!empty($now_content)){
                    $content = json_encode($now_content);
                }
            }
            if(empty($content)){
                $this->output('缺少必要参数!', 'costtemplate/addtemplate', 2, 0);
            }
        	$data = array('name'=>$name,'content'=>$content,'is_standard'=>$is_standard,'type'=>$type);
            if($id){
                $result = $m_costtemplate->updateData(array('id'=>$id), $data);
            }else{
                $result = $m_costtemplate->add($data);
            }
        	if($result){
        		$this->output('操作成功', 'costtemplate/datalist');
        	}else{
        		$this->output('操作失败', 'costtemplate/addtemplate',2,0);
        	}
        }
    }

}