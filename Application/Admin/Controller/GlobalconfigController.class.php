<?php
namespace Admin\Controller;

class GlobalconfigController extends BaseController {


    public function configdata(){
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $where = " config_key in('sale_ys_money','sale_cq_money','hotel_stock_num')";
        $volume_arr = $m_sys_config->getList($where);
        $info = array();
        foreach($volume_arr as $v){
            $info[$v['config_key']] = $v['config_value'];
        }
        $this->assign('info',$info);
        $this->display('configdata');
    }


    /**
     * @desc 修改设置
     */
    public function editconfig(){
        $sale_ys_money = I('post.sale_ys_money',0,'intval');
        $sale_cq_money = I('post.sale_cq_money',0,'intval');
        $hotel_stock_num = I('post.hotel_stock_num',0,'intval');

        $m_sys_config = new \Admin\Model\SysConfigModel();
        if($sale_ys_money){
            $data = array('config_value'=>$sale_ys_money);
            $m_sys_config->editData($data, 'sale_ys_money');
        }
        if($sale_cq_money){
            $data = array('config_value'=>$sale_cq_money);
            $m_sys_config->editData($data, 'sale_cq_money');
        }
        if($hotel_stock_num){
            $data = array('config_value'=>$hotel_stock_num);
            $m_sys_config->editData($data, 'hotel_stock_num');
        }

        $sys_list = $m_sys_config->getList(array('status'=>1));
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(12);
        $cache_key = C('SYSTEM_CONFIG');
        $redis->set($cache_key, json_encode($sys_list));
        $this->output('操作成功','globalconfig/configdata');
    }
}