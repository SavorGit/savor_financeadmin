<?php
namespace Admin\Controller;

class StockinvoiceController extends BaseController {

    private $invoice_type_arr = array();

    public function __construct() {
        parent::__construct();
        $config_contract = C('FINACE_CONTRACT');
        $this->invoice_type_arr = $config_contract['invoice_type'];
    }

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
        $where['invoice_time'] = array(array('egt',$now_start_time),array('elt',$now_end_time));

        $start = ($pageNum-1)*$size;
        $m_stock_invoice = new \Admin\Model\StockInvoiceModel();
        $res_list = $m_stock_invoice->getDataList('*',$where,'id desc', $start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_invoicerecord = new \Admin\Model\StockInvoiceRecordModel();
            $data_list = $res_list['list'];
            foreach ($data_list as $k=>$v){
                $invoice_type_str = '';
                if(isset($this->invoice_type_arr[$v['invoice_type']])){
                    $invoice_type_str = $this->invoice_type_arr[$v['invoice_type']]['name'];
                }
                $data_list[$k]['invoice_type_str'] = $invoice_type_str;
                $res_linknum = $m_invoicerecord->getAllData('count(id) as num',array('stock_invoice_id'=>$v['id']));
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

    public function addinvoice(){
        $id = I('id',0,'intval');
        $m_invoice = new \Admin\Model\StockInvoiceModel();
        if(IS_POST){
            $serial_number = I('post.serial_number','','trim');
            $invoice_type = I('post.invoice_type',0,'intval');//发票类型1专票,2普票
            $invoice_money = I('post.invoice_money',0,'intval');
            $tax_rate = I('post.tax_rate',0,'intval');
            $invoice_time = I('post.invoice_time','','trim');
            $purchase_num = I('post.purchase_num',0,'intval');
            $purchase_unit_price = I('post.purchase_unit_price',0,'intval');
            $userInfo = session('sysUserInfo');
            $sysuser_id = $userInfo['id'];

            $tax_money = $invoice_money*($tax_rate/100);
            $data = array('serial_number'=>$serial_number,'invoice_type'=>$invoice_type,'invoice_money'=>$invoice_money,
                'tax_rate'=>$tax_rate,'invoice_time'=>$invoice_time,'purchase_num'=>$purchase_num,'tax_money'=>$tax_money,
                'purchase_unit_price'=>$purchase_unit_price,'sysuser_id'=>$sysuser_id);
            if(!empty($id)){
                $data['update_time'] = date('Y-m-d H:i:s');
                $m_invoice->updateData(array('id'=>$id),$data);
            }else{
                $m_invoice->add($data);
            }
            $this->output('操作成功', 'stockinvoice/datalist');
        }else{
            $invoice_type_arr = $this->invoice_type_arr;
            $vinfo = array('tax_rate'=>13,'invoice_type'=>1);
            if($id){
                $vinfo = $m_invoice->getInfo(array('id'=>$id));
            }
            $this->assign('invoice_type_arr',$invoice_type_arr);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function linkstockadd(){
        $stock_invoice_id = I('stock_invoice_id',0,'intval');
        $source = I('source',0,'intval');
        $m_invoice = new \Admin\Model\StockInvoiceModel();
        $res_invoice = $m_invoice->getInfo(array('id'=>$stock_invoice_id));
        $m_invoicerecord = new \Admin\Model\StockInvoiceRecordModel();
        $m_stock = new \Admin\Model\StockModel();
        if(IS_POST){
            $stock_ids = I('post.stock_ids','','trim');
            $remain_money = I('post.remain_money',0);
            if($remain_money==0){
                $this->output('已完成收款', 'stockinvoice/linkstockadd',2,0);
            }
            if(!empty($stock_ids)){
                $stock_ids = explode(',',$stock_ids);
                $pay_money = $remain_money;
                $is_over = 0;
                foreach ($stock_ids as $v){
                    $stock_id = intval($v);
                    $res_stock = $m_stock->getInfo(array('id'=>$stock_id));
                    $total_money = $res_stock['total_money'];

                    $res_record = $m_invoicerecord->getAllData('*',array('stock_id'=>$stock_id,'stock_invoice_id'=>$stock_invoice_id));
                    if(!empty($res_record)){
                        $this->output("入库单单:{$stock_id}请勿重复收款", 'stockinvoice/linkstockadd',2,0);
                    }
                    $res_stock_money = $m_invoicerecord->getAllData('sum(invoice_money) as all_invoice_money',array('stock_id'=>$stock_id));
                    $remian_money = $total_money-intval($res_stock_money[0]['all_invoice_money']);
                    if($remian_money==0){
                        $this->output("入库单单:{$stock_id}已完成收款", 'stockinvoice/linkstockadd',2,0);
                    }
                    $record_pay_money = 0;
                    $invoice_status = 1;
                    if($pay_money>=0){
                        if($pay_money>=$remian_money){
                            $record_pay_money = $remian_money;
                            $invoice_status = 2;
                        }else{
                            if($pay_money>0){
                                $invoice_status = 3;
                                $record_pay_money = $pay_money;
                            }
                        }
                    }else{
                        $is_over=1;
                        $record_pay_money = abs($pay_money);
                        $invoice_status = 3;
                    }
                    if($is_over==1){
                        $this->output('所选出库单数大于可分配的收款金额', 'stockinvoice/linksaleadd', 2, 0);
                    }
                    $pay_money = $pay_money-$remian_money;
                    $pay_record[]=array('stock_id'=>$stock_id,'invoice_money'=>$record_pay_money,'invoice_status'=>$invoice_status,'re_pay_money'=>$pay_money,'is_over'=>$is_over);
                }
                if(!empty($pay_record)){
                    foreach ($pay_record as $v){
                        if($v['invoice_money']>0){
                            $stock_id = $v['stock_id'];
                            $res_stock = $m_stock->getInfo(array('id'=>$stock_id));
                            if(!empty($res_stock['stock_invoice_ids'])){
                                $stock_invoice_ids = $res_stock['stock_invoice_ids']."$stock_invoice_id,";
                            }else{
                                $stock_invoice_ids = ",$stock_invoice_id,";
                            }
                            $now_invoice_money = $res_stock['invoice_money']+$v['invoice_money'];
                            $updata = array('is_invoice'=>1,'invoice_status'=>$v['invoice_status'],'invoice_money'=>$now_invoice_money,
                                'stock_invoice_ids'=>$stock_invoice_ids,'invoice_time'=>$res_invoice['invoice_time']);
                            $m_stock->updateData(array('id'=>$v['stock_id']),$updata);

                            $m_invoicerecord->add(array('stock_id'=>$v['stock_id'],'stock_invoice_id'=>$stock_invoice_id,'invoice_money'=>$v['invoice_money']));
                        }
                    }
                }
            }
            if($source==1){
                $jump_url = 'stockinvoice/datalist';
            }else{
                $jump_url = 'stockinvoice/linkstocklist';
            }
            $this->output('操作成功', $jump_url);
        }else{
            $res_money = $m_invoicerecord->getAllData('sum(invoice_money) as all_invoice_money',array('stock_invoice_id'=>$stock_invoice_id));
            $remain_money = $res_invoice['invoice_money']-intval($res_money[0]['all_invoice_money']);

            $where = array('type'=>10,'io_type'=>11,'status'=>array('gt',1),'invoice_status'=>array('in','1,3'));
            $where['io_date'] = array('egt','2023-11-09');
            $where['stock_invoice_ids'] = array('notlike',"%,$stock_invoice_id,%");
            $stock_list = $m_stock->getDataList('id,name,serial_number,pay_status,stock_invoice_ids,total_money-invoice_money as money,department_user_id,add_time',$where,'id desc');
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
            $this->assign('stock_invoice_id',$stock_invoice_id);
            $this->assign('remain_money',$remain_money);
            $this->assign('source',$source);
            $this->display();
        }
    }

    public function linkstocklist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $stock_invoice_id = I('stock_invoice_id',0,'intval');
        $start = ($page-1)*$size;

        $m_invoicerecord = new \Admin\Model\StockInvoiceRecordModel();
        $fields = 'a.*,stock.id as stock_id,stock.total_money,stock.invoice_money,stock.serial_number';
        $result = $m_invoicerecord->getList($fields,array('a.stock_invoice_id'=>$stock_invoice_id),'a.id desc',$start,$size);
        $datalist = $result['list'];

        $this->assign('stock_invoice_id', $stock_invoice_id);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function linkstockdel(){
        $id = I('get.id',0,'intval');
        $m_invoicerecord = new \Admin\Model\StockInvoiceRecordModel();
        $res_info = $m_invoicerecord->getInfo(array('id'=>$id));
        $m_invoicerecord->delData(array('id'=>$id));
        $stock_invoice_id = $res_info['stock_invoice_id'];

        $m_stock = new \Admin\Model\StockModel();
        $res_stock = $m_stock->getInfo(array('id'=>$res_info['stock_id']));
        $now_invoice_money = sprintf("%.2f",$res_stock['invoice_money']-$res_info['invoice_money']);
        $invoice_status = 1;
        if($now_invoice_money>0){
            $invoice_status = 3;
        }
        $all_stock_invoice_ids = explode(',',trim($res_stock['stock_invoice_ids'],','));
        $now_key = array_search($stock_invoice_id,$all_stock_invoice_ids);
        $updata = array('invoice_status'=>$invoice_status,'invoice_money'=>$now_invoice_money);
        if($now_key!==false){
            unset($all_stock_invoice_ids[$now_key]);
            if(empty($all_stock_invoice_ids)){
                $nowstock_invoice_ids = '';
                $updata['is_invoice'] = 0;
                $updata['invoice_time'] = '0000-00-00';
            }else{
                $nowstock_invoice_ids = join(',',$all_stock_invoice_ids);
                $nowstock_invoice_ids = $nowstock_invoice_ids.',';
            }
            $updata['stock_invoice_ids'] = $nowstock_invoice_ids;
        }
        $m_stock->updateData(array('id'=>$res_info['stock_id']),$updata);

        $this->output('操作成功!', 'stockinvoice/linkstocklist',2);
    }

}