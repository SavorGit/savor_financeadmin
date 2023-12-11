<?php
namespace Admin\Controller;

class StockpaymentController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        $where = array();
        if(empty($start_time) || empty($end_time)){
            $start_time = date('Y-m-d',strtotime('-1 month'));
            $end_time = date('Y-m-d');
        }
        $now_start_time = date('Y-m-d',strtotime($start_time));
        $now_end_time = date('Y-m-d',strtotime($end_time));
        $where['pay_time'] = array(array('egt',$now_start_time),array('elt',$now_end_time));

        $start = ($pageNum-1)*$size;
        $m_stock_payment = new \Admin\Model\StockPaymentModel();
        $res_list = $m_stock_payment->getDataList('*',$where,'id desc', $start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_paymentrecord = new \Admin\Model\StockPaymentRecordModel();
            $data_list = $res_list['list'];
            foreach ($data_list as $k=>$v){
                $res_linknum = $m_paymentrecord->getAllData('count(id) as num',array('stock_payment_id'=>$v['id']));
                $data_list[$k]['link_num'] = intval($res_linknum[0]['num']);
            }
        }
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function addpayment(){
        $id = I('id',0,'intval');
        $m_stock_payment = new \Admin\Model\StockPaymentModel();
        if(IS_POST){
            $tax_rate = I('post.tax_rate',0,'intval');
            $pay_money = I('post.pay_money',0,'intval');
            $pay_time = I('post.pay_time','','trim');
            $userInfo = session('sysUserInfo');
            $sysuser_id = $userInfo['id'];

            $data = array('tax_rate'=>$tax_rate,'pay_money'=>$pay_money,'pay_time'=>$pay_time,'sysuser_id'=>$sysuser_id);
            if(!empty($id)){
                $data['update_time'] = date('Y-m-d H:i:s');
                $m_stock_payment->updateData(array('id'=>$id),$data);
            }else{
                $m_stock_payment->add($data);
            }
            $this->output('操作成功', 'stockpayment/datalist');
        }else{
            $vinfo = array('tax_rate'=>13);
            if($id){
                $vinfo = $m_stock_payment->getInfo(array('id'=>$id));
            }
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function linkstockadd(){
        $stock_payment_id = I('stock_payment_id',0,'intval');
        $source = I('source',0,'intval');

        $m_paymentrecord = new \Admin\Model\StockPaymentRecordModel();
        $m_stock = new \Admin\Model\StockModel();
        if(IS_POST){
            $stock_ids = I('post.stock_ids','','trim');
            $remain_money = I('post.remain_money',0);
            if($remain_money==0){
                $this->output('已完成收款', 'stockpayment/linkstockadd',2,0);
            }
            if(!empty($stock_ids)){
                $stock_ids = explode(',',$stock_ids);
                $pay_money = $remain_money;
                $is_over = 0;
                foreach ($stock_ids as $v){
                    $stock_id = intval($v);
                    $res_stock = $m_stock->getInfo(array('id'=>$stock_id));
                    $total_money = $res_stock['total_money'];

                    $res_record = $m_paymentrecord->getAllData('*',array('stock_id'=>$stock_id,'stock_payment_id'=>$stock_payment_id));
                    if(!empty($res_record)){
                        $this->output("入库单单:{$stock_id}请勿重复收款", 'stockpayment/linkstockadd',2,0);
                    }
                    $res_stock_money = $m_paymentrecord->getAllData('sum(pay_money) as all_pay_money',array('stock_id'=>$stock_id));
                    $remian_money = $total_money-intval($res_stock_money[0]['all_pay_money']);
                    if($remian_money==0){
                        $this->output("入库单单:{$stock_id}已完成收款", 'stockpayment/linkstockadd',2,0);
                    }
                    $record_pay_money = 0;
                    $pay_status = 1;
                    if($pay_money>=0){
                        if($pay_money>=$remian_money){
                            $record_pay_money = $remian_money;
                            $pay_status = 2;
                        }else{
                            if($pay_money>0){
                                $pay_status = 3;
                                $record_pay_money = $pay_money;
                            }
                        }
                    }else{
                        $is_over=1;
                        $record_pay_money = abs($pay_money);
                        $pay_status = 3;
                    }
                    if($is_over==1){
                        $this->output('所选出库单数大于可分配的收款金额', 'salepayment/linksaleadd', 2, 0);
                    }
                    $pay_money = $pay_money-$remian_money;
                    $pay_record[]=array('stock_id'=>$stock_id,'pay_money'=>$record_pay_money,'pay_status'=>$pay_status,'re_pay_money'=>$pay_money,'is_over'=>$is_over);
                }
                if(!empty($pay_record)){
                    foreach ($pay_record as $v){
                        if($v['pay_money']>0){
                            $stock_id = $v['stock_id'];
                            $res_stock = $m_stock->getInfo(array('id'=>$stock_id));
                            if(!empty($res_stock['stock_payment_ids'])){
                                $stock_payment_ids = $res_stock['stock_payment_ids']."$stock_payment_id,";
                            }else{
                                $stock_payment_ids = ",$stock_payment_id,";
                            }
                            $now_pay_money = $res_stock['pay_money']+$v['pay_money'];
                            $updata = array('pay_status'=>$v['pay_status'],'pay_money'=>$now_pay_money,'stock_payment_ids'=>$stock_payment_ids);
                            $m_stock->updateData(array('id'=>$v['stock_id']),$updata);

                            $m_paymentrecord->add(array('stock_id'=>$v['stock_id'],'stock_payment_id'=>$stock_payment_id,'pay_money'=>$v['pay_money']));
                        }
                    }
                }
            }
            if($source==1){
                $jump_url = 'stockpayment/datalist';
            }else{
                $jump_url = 'stockpayment/linkstocklist';
            }
            $this->output('操作成功', $jump_url);
        }else{
            $m_payment = new \Admin\Model\StockPaymentModel();
            $res_payment = $m_payment->getInfo(array('id'=>$stock_payment_id));

            $res_money = $m_paymentrecord->getAllData('sum(pay_money) as all_pay_money',array('stock_payment_id'=>$stock_payment_id));
            $remain_money = $res_payment['pay_money']-intval($res_money[0]['all_pay_money']);

            $where = array('type'=>10,'io_type'=>11,'status'=>array('gt',1),'pay_status'=>array('in','1,3'));
            $where['io_date'] = array('egt','2023-11-09');
            $where['stock_payment_ids'] = array('notlike',"%,$stock_payment_id,%");
            $stock_list = $m_stock->getDataList('id,name,serial_number,pay_status,stock_payment_ids,total_money-pay_money as money,department_user_id',$where,'id desc');
            $all_stock = array();
            $m_duser = new \Admin\Model\DepartmentUserModel();
            foreach ($stock_list as $k=>$v){
                $is_select = '';
                $v['is_select'] = $is_select;
                $pay_money = $v['money'];
                $pay_status_str = '';
                if($v['pay_status']==3){
                    $pay_status_str = '【部分收款】';
                }
                $res_user = $m_duser->getInfo(array('id'=>$v['department_user_id']));

                $v['name'] = "{$v['id']}--{$v['add_time']}--{$v['name']}--{$pay_money}--{$res_user['name']}{$pay_status_str}";
                $all_stock[]=$v;
            }

            $this->assign('stock_list',$all_stock);
            $this->assign('stock_payment_id',$stock_payment_id);
            $this->assign('remain_money',$remain_money);
            $this->assign('source',$source);
            $this->display();
        }
    }

    public function linkstocklist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $stock_payment_id = I('stock_payment_id',0,'intval');
        $start = ($page-1)*$size;

        $m_paymentrecord = new \Admin\Model\StockPaymentRecordModel();
        $fields = 'a.*,stock.id as stock_id,stock.total_money,stock.pay_money,stock.serial_number';
        $result = $m_paymentrecord->getList($fields,array('a.stock_payment_id'=>$stock_payment_id),'a.id desc',$start,$size);
        $datalist = $result['list'];

        $this->assign('stock_payment_id', $stock_payment_id);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function linkstockdel(){
        $id = I('get.id',0,'intval');
        $m_paymentrecord = new \Admin\Model\StockPaymentRecordModel();
        $res_info = $m_paymentrecord->getInfo(array('id'=>$id));
        $m_paymentrecord->delData(array('id'=>$id));
        $stock_payment_id = $res_info['stock_payment_id'];

        $m_stock = new \Admin\Model\StockModel();
        $res_stock = $m_stock->getInfo(array('id'=>$res_info['stock_id']));
        $now_pay_money = sprintf("%.2f",$res_stock['pay_money']-$res_info['pay_money']);
        $pay_status = 1;
        if($now_pay_money>0){
            $pay_status = 3;
        }
        $all_stock_payment_ids = explode(',',trim($res_stock['stock_payment_ids'],','));
        $now_key = array_search($stock_payment_id,$all_stock_payment_ids);
        $updata = array('pay_status'=>$pay_status,'pay_money'=>$now_pay_money);
        if($now_key!==false){
            unset($all_stock_payment_ids[$now_key]);
            $nowstock_payment_ids = join(',',$all_stock_payment_ids);
            $updata['stock_payment_ids'] = $nowstock_payment_ids.',';
        }
        $m_stock->updateData(array('id'=>$res_info['stock_id']),$updata);

        $this->output('操作成功!', 'stockpayment/linkstocklist',2);
    }

}