<?php
namespace Admin\Controller;
use Think\Controller;
/**
 * @desc 用友接口
 *
 */
class U8cloudController extends Controller {

    private $voucher_params = array(
        'pk_corp'=>'02',
        'pk_glorgbook'=>'02-0001',
        'pk_currtype'=>'CNY',
        'rate'=>1.13,
        'pk_prepared'=>'13716111670',

    );

    public function __construct() {
        parent::__construct();
    }

    public function instockvoucher1(){
        $stock_id = I('get.stock_id',0,'intval');
        $userinfo = session('sysUserInfo');
        if(empty($userinfo['telephone'])){
            $this->output('请使用用友账号进行同步','stock/inlist',2,0);
        }
        $pk_prepared = $userinfo['telephone'];
        $m_stock = new \Admin\Model\StockModel();
        $fields = 'a.*,s.id as gysj_id,s.name as sname,s.short_name as sshort_name';
        $res_stock = $m_stock->getStockInfo($fields,array('a.id'=>$stock_id));
        if($res_stock['io_date']<'2024-01-01'){
            $this->output('请选择采购入库时间大于2024年','stock/inlist',2,0);
        }
        if($res_stock['io_type']!=11){
            $this->output('请选择入库类型为采购入库','stock/inlist',2,0);
        }
        if($res_stock['status']<2){
            $this->output('请先操作完成入库动作','stock/inlist',2,0);
        }
        if($res_stock['push_u8_status1']==1){
            $this->output('采购入库单凭证已完成','stock/inlist',2,0);
        }
        $supplier_name = $res_stock['sname'];
        if(!empty($res_stock['sshort_name'])){
            $supplier_name = $res_stock['sshort_name'];
        }

        $fileds = 'a.goods_id,sum(a.total_amount) as total_num,sum(a.total_fee) as total_fee,goods.name as goods_name,goods.u8_pk_accsubj';
        $where = array('a.stock_id'=>$stock_id,'a.type'=>1);
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_record = $m_stock_record->getStockRecordList($fileds,$where,'','','a.goods_id');
        $voucher = array();
        foreach ($res_record as $v){
            $total_fee = $v['total_fee'];
            $rate_money = round($total_fee/$this->voucher_params['rate'],2);
            $now_money = $total_fee-$rate_money;

            $explanation = $supplier_name.'-'.$v['goods_name'].'-'.$v['total_num'].'瓶';
            $goods_pk_accsubj = $v['u8_pk_accsubj'];
            $pk_currtype = $this->voucher_params['pk_currtype'];
            $freevalue1 = $res_stock['serial_number'];
            $voucher[]=array(
                'details'=>array(
                    array('explanation'=>$explanation,'pk_accsubj'=>$goods_pk_accsubj,'pk_currtype'=>$pk_currtype,'debitamount'=>$now_money,'creditamount'=>0,'freevalue1'=>$freevalue1),
                    array('explanation'=>$explanation,'pk_accsubj'=>'222103','pk_currtype'=>$pk_currtype,'debitamount'=>$rate_money,'creditamount'=>0,'freevalue1'=>$freevalue1),
                    array('explanation'=>$explanation,'pk_accsubj'=>'22020203','pk_currtype'=>$pk_currtype,'debitamount'=>0,'creditamount'=>$total_fee,'freevalue1'=>$freevalue1,
                        'ass'=>array(array('checktypecode'=>'73','checkvaluecode'=>"GYSJ{$res_stock['gysj_id']}"))
                    ),
                ),
                'pk_corp'=>$this->voucher_params['pk_corp'],
                'pk_glorgbook'=>$this->voucher_params['pk_glorgbook'],
                'pk_prepared'=>$pk_prepared,
                'pk_vouchertype'=>'采购入库',
                'prepareddate'=>$res_stock['io_date']
            );
        }

        $params = array(
            'voucher'=>$voucher
        );
        $u8 = new \Common\Lib\U8cloud();
        $resp_apidata = $u8->addVoucher($params);

        $res_data = json_decode($resp_apidata['result'],true);
        $res_u8data = json_decode($res_data['data'],true);
        if(empty($res_u8data[0]['pk_voucher'])){
            $this->output('调用凭证接口出错','stock/inlist',2,0);
        }
        $goods_voucher = array();
        foreach ($res_u8data as $v){
            $pk_voucher = $v['pk_voucher'];
            $goods_pk_accsubj = $v['detail'][0]['accsubj_code'];
            $goods_voucher[$goods_pk_accsubj]=$pk_voucher;
        }
        $m_stock->updateData(array('id'=>$stock_id),array('push_u8_status1'=>1,'push_u8_time1'=>date('Y-m-d H:i:s')));

        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $res_detail = $m_stock_detail->getList('a.id as stock_detail_id,goods.u8_pk_accsubj',array('a.stock_id'=>$stock_id,'a.status'=>1), 'a.id desc',0,0);
        $push_data = array();
        foreach ($res_detail as $v){
            if(isset($goods_voucher[$v['u8_pk_accsubj']])){
                $push_data[]=array('stock_id'=>$stock_id,'stock_detail_id'=>$v['stock_detail_id'],'type'=>11,
                    'u8_pk_id'=>$goods_voucher[$v['u8_pk_accsubj']],'status'=>1);
            }
        }
        $m_pushu8 = new \Admin\Model\Pushu8RecordModel();
        $m_pushu8->addAll($push_data);
        $this->output('同步采购入库单用友凭证成功','stock/inlist',3);
    }

    pubLic function instockvoucher2(){
        $stock_id = I('get.stock_id',0,'intval');
        $userinfo = session('sysUserInfo');
        if(empty($userinfo['telephone'])){
            $this->output('请使用用友账号进行同步','stock/inlist',2,0);
        }
        $pk_prepared = $userinfo['telephone'];
        $m_stock = new \Admin\Model\StockModel();
        $fields = 'a.*,s.id as gysj_id,s.name as sname,s.short_name as sshort_name';
        $res_stock = $m_stock->getStockInfo($fields,array('a.id'=>$stock_id));
        if($res_stock['io_date']<'2024-01-01'){
            $this->output('请选择入库类型为采购入库','stock/inlist',2,0);
        }
        if($res_stock['io_type']!=11){
            $this->output('请选择入库类型为采购入库','stock/inlist',2,0);
        }
        if($res_stock['invoice_status']!=2){
            $this->output('请先完成收到发票','stock/inlist',2,0);
        }
        if($res_stock['push_u8_status2']==1){
            $this->output('收到发票凭证已完成','stock/inlist',2,0);
        }
        $supplier_name = $res_stock['sname'];
        if(!empty($res_stock['sshort_name'])){
            $supplier_name = $res_stock['sshort_name'];
        }

        $fileds = 'a.goods_id,sum(a.total_amount) as total_num,sum(a.total_fee) as total_fee,goods.name as goods_name';
        $where = array('a.stock_id'=>$stock_id,'a.type'=>1);
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_record = $m_stock_record->getStockRecordList($fileds,$where,'','','a.goods_id');
        $voucher = array();
        foreach ($res_record as $v){
            $total_fee = $v['total_fee'];

            $explanation = '收到-'.$supplier_name.'-'.$v['goods_name'].'-'.$v['total_num'].'瓶-发票';
            $pk_currtype = $this->voucher_params['pk_currtype'];
            $freevalue1 = $res_stock['serial_number'];
            $voucher[]=array(
                'details'=>array(
                    array('explanation'=>$explanation,'pk_accsubj'=>'22020203','pk_currtype'=>$pk_currtype,'debitamount'=>$total_fee,'creditamount'=>0,'freevalue1'=>$freevalue1,
                        'ass'=>array(array('checktypecode'=>'73','checkvaluecode'=>"GYSJ{$res_stock['gysj_id']}"))
                    ),
                    array('explanation'=>$explanation,'pk_accsubj'=>'22020201','pk_currtype'=>$pk_currtype,'debitamount'=>0,'creditamount'=>$total_fee,'freevalue1'=>$freevalue1,
                        'ass'=>array(
                            array('checktypecode'=>'73','checkvaluecode'=>"GYSJ{$res_stock['gysj_id']}"),
                            array('checktypecode'=>'2','checkvaluecode'=>"{$res_stock['department_id']}"),
                        )
                    ),
                ),
                'pk_corp'=>$this->voucher_params['pk_corp'],
                'pk_glorgbook'=>$this->voucher_params['pk_glorgbook'],
                'pk_prepared'=>$pk_prepared,
                'pk_vouchertype'=>'采购发票',
                'prepareddate'=>$res_stock['invoice_time']
            );
        }

        $params = array(
            'voucher'=>$voucher
        );
        $u8 = new \Common\Lib\U8cloud();
        $resp_apidata = $u8->addVoucher($params);

        $res_data = json_decode($resp_apidata['result'],true);
        $res_u8data = json_decode($res_data['data'],true);
        if(empty($res_u8data[0]['pk_voucher'])){
            $this->output('调用凭证接口出错','stock/inlist',2,0);
        }
        $m_stock->updateData(array('id'=>$stock_id),array('push_u8_status2'=>1,'push_u8_time2'=>date('Y-m-d H:i:s')));

        $push_data = array();
        foreach ($res_u8data as $v){
            $pk_voucher = $v['pk_voucher'];
            $push_data[]=array('stock_id'=>$stock_id,'type'=>12,'u8_pk_id'=>$pk_voucher,'status'=>1);
        }

        $m_pushu8 = new \Admin\Model\Pushu8RecordModel();
        $m_pushu8->addAll($push_data);
        $this->output('同步收到发票用友凭证成功','stock/inlist',3);
    }

    pubLic function instockvoucher3(){
        $stock_id = I('get.stock_id',0,'intval');
        $userinfo = session('sysUserInfo');
        if(empty($userinfo['telephone'])){
            $this->output('请使用用友账号进行同步','stock/inlist',2,0);
        }
        $pk_prepared = $userinfo['telephone'];
        $m_stock = new \Admin\Model\StockModel();
        $fields = 'a.*,s.id as gysj_id,s.name as sname,s.short_name as sshort_name';
        $res_stock = $m_stock->getStockInfo($fields,array('a.id'=>$stock_id));
        if($res_stock['io_date']<'2024-01-01'){
            $this->output('请选择入库类型为采购入库','stock/inlist',2,0);
        }
        if($res_stock['io_type']!=11){
            $this->output('请选择入库类型为采购入库','stock/inlist',2,0);
        }
        if($res_stock['pay_status']!=2){
            $this->output('请先完成支付账款','stock/inlist',2,0);
        }
        if($res_stock['push_u8_status3']==1){
            $this->output('支付账款凭证已完成','stock/inlist',2,0);
        }
        $supplier_name = $res_stock['sname'];
        if(!empty($res_stock['sshort_name'])){
            $supplier_name = $res_stock['sshort_name'];
        }

        $fileds = 'a.goods_id,sum(a.total_amount) as total_num,sum(a.total_fee) as total_fee,goods.name as goods_name';
        $where = array('a.stock_id'=>$stock_id,'a.type'=>1);
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_record = $m_stock_record->getStockRecordList($fileds,$where,'','','a.goods_id');
        $voucher = array();
        foreach ($res_record as $v){
            $total_fee = $v['total_fee'];

            $explanation = '付-'.$supplier_name.'-'.$v['goods_name'].'-'.$v['total_num'].'瓶-款';
            $pk_currtype = $this->voucher_params['pk_currtype'];
            $freevalue1 = $res_stock['serial_number'];
            $voucher[]=array(
                'details'=>array(
                    array('explanation'=>$explanation,'pk_accsubj'=>'22020201','pk_currtype'=>$pk_currtype,'debitamount'=>$total_fee,'creditamount'=>0,'freevalue1'=>$freevalue1,
                        'ass'=>array(
                            array('checktypecode'=>'73','checkvaluecode'=>"GYSJ{$res_stock['gysj_id']}"),
                            array('checktypecode'=>'2','checkvaluecode'=>"{$res_stock['department_id']}"),
                        ),
                        'cashflow'=>array(
                            array('money'=>$total_fee,'pk_cashflow'=>'1121','pk_currtype'=>$pk_currtype)
                        )
                    ),
                    array('explanation'=>$explanation,'pk_accsubj'=>'100201','pk_currtype'=>$pk_currtype,'debitamount'=>0,'creditamount'=>$total_fee,'freevalue1'=>$freevalue1,
                    ),
                ),
                'pk_corp'=>$this->voucher_params['pk_corp'],
                'pk_glorgbook'=>$this->voucher_params['pk_glorgbook'],
                'pk_prepared'=>$pk_prepared,
                'pk_vouchertype'=>'采购付款',
                'prepareddate'=>$res_stock['invoice_time']
            );
        }

        $params = array(
            'voucher'=>$voucher
        );
        $u8 = new \Common\Lib\U8cloud();
        $resp_apidata = $u8->addVoucher($params);

        $res_data = json_decode($resp_apidata['result'],true);
        $res_u8data = json_decode($res_data['data'],true);
        if(empty($res_u8data[0]['pk_voucher'])){
            $this->output('调用凭证接口出错','stock/inlist',2,0);
        }
        $m_stock->updateData(array('id'=>$stock_id),array('push_u8_status3'=>1,'push_u8_time3'=>date('Y-m-d H:i:s')));

        $push_data = array();
        foreach ($res_u8data as $v){
            $pk_voucher = $v['pk_voucher'];
            $push_data[]=array('stock_id'=>$stock_id,'type'=>13,'u8_pk_id'=>$pk_voucher,'status'=>1);
        }

        $m_pushu8 = new \Admin\Model\Pushu8RecordModel();
        $m_pushu8->addAll($push_data);
        $this->output('同步支付账款用友凭证成功','stock/inlist',3);
    }




    public function voucherparams(){
        $explanation='北京福运顺商贸有限公司-汾酒 53度青花30复兴版 500ml-1瓶';
        $explanation1='北京福运顺商贸有限公司-汾酒 53度青花30复兴版 500ml-2瓶';
        $params = array(
            'voucher'=>array(
                array(
                    'details'=>array(
                        array('explanation'=>$explanation,'pk_accsubj'=>'14050602','pk_currtype'=>'CNY','debitamount'=>88,'creditamount'=>0,'freevalue1'=>'BJRK20231204001'),
                        array('explanation'=>$explanation,'pk_accsubj'=>'222103','pk_currtype'=>'CNY','debitamount'=>12,'creditamount'=>0,'freevalue1'=>'BJRK20231204001'),
                        array('explanation'=>$explanation,'pk_accsubj'=>'22020203','pk_currtype'=>'CNY','debitamount'=>0,'creditamount'=>100,'freevalue1'=>'BJRK20231204001',
                            'ass'=>array(array('checktypecode'=>'73','checkvaluecode'=>'GYSJ10'))
                            ),
                    ),
                    'pk_corp'=>'02',
                    'pk_glorgbook'=>'02-0001',
                    'pk_prepared'=>'13716111670',
                    'pk_vouchertype'=>'银行',
                    'prepareddate'=>'2024-01-01'
                ),
                array(
                    'details'=>array(
                        array('explanation'=>$explanation1,'pk_accsubj'=>'14050602','pk_currtype'=>'CNY','debitamount'=>88,'creditamount'=>0,'freevalue1'=>'BJRK20231204001'),
                        array('explanation'=>$explanation1,'pk_accsubj'=>'222103','pk_currtype'=>'CNY','debitamount'=>12,'creditamount'=>0,'freevalue1'=>'BJRK20231204001'),
                        array('explanation'=>$explanation1,'pk_accsubj'=>'22020203','pk_currtype'=>'CNY','debitamount'=>0,'creditamount'=>100,'freevalue1'=>'BJRK20231204001',
                            'ass'=>array(array('checktypecode'=>'73','checkvaluecode'=>'GYSJ10'))
                        ),
                    ),
                    'pk_corp'=>'02',
                    'pk_glorgbook'=>'02-0001',
                    'pk_prepared'=>'13716111670',
                    'pk_vouchertype'=>'银行',
                    'prepareddate'=>'2024-01-01'
                )
            )
        );
        echo json_encode($params);

    }


    public function output($message,$navTab,$type=1,$status=1,$callback="",$del){
        switch ($type){
            case 1://关闭
                $callbackType = 'closeCurrent';
                break;
            case 2://重新载入
                $callbackType = 'forward';
                break;
            default://停留在当前页
                $callbackType = '';
                break;
        }
        $data = array('status'=>$status,'info'=>$message,'navTabId'=>$navTab,'url'=>'',
            'callbackType'=>$callbackType,'forwardUrl'=>'','confirmMsg'=>'','callback'=>$callback,'del'=>$del);
        $this->ajaxReturn($data,'TEXT');
    }
}