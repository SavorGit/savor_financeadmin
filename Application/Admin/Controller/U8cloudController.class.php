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
                'prepareddate'=>$res_stock['pay_time']
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

    public function sellvoucher1(){
        $sale_id = I('get.sale_id',0,'intval');
        $userinfo = session('sysUserInfo');
        if(!empty($userinfo['telephone'])){
            $pk_prepared = $userinfo['telephone'];
        }else{
            $pk_prepared = $this->voucher_params['pk_prepared'];
        }
        $m_sale = new \Admin\Model\SaleModel();
        $fileds = 'a.id,a.idcode,a.residenter_id,a.goods_settlement_price,a.settlement_price,a.add_time,record.avg_price,record.pidcode,record.id as stock_record_id,
        hotel.id as hotel_id,hotel.name as hotel_name,hotel.short_name,goods.name as goods_name,goods.u8_pk_accsubj,area.region_name as area_name';
        $res_sale = $m_sale->getSaleDatas($fileds,array('a.id'=>$sale_id));
        if(empty($res_sale[0]['residenter_id'])){
            $this->output('发起核销时,无酒楼驻店人','stock/writeofflist',2,0);
        }
        $m_department_user = new \Admin\Model\DepartmentUserModel();
        $res_duser = $m_department_user->getAll('department_id',array('sys_user_id'=>$res_sale[0]['residenter_id']),0,1,'id desc');
        if(empty($res_duser[0]['department_id'])){
            $this->output('酒楼驻店人无对应采购组织部门','stock/writeofflist',2,0);
        }
        $department_id = $res_duser[0]['department_id'];
        $hotel_name = $res_sale[0]['hotel_name'];
        if(!empty($res_sale[0]['short_name'])){
            $hotel_name = $res_sale[0]['short_name'];
        }
        $wo_date = date('m月d日',strtotime($res_sale[0]['add_time']));
        $prepareddate = date('Y-m-d',strtotime($res_sale[0]['add_time']));
        $idcode = $res_sale[0]['idcode'];
        $explanation1 = $wo_date.'-'.$res_sale[0]['area_name'].'-'.$hotel_name.'-'.$res_sale[0]['goods_name'];
        $explanation2 = $explanation1.'-成本结转';

        $voucher = array();
        $total_fee = $res_sale[0]['goods_settlement_price'];
        $rate_money = round($total_fee/$this->voucher_params['rate'],2);
        $now_money = $total_fee-$rate_money;
        $pk_currtype = $this->voucher_params['pk_currtype'];
        if(!empty($res_sale[0]['avg_price'])){
            $avg_price = $res_sale[0]['avg_price'];
            $avg_rate_money = round($avg_price/$this->voucher_params['rate'],2);
        }else{
            $avg_rate_money = 0;
            if(!empty($res_sale[0]['pidcode'])){
                $m_stock_record = new \Admin\Model\StockRecordModel();
                $rwhere = array('idcode'=>$res_sale[0]['pidcode']);
                $rwhere['avg_price'] = array('gt',0);
                $res_record = $m_stock_record->getAll('avg_price',$rwhere,0,1,'id desc');
                if(!empty($res_record[0]['avg_price'])){
                    $avg_price = $res_record[0]['avg_price'];
                    $avg_rate_money = round($avg_price/$this->voucher_params['rate'],2);
                }
            }
        }

        $voucher[]=array(
            'details'=>array(
                array('explanation'=>$explanation1,'pk_accsubj'=>'11220201','pk_currtype'=>$pk_currtype,'debitamount'=>$total_fee,'creditamount'=>0,'freevalue5'=>$idcode,
                    'ass'=>array(
                        array('checktypecode'=>'73','checkvaluecode'=>"HZCT{$res_sale[0]['hotel_id']}"),
                        array('checktypecode'=>'2','checkvaluecode'=>"{$department_id}"),
                    ),
                ),
                array('explanation'=>$explanation1,'pk_accsubj'=>'600101','pk_currtype'=>$pk_currtype,'debitamount'=>0,'creditamount'=>$rate_money,'freevalue5'=>$idcode,
                    'ass'=>array(
                        array('checktypecode'=>'2','checkvaluecode'=>"{$department_id}"),
                    ),
                ),
                array('explanation'=>$explanation1,'pk_accsubj'=>'22210108','pk_currtype'=>$pk_currtype,'debitamount'=>0,'creditamount'=>$now_money,'freevalue5'=>$idcode,),

                array('explanation'=>$explanation2,'pk_accsubj'=>'640101','pk_currtype'=>$pk_currtype,'debitamount'=>$avg_rate_money,'creditamount'=>0,'freevalue5'=>$idcode,),
                array('explanation'=>$explanation2,'pk_accsubj'=>"{$res_sale[0]['u8_pk_accsubj']}",'pk_currtype'=>$pk_currtype,'debitamount'=>0,'creditamount'=>$avg_rate_money,'freevalue5'=>$idcode,),

            ),
            'pk_corp'=>$this->voucher_params['pk_corp'],
            'pk_glorgbook'=>$this->voucher_params['pk_glorgbook'],
            'pk_prepared'=>$pk_prepared,
            'pk_vouchertype'=>'收入成本确认',
            'prepareddate'=>$prepareddate
        );

        $params = array(
            'voucher'=>$voucher
        );
        $u8 = new \Common\Lib\U8cloud();
        $resp_apidata = $u8->addVoucher($params);
        $res_data = json_decode($resp_apidata['result'],true);
        $res_u8data = json_decode($res_data['data'],true);
        if(empty($res_u8data[0]['pk_voucher'])){
            $this->output('调用凭证接口出错','stock/writeofflist',2,0);
        }
        $push_data=array('sale_id'=>$sale_id,'stock_record_id'=>$res_sale[0]['stock_record_id'],'type'=>21,'u8_pk_id'=>$res_u8data[0]['pk_voucher'],'status'=>1);
        $m_pushu8 = new \Admin\Model\Pushu8RecordModel();
        $m_pushu8->add($push_data);
        $this->output('同步酒楼核销用友凭证成功','stock/writeofflist',3);
    }

    public function sellvoucher2(){
        $sale_id = I('get.sale_id',0,'intval');
        $userinfo = session('sysUserInfo');
        if(empty($userinfo['telephone'])){
            $this->output('请使用用友账号进行同步','saleissue/index',2,0);
        }
        $pk_prepared = $userinfo['telephone'];
        $m_sale = new \Admin\Model\SaleModel();
        $fileds = 'a.id,a.idcode,a.residenter_id,a.ptype,a.settlement_price,a.add_time,record.avg_price,record.pidcode,record.id as stock_record_id,
        hotel.id as hotel_id,hotel.name as hotel_name,hotel.short_name,goods.name as goods_name,goods.u8_pk_accsubj,area.region_name as area_name';
        $res_sale = $m_sale->getSaleDatas($fileds,array('a.id'=>$sale_id));
        if(empty($res_sale[0]['residenter_id'])){
            $this->output('发起核销时,无酒楼驻店人','saleissue/index',2,0);
        }
        $m_department_user = new \Admin\Model\DepartmentUserModel();
        $res_duser = $m_department_user->getAll('department_id',array('sys_user_id'=>$res_sale[0]['residenter_id']),0,1,'id desc');
        if(empty($res_duser[0]['department_id'])){
            $this->output('酒楼驻店人无对应采购组织部门','saleissue/index',2,0);
        }
        if($res_sale[0]['ptype']!=1){
            $this->output('请先完成收款动作','saleissue/index',2,0);
        }

        $department_id = $res_duser[0]['department_id'];
        $hotel_name = $res_sale[0]['hotel_name'];
        if(!empty($res_sale[0]['short_name'])){
            $hotel_name = $res_sale[0]['short_name'];
        }
        $wo_date = date('m月d日',strtotime($res_sale[0]['add_time']));
        $idcode = $res_sale[0]['idcode'];
        $pk_currtype = $this->voucher_params['pk_currtype'];
        $explanation = '收到-'.$wo_date.'-'.$res_sale[0]['area_name'].'-'.$hotel_name.'-'.$res_sale[0]['goods_name'].'-货款';
        $total_fee = $res_sale[0]['settlement_price'];
        $m_payment_record = new \Admin\Model\SalePaymentRecordModel();
        $res_precord = $m_payment_record->getPaymentRecords('a.id,p.pay_time',array('a.sale_id'=>$sale_id),'p.pay_time desc','0,1');
        $prepareddate = $res_precord[0]['pay_time'];

        $voucher = array();
        $voucher[]=array(
            'details'=>array(
                array('explanation'=>$explanation,'pk_accsubj'=>'100201','pk_currtype'=>$pk_currtype,'debitamount'=>$total_fee,'creditamount'=>0,'freevalue5'=>$idcode,
                    'cashflow'=>array(
                        array('money'=>$total_fee,'pk_cashflow'=>'1111','pk_currtype'=>$pk_currtype)
                    )
                ),
                array('explanation'=>$explanation,'pk_accsubj'=>'11220201','pk_currtype'=>$pk_currtype,'debitamount'=>0,'creditamount'=>$total_fee,'freevalue5'=>$idcode,
                    'ass'=>array(
                        array('checktypecode'=>'73','checkvaluecode'=>"HZCT{$res_sale[0]['hotel_id']}"),
                        array('checktypecode'=>'2','checkvaluecode'=>"{$department_id}"),
                    ),
                ),
            ),
            'pk_corp'=>$this->voucher_params['pk_corp'],
            'pk_glorgbook'=>$this->voucher_params['pk_glorgbook'],
            'pk_prepared'=>$pk_prepared,
            'pk_vouchertype'=>'销售回款',
            'prepareddate'=>$prepareddate
        );

        $params = array(
            'voucher'=>$voucher
        );
        $u8 = new \Common\Lib\U8cloud();
        $resp_apidata = $u8->addVoucher($params);
        $res_data = json_decode($resp_apidata['result'],true);
        $res_u8data = json_decode($res_data['data'],true);
        if(empty($res_u8data[0]['pk_voucher'])){
            $this->output('调用凭证接口出错','saleissue/index',2,0);
        }
        $push_data=array('sale_id'=>$sale_id,'stock_record_id'=>$res_sale[0]['stock_record_id'],'type'=>22,'u8_pk_id'=>$res_u8data[0]['pk_voucher'],'status'=>1);
        $m_pushu8 = new \Admin\Model\Pushu8RecordModel();
        $m_pushu8->add($push_data);
        $this->output('同步酒楼回款用友凭证成功','saleissue/index',3);
    }

    public function sellvoucher3(){
        $sale_id = I('get.sale_id',0,'intval');
        $userinfo = session('sysUserInfo');
        if(!empty($userinfo['telephone'])){
            $pk_prepared = $userinfo['telephone'];
        }else{
            $pk_prepared = $this->voucher_params['pk_prepared'];
        }
        $m_sale = new \Admin\Model\SaleModel();
        $fileds = 'a.id,a.idcode,a.residenter_id,a.goods_settlement_price,a.settlement_price,a.add_time,record.avg_price,record.pidcode,record.id as stock_record_id,
        hotel.id as hotel_id,hotel.name as hotel_name,hotel.short_name,goods.name as goods_name,goods.u8_pk_accsubj,area.region_name as area_name';
        $res_sale = $m_sale->getSaleDatas($fileds,array('a.id'=>$sale_id));
        if(empty($res_sale[0]['residenter_id'])){
            $this->output('发起核销时,无酒楼驻店人','stock/writeofflist',2,0);
        }
        $m_department_user = new \Admin\Model\DepartmentUserModel();
        $res_duser = $m_department_user->getAll('department_id',array('sys_user_id'=>$res_sale[0]['residenter_id']),0,1,'id desc');
        if(empty($res_duser[0]['department_id'])){
            $this->output('酒楼驻店人无对应采购组织部门','stock/writeofflist',2,0);
        }
        $department_id = $res_duser[0]['department_id'];
        $hotel_name = $res_sale[0]['hotel_name'];
        if(!empty($res_sale[0]['short_name'])){
            $hotel_name = $res_sale[0]['short_name'];
        }
        $wo_date = date('m月d日',strtotime($res_sale[0]['add_time']));
        $prepareddate = date('Y-m-d',strtotime($res_sale[0]['add_time']));
        $idcode = $res_sale[0]['idcode'];
        $explanation1 = $wo_date.'-'.$res_sale[0]['area_name'].'-'.$hotel_name.'-'.$res_sale[0]['goods_name'];
        $explanation2 = $explanation1.'-成本结转';

        $voucher = array();
        $total_fee = $res_sale[0]['goods_settlement_price'];
        $rate_money = round($total_fee/$this->voucher_params['rate'],2);
        $now_money = $total_fee-$rate_money;
        $pk_currtype = $this->voucher_params['pk_currtype'];
        if(!empty($res_sale[0]['avg_price'])){
            $avg_price = $res_sale[0]['avg_price'];
            $avg_rate_money = round($avg_price/$this->voucher_params['rate'],2);
        }else{
            $avg_rate_money = 0;
            if(!empty($res_sale[0]['pidcode'])){
                $m_stock_record = new \Admin\Model\StockRecordModel();
                $rwhere = array('idcode'=>$res_sale[0]['pidcode']);
                $rwhere['avg_price'] = array('gt',0);
                $res_record = $m_stock_record->getAll('avg_price',$rwhere,0,1,'id desc');
                if(!empty($res_record[0]['avg_price'])){
                    $avg_price = $res_record[0]['avg_price'];
                    $avg_rate_money = round($avg_price/$this->voucher_params['rate'],2);
                }
            }
        }

        $voucher[]=array(
            'details'=>array(
                array('explanation'=>$explanation1,'pk_accsubj'=>'80010803','pk_currtype'=>$pk_currtype,'debitamount'=>$total_fee,'creditamount'=>0,'freevalue5'=>$idcode,
                    'ass'=>array(
                        array('checktypecode'=>'2','checkvaluecode'=>"{$department_id}"),
                    ),
                ),
                array('explanation'=>$explanation1,'pk_accsubj'=>'600101','pk_currtype'=>$pk_currtype,'debitamount'=>0,'creditamount'=>$rate_money,'freevalue5'=>$idcode,
                    'ass'=>array(
                        array('checktypecode'=>'2','checkvaluecode'=>"{$department_id}"),
                    ),
                ),
                array('explanation'=>$explanation1,'pk_accsubj'=>'22210108','pk_currtype'=>$pk_currtype,'debitamount'=>0,'creditamount'=>$now_money,'freevalue5'=>$idcode,),

                array('explanation'=>$explanation2,'pk_accsubj'=>'640101','pk_currtype'=>$pk_currtype,'debitamount'=>$avg_rate_money,'creditamount'=>0,'freevalue5'=>$idcode,),
                array('explanation'=>$explanation2,'pk_accsubj'=>"{$res_sale[0]['u8_pk_accsubj']}",'pk_currtype'=>$pk_currtype,'debitamount'=>0,'creditamount'=>$avg_rate_money,'freevalue5'=>$idcode,),

            ),
            'pk_corp'=>$this->voucher_params['pk_corp'],
            'pk_glorgbook'=>$this->voucher_params['pk_glorgbook'],
            'pk_prepared'=>$pk_prepared,
            'pk_vouchertype'=>'视同销售',
            'prepareddate'=>$prepareddate
        );

        $params = array(
            'voucher'=>$voucher
        );
        $u8 = new \Common\Lib\U8cloud();
        $resp_apidata = $u8->addVoucher($params);
        $res_data = json_decode($resp_apidata['result'],true);
        $res_u8data = json_decode($res_data['data'],true);
        if(empty($res_u8data[0]['pk_voucher'])){
            $this->output('调用凭证接口出错','stock/writeofflist',2,0);
        }
        $push_data=array('sale_id'=>$sale_id,'stock_record_id'=>$res_sale[0]['stock_record_id'],'type'=>21,'u8_pk_id'=>$res_u8data[0]['pk_voucher'],'status'=>1);
        $m_pushu8 = new \Admin\Model\Pushu8RecordModel();
        $m_pushu8->add($push_data);
        $this->output('同步品鉴酒用友凭证成功','stock/writeofflist',3);
    }

    public function delvoucher(){
        //'0001F81000000000318C','0001F81000000000318S'
        $pk_voucher = I('pk_voucher','');

        $params = array('page_now'=>1,'page_size'=>5,'pk_glorgbook'=>$this->voucher_params['pk_glorgbook'],'pk_voucher'=>$pk_voucher);

        $u8 = new \Common\Lib\U8cloud();
        $resp_apidata = $u8->delVoucher($params);
        $res_data = json_decode($resp_apidata['result'],true);
        if($res_data['status']=='success'){
            $message = '删除凭证成功';
        }else{
            $message = '删除凭证失败'.$resp_apidata['result'];
        }
        $this->output($message,'saleissue/index',3);
    }

    private function output($message,$navTab,$type=1,$status=1,$callback="",$del){
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