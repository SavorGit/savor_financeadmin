<?php
namespace Dataexport\Controller;

class PurchaselistController extends BaseController {
    
    public function index(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        
        $where = [];
        $where['p.purchase_date'] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        $where['a.status'] = 1;
        $fileds = "ct.serial_number c_serial_number,p.serial_number,p.purchase_date,a.total_amount,s.name supplier_name,
                   case a.status
				   when 1 then '进行中'
				   when 2 then '已完成' END AS status,
                   g.id goods_id,a.goods_id,g.name goods_name,p.id purchase_id,st.id stock_id,st.io_date,st.serial_number stock_serial_number";
        $PurchaseDetailModel = new \Admin\Model\PurchaseDetailModel();
        $data_list = $PurchaseDetailModel->alias('a')
                            ->join('savor_finance_purchase p on a.purchase_id=p.id','left')
                            ->join('savor_finance_contract ct on ct.id=p.contract_id','left')
                            ->join('savor_finance_goods g on a.goods_id=g.id','left')
                            ->join('savor_finance_category c on g.category_id=c.id','left')
                            ->join('savor_finance_unit  u on a.unit_id  = u.id','left')
                            ->join('savor_finance_supplier s on g.supplier_id= s.id','left')
                            ->join('savor_finance_stock st on st.purchase_id=p.id','left')
                            ->field($fileds)
                            ->where($where)
                            ->select();
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $m_purchase_paydetail = new \Admin\Model\PurchasePaydetailModel();
        $m_stock_record = new \Admin\Model\StockRecordModel();
        foreach($data_list as $key=>$v){
            //获取入库明细
            $where = [];
            $where['stock_id'] = $v['stock_id'];
            $where['goods_id'] = $v['goods_id'];
            $where['status']   = 1;
            $ret = $m_stock_detail->field('*')->where($where)->find();
            //含税单价
            $price = $ret['price'];
            $data_list[$key]['price'] = $price; 
            //税率 
            $data_list[$key]['rate']  = !empty($price) ? ($ret['rate']*100).'%' :'';
            //不含税单价
            $data_list[$key]['no_rate_price'] = $price - $price* $ret['rate']; 
            //含税总金额   单价*数量
            $total_money = $price * $v['total_amount'];
            $data_list[$key]['total_money'] = $total_money;
            //不含税总金额   总价-总税额
            $total_rate_money = $price* $ret['rate']*$v['total_amount'];
            $data_list[$key]['no_rate_total_money'] = $total_money - $total_rate_money;
            //总税额
            $data_list[$key]['total_no_rate_money'] = $total_rate_money;
            //付款日期 付款明细最后一条时间
            $where = [];
            $where['purchase_id'] = $v['purchase_id'];
            $where['status'] = 1;
            $rts = $m_purchase_paydetail->field('pay_date')->where($where)->order('id desc')->find();
            $data_list[$key]['pay_date'] = $rts['pay_date'];
            //已付金额+未付金额= 含税总金额
            $rts = $m_purchase_paydetail->field('sum(pay_fee) have_pay_fee')->where($where)->find();
            $data[$key]['have_pay_fee'] = $rts['have_pay_fee'];
            $data[$key]['have_no_pay_fee'] = $total_money - $rts['have_pay_fee'];
            
            //入库数量
            $where = [];
            $where['stock_id'] = $v['stock_id'];
            $where['goods_id'] = $v['goods_id'];
            $where['type']     = 1;
            $where['dstatus']  = 1;
            $rts = $m_stock_record->field('sum(total_amount) storage_nums')->where($where)->select();
            $data_list[$key]['storage_nums'] = $rts[0]['storage_nums'];
            
            //入库金额
            $data_list[$key]['storage_total_money'] = $rts[0]['storage_nums']*$price;
            
            
            
        }
        $cell = array(
            array('c_serial_number','采购合同编号'),
            array('serial_number','采购单号'),
            array('purchase_date','采购日期'),
            array('total_amount','数量'),
            array('supplier_name','供应商'),
            array('status','采购状态'),
            array('goods_id','商品编码'),
            array('goods_name','商品名称'),
            array('price','含税单价'),
            array('rate','税率'),
            
            array('no_rate_price','不含税单价'),
            array('total_money','含税总金额'),
            array('no_rate_total_money','不含税总金额'),
            array('total_no_rate_money','总税额'),
            array('pay_date','付款日期'),
            array('have_pay_fee','已付金额'),
            array('have_no_pay_fee','未付金额'),
            array('io_date','入库时间'),
            array('stock_serial_number','入库单编号'),
            array('storage_total_money','入库金额'),
            array('storage_nums','入库数量'),
            
        );
        $filename = '采购订单列表';
        $this->exportToExcel($cell,$data_list,$filename,1);
    }
    public function summary(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        
        $where = [];
        $where['p.purchase_date'] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        $where['a.status'] = 1;
        
        
        $PurchaseDetailModel = new \Admin\Model\PurchaseDetailModel();
        
        $fields = 'g.barcode,a.goods_id ,g.name goods_name ,s.name supplier_name';
        $result = $PurchaseDetailModel->alias('a')
        ->join('savor_finance_purchase p on a.purchase_id=p.id','left')
        ->join('savor_finance_goods g on a.goods_id = g.id','left')
        ->join('savor_finance_supplier s on g.supplier_id= s.id','left')
        ->field($fields)->where($where)->group('a.goods_id')->select();
        foreach($result as $key=>$v){
            //数量
            $where = [];
            $where['p.purchase_date'] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
            $where['a.status']   = 1;
            $where['a.goods_id'] = $v['goods_id'];
            
            $fields = 'a.total_amount,a.price,st.id stock_id';
            $rts = $PurchaseDetailModel->alias('a')
            ->join('savor_finance_purchase p on a.purchase_id=p.id','left')
            ->join('savor_finance_contract ct on ct.id=p.contract_id','left')
            ->join('savor_finance_goods g on a.goods_id=g.id','left')
            ->join('savor_finance_stock st on st.purchase_id=p.id','left')
            ->field($fields)
            ->where($where)
            ->select();
            $total_amount = 0;
            $total_money  = 0;
            foreach($rts as $kk=>$vv){
                $total_amount  +=$vv['total_amount'] ;
                $all_money   = $vv['price'] * $vv['total_amount'];
                $total_money += $all_money;
            }
            $result[$key]['total_amount'] = $total_amount;
            $no_rate_total_money = round($total_money / 1.13,2);
            $result[$key]['no_rate_total_money'] = $no_rate_total_money;
            $result[$key]['rate_total_money']    = $total_money - $no_rate_total_money;
            $result[$key]['total_money']  = $total_money;
            
        }
        $cell = array(
            array('goods_id','商品编码'),
            array('goods_name','商品名称'),
            array('supplier_name','供应商'),
            array('total_amount','数量'),
            array('total_money','含税总金额'),
            array('no_rate_total_money','不含税总金额'),
            array('rate_total_money','税额'),
            
        );
        $filename = '采购汇总表';
        $this->exportToExcel($cell,$result,$filename,1);
    }
    
}