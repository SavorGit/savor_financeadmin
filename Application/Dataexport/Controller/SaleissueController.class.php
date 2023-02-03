<?php
namespace Dataexport\Controller;

class SaleissueController extends BaseController {
    
    /**
     * @desc 数据查询  销售出库单列表
     */
    public function datalist() {
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        
        $where = [];
        $where['a.add_time'] = array(array('EGT',$start_date.' 00:00:00'),array('ELT',$end_date.' 23:59:59'));
        $orders = 'a.id desc';
        $fields = "a.add_time,a.id,case a.type when 1 then '餐厅销售' when 2 then '团购售卖' when 3 then '其他售卖' end as type,
                   a.idcode,area.region_name,a.hotel_id,hotel.name hotel_name,goods.barcode,goods.name goods_name,
                   unit.name unit_name,spe.name spe_name,a.settlement_price,a.cost_price,a.settlement_price-a.cost_price as profit ,
                   a.pay_time,a.pay_money,a.settlement_price-a.pay_money uncollected_money,a.invoice_time,a.invoice_money,sysuser.remark,user.nickName,user.name";
        $m_sale = new \Admin\Model\SaleModel();
        $data_list = $m_sale->alias('a')
                            ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
                            ->join('savor_finance_goods goods on a.goods_id   = goods.id','left')
                            ->join('savor_finance_unit unit on unit.id =goods.unit_id','left')
                            ->join('savor_finance_specification spe on goods.specification_id= spe.id','left')
                            ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                            ->join('savor_sysuser sysuser on a.maintainer_id= sysuser.id ','left')
                            ->join('savor_area_info area on  area.id=hotel.area_id','left')
                            ->join('savor_smallapp_user user on a.sale_openid=user.openid','left')
                            ->field($fields)
                            ->where($where)
                            ->order($orders)
                            ->select();
        foreach($data_list as $key=>$v){
            if($v['uncollected_money']==0){
                $account_days =  ceil((strtotime($v['pay_time']) - strtotime($v['add_time'])) / 86400) ;
            }else {
                $account_days =  ceil((time() - strtotime($v['add_time'])) / 86400) ;
            }
            $data_list[$key]['account'] = $account_days.'天';
            if(empty($v['name'])){
                $data_list[$key]['name'] = $v['nickname'];
            }
            $data_list[$key]['amount'] = 1;
        }
        //print_r($data_list);
        $cell = array(
            array('add_time','核销日期'),
            array('id','核销单据编号'),
            array('type','销售类型'),
            array('idcode','唯一识别码'),
            array('region_name','城市'),
            array('hotel_id','仓库编号'),
            array('hotel_name','仓库名称'),
            array('barcode','商品编码'),
            array('goods_name','商品名称'),
            array('unit_name','单位'),
            array('spe_name','规格'),
            array('amount','数量'),
            
            array('settlement_price','结算单价'),
            array('cost_price','出库成本'),
            array('profit','销售毛利'),
            array('pay_time','收款日期'),
            array('pay_money','收款金额'),
            array('uncollected_money','未收款金额'),
            array('account','账龄'),
            array('invoice_time','开票日期'),
            array('invoice_money','开票金额'),
            array('remark','业务员'),
            array('name','销售经理'),
        );
        $filename = '销售出库单列表';
        $this->exportToExcel($cell,$data_list,$filename,1);
    }
}