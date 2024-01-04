<?php
namespace Admin\Controller;
class StockopenrewardController extends BaseController {

    public function datalist(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        if(empty($start_date) || empty($end_date)){
            $start_date = date('Y-m-d',strtotime("-14 day"));
            $end_date = date('Y-m-d');
        }
        $open_date = '2024-01-03';
        if($start_date<$open_date){
            $start_date = $open_date;
        }
        $stime = strtotime($start_date);
        $etime = strtotime($end_date);
        if($etime-$stime>15*86400){
            $this->output('请选择15天内的时间段', 'stockopenreward/datalist',2,0);
        }

        $all_recycle_status = C('STOCK_RECYLE_STATUS');
        $where = array('a.type'=>7,'a.wo_status'=>2,'a.wo_reason_type'=>1,'a.recycle_status'=>array('in','1,2,3,5,6'));
        $now_start_time = date('Y-m-d 00:00:00',$stime);
        $now_end_time = date('Y-m-d 23:59:59',$etime);
        $where['a.add_time'] = array(array('egt',$now_start_time),array('elt',$now_end_time));

        $start = ($pageNum-1)*$size;
        $fields = 'a.id,a.idcode,a.vintner_code,a.out_time,a.recycle_img,a.recycle_status,a.reason,a.add_time,
        hotel.id as hotel_id,hotel.name as hotel_name,su.remark as residenter_name,user.nickName as username,user.mobile';
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_list = $m_stock_record->getRecordSaleList($fields,$where, 'a.id desc', $start,$size);
        $data_list = $res_list['list'];
        $oss_host = get_oss_host();
        foreach ($data_list as $k=>$v){
            if(!empty($v['recycle_img'])){
                $data_list[$k]['recycle_img'] = $oss_host.$v['recycle_img'];
            }
            $data_list[$k]['recycle_status_str'] = $all_recycle_status[$v['recycle_status']];
            if($v['recycle_status']==3){
                $data_list[$k]['reason'] = '未上传开瓶资料';
            }
        }

        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function dataimport(){
        $cache_key = 'cronscript:finance:openrewardexcel';
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $res_data = $redis->get($cache_key);
        if(!empty($res_data)){
            $now_time = time();
            $diff_time = $now_time - $res_data;
            $errMsg = "你上传的文件正在处理中，处理时间{$diff_time}秒，请稍后。";
            $this->output($errMsg, 'stockopenreward/datalist', 3,0);
        }
        if(IS_POST){
            $upload = new \Think\Upload();
            $upload->exts = array('xls','xlsx','csv');
            $upload->maxSize = 2097152;
            $upload->rootPath = $this->imgup_path();
            $upload->savePath = '';
            $upload->saveName = time().mt_rand();
            $info = $upload->upload();
            if(!$info){
                $errMsg = $upload->getError();
                $this->output($errMsg, 'stockopenreward/dataimport', 2,0);
            }else{
                $userinfo = session('sysUserInfo');
                $sysuser_id = $userinfo['id'];
                $file_name = urlencode($info['fileup']['savepath'].$info['fileup']['savename']);
                $shell = "/opt/install/php/bin/php /application_data/web/php/savor_financeadmin/cli.php dataexport/stockopenreward/dataimportscript/fname/$file_name/auid/$sysuser_id > /tmp/null &";
                system($shell);
                $now_time = time();
                $redis->set($cache_key,$now_time,3600);
                $this->output('导入成功,开始处理审核数据', 'stockopenreward/datalist');
            }
        }else{
            $this->display();
        }

    }
}