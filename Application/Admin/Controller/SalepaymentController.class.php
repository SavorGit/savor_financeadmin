<?php
namespace Admin\Controller;

class SalepaymentController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        $where = array();
        if(!empty($keyword)){
            $where['hotel.name'] = array('like',"%$keyword%");
        }
        if(empty($start_time) || empty($end_time)){
            $start_time = date('Y-m-d',strtotime('-1 month'));
            $end_time = date('Y-m-d');
        }
        $now_start_time = date('Y-m-d',strtotime($start_time));
        $now_end_time = date('Y-m-d',strtotime($end_time));
        $where['a.pay_time'] = array(array('egt',$now_start_time),array('elt',$now_end_time));

        $start = ($pageNum-1)*$size;
        $m_salepayment = new \Admin\Model\SalePaymentModel();
        $res_list = $m_salepayment->getList('a.*,hotel.name as hotel_name',$where,'a.id desc', $start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $all_types = array('1'=>'核销','2'=>'团购');
            $m_paymentrecord = new \Admin\Model\SalePaymentRecordModel();
            $data_list = $res_list['list'];
            foreach ($data_list as $k=>$v){
                $res_linknum = $m_paymentrecord->getAllData('count(id) as num',array('sale_payment_id'=>$v['id']));
                $data_list[$k]['link_sale_num'] = intval($res_linknum[0]['num']);
                $data_list[$k]['type_str'] = $all_types[$v['type']];
            }
        }
        $this->assign('keyword',$keyword);
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
        $m_salepayment = new \Admin\Model\SalePaymentModel();
        if(IS_POST){
            $tax_rate = I('post.tax_rate',0,'intval');
            $pay_money = I('post.pay_money',0,'intval');
            $type = I('post.type',1,'intval');
            $hotel_id = I('post.hotel_id',0,'intval');
            $pay_time = I('post.pay_time','','trim');
            $userInfo = session('sysUserInfo');
            $sysuser_id = $userInfo['id'];
            if($type==1){
                if(empty($hotel_id)){
                    $this->output('请选择酒楼', 'salepayment/addpayment',2,0);
                }
            }

            $data = array('hotel_id'=>$hotel_id,'tax_rate'=>$tax_rate,'pay_money'=>$pay_money,'pay_time'=>$pay_time,'type'=>$type,'sysuser_id'=>$sysuser_id);
            if(!empty($id)){
                $data['update_time'] = date('Y-m-d H:i:s');
                $m_salepayment->updateData(array('id'=>$id),$data);
            }else{
                $nowdate = date('Ymd');
                $where = array('DATE_FORMAT(add_time, "%Y%m%d")'=>$nowdate);
                $res_salepayment = $m_salepayment->getAllData('count(id) as num',$where);
                if($res_salepayment[0]['num']>0){
                    $number = $res_salepayment[0]['num']+1;
                }else{
                    $number = 1;
                }
                $num_str = str_pad($number,4,'0',STR_PAD_LEFT);
                $serial_number = "SKD-$nowdate-$num_str";
                $data['serial_number']=$serial_number;
                $m_salepayment->add($data);
            }
            $this->output('操作成功', 'salepayment/datalist');
        }else{
            $vinfo = array('tax_rate'=>13,'type'=>1);
            if($id){
                $vinfo = $m_salepayment->getInfo(array('id'=>$id));
            }
            $m_hotel = new \Admin\Model\HotelModel();
            $hotel_list = $m_hotel->getHotelDatas('hotel.id,hotel.name,area.region_name',array('hotel.state'=>1,'hotel.flag'=>0,'ext.is_salehotel'=>1),'area.id asc');
            foreach ($hotel_list as $k=>$v){
                $hotel_list[$k]['name'] = "{$v['region_name']}--".$v['name'];
            }
            $this->assign('hotel_list',$hotel_list);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function delpayment(){
        $sale_payment_id = I('get.id',0,'intval');

        $m_paymentrecord = new \Admin\Model\SalePaymentRecordModel();
        $res_record = $m_paymentrecord->getAllData('count(id) as num',array('sale_payment_id'=>$sale_payment_id));
        if($res_record[0]['num']>0){
            $this->output('已关联出库单无法删除', 'salepayment/delpayment',2,0);
        }
        $m_salepayment = new \Admin\Model\SalePaymentModel();
        $m_salepayment->delData(array('id'=>$sale_payment_id));

        $this->output('操作成功!', 'salepayment/datalist',2);
    }

    public function linksaleadd(){
        $sale_payment_id = I('sale_payment_id',0,'intval');
        $source = I('source',0,'intval');

        $m_paymentrecord = new \Admin\Model\SalePaymentRecordModel();
        $m_sale = new \Admin\Model\SaleModel();
        if(IS_POST){
            $sale_ids = I('post.sale_ids','','trim');
            $remain_money = I('post.remain_money',0);
            if($remain_money==0){
                $this->output('已完成收款', 'salepayment/linksaleadd',2,0);
            }
            if(!empty($sale_ids)){
                $sale_ids = explode(',',$sale_ids);
                $pay_money = $remain_money;
                $is_over = 0;
                foreach ($sale_ids as $v){
                    $sale_id = intval($v);
                    $res_sale = $m_sale->getInfo(array('id'=>$sale_id));
                    $settlement_price = $res_sale['settlement_price'];

                    $res_record = $m_paymentrecord->getAllData('*',array('sale_id'=>$sale_id,'sale_payment_id'=>$sale_payment_id));
                    if(!empty($res_record)){
                        $this->output("出库单:{$sale_id}请勿重复收款", 'salepayment/linksaleadd',2,0);
                    }
                    $res_sale_money = $m_paymentrecord->getAllData('sum(pay_money) as all_pay_money',array('sale_id'=>$sale_id));
                    $remian_settlement_price = $settlement_price-intval($res_sale_money[0]['all_pay_money']);
                    if($remian_settlement_price==0){
                        $this->output("出库单:{$sale_id}已完成收款", 'salepayment/linksaleadd',2,0);
                    }
                    $record_pay_money = 0;
                    $ptype = 0;
                    if($pay_money>=0){
                        if($pay_money>=$remian_settlement_price){
                            $record_pay_money = $remian_settlement_price;
                            $ptype = 1;
                        }else{
                            if($pay_money>0){
                                $ptype = 2;
                                $record_pay_money = $pay_money;
                            }
                        }
                    }else{
                        $is_over=1;
                        $record_pay_money = abs($pay_money);
                        $ptype = 2;
                    }
                    if($is_over==1){
                        $this->output('所选出库单数大于可分配的收款金额', 'salepayment/linksaleadd', 2, 0);
                    }
                    $pay_money = $pay_money-$remian_settlement_price;
                    $pay_record[]=array('sale_id'=>$sale_id,'pay_money'=>$record_pay_money,'ptype'=>$ptype,'re_pay_money'=>$pay_money,'is_over'=>$is_over);
                }
                if(!empty($pay_record)){
                    foreach ($pay_record as $v){
                        if($v['pay_money']>0){
                            $m_sale->updateData(array('id'=>$v['sale_id']),array('status'=>2,'sale_payment_id'=>$sale_payment_id,'ptype'=>$v['ptype']));
                            $m_sale->where(array('id'=>$v['sale_id']))->setInc('pay_money',$v['pay_money']);

                            $m_paymentrecord->add(array('sale_id'=>$v['sale_id'],'sale_payment_id'=>$sale_payment_id,'pay_money'=>$v['pay_money']));
                        }
                    }
                }
            }
            if($source==1){
                $jump_url = 'salepayment/datalist';
            }else{
                $jump_url = 'salepayment/linksalelist';
            }
            $this->output('操作成功', $jump_url);
        }else{
            $m_salepayment = new \Admin\Model\SalePaymentModel();
            $res_salepayment = $m_salepayment->getInfo(array('id'=>$sale_payment_id));
            $res_money = $m_paymentrecord->getAllData('sum(pay_money) as all_pay_money',array('sale_payment_id'=>$sale_payment_id));
            $remain_money = $res_salepayment['pay_money']-intval($res_money[0]['all_pay_money']);

            if($res_salepayment['type']==1){
                $fileds = "a.id,a.idcode,hotel.name hotel_name,a.add_time,a.sale_payment_id,a.settlement_price,a.ptype,
            a.goods_id,goods.name as goods_name,a.sale_openid,a.maintainer_id";
                $where = array('a.hotel_id'=>$res_salepayment['hotel_id'],'a.ptype'=>array('in','0,2'),'record.wo_reason_type'=>1,'record.wo_status'=>2);
                $res_all_sales = $m_sale->getList($fileds,$where,'a.id desc', 0,0);
            }else{
                $where = array('type'=>array('in','2,5'),'ptype'=>array('in','0,2'));
                $res_all_sales = $m_sale->getAllData('*',$where,'id desc');
            }

            $m_sysuser = new \Admin\Model\SysuserModel();
            $m_stock_record = new \Admin\Model\StockRecordModel();
            $all_sales = array();
            foreach ($res_all_sales as $k=>$v){
                $is_select = '';
                if($sale_payment_id>0 && $v['sale_payment_id']==$sale_payment_id){
                    $is_select = 'selected';
                    continue;
                }
                $v['is_select'] = $is_select;
                $res_user = $m_sysuser->getSysUser($v['maintainer_id']);
                $pay_money = $v['settlement_price'];
                $pay_status = '';
                if($v['ptype']==2){
                    $res_money = $m_paymentrecord->getAllData('sum(pay_money) as all_pay_money',array('sale_id'=>$v['id']));
                    $pay_money = $pay_money-$res_money[0]['all_pay_money'];
                    $pay_money = sprintf("%.2f",$pay_money);
                    $pay_status = '【部分收款】';
                }
                $goods_name = $v['goods_name'];
                if($res_salepayment['type']==2){
                    $all_idcodes = explode("\n",$v['idcode']);
                    $fileds = 'a.id,a.type,a.idcode,goods.name as goods_name,goods.id goods_id';
                    $res_list = $m_stock_record->getStockRecordList($fileds,array('a.idcode'=>trim($all_idcodes[0]),'a.dstatus'=>1),'a.id desc','0,1','');
                    $goods_name = $res_list[0]['goods_name'];
                }

                $v['name'] = "{$v['id']}--{$v['add_time']}--{$goods_name}--{$pay_money}--{$res_user[0]['remark']}{$pay_status}";
                $all_sales[]=$v;
            }

            $this->assign('all_sales',$all_sales);
            $this->assign('sale_payment_id',$sale_payment_id);
            $this->assign('remain_money',$remain_money);
            $this->assign('source',$source);
            $this->display();
        }
    }

    public function linksalelist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $sale_payment_id = I('sale_payment_id',0,'intval');
        $start = ($page-1)*$size;

        $m_paymentrecord = new \Admin\Model\SalePaymentRecordModel();
        $fields = 'a.*,sale.goods_id,sale.idcode,sale.settlement_price,sale.ptype,sale.status';
        $result = $m_paymentrecord->getList($fields,array('a.sale_payment_id'=>$sale_payment_id),'a.id desc',$start,$size);
        $datalist = $result['list'];
        $all_ptype = C('PAY_TYPE');
        foreach ($datalist as $k=>$v){
            $datalist[$k]['ptype_str'] = $all_ptype[$v['ptype']];
        }

        $this->assign('sale_payment_id', $sale_payment_id);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function linksaledel(){
        $id = I('get.id',0,'intval');
        $m_pushu8 = new \Admin\Model\Pushu8RecordModel();
        $res_push = $m_pushu8->getInfo(array('payment_record_id'=>$id,'type'=>22));
        if(!empty($res_push)){
            $this->output('当前出库单已推送用友【销售回款凭证】,不能删除', 'salepayment/linksalelist',2,0);
        }

        $m_paymentrecord = new \Admin\Model\SalePaymentRecordModel();
        $res_info = $m_paymentrecord->getInfo(array('id'=>$id));
        $m_paymentrecord->delData(array('id'=>$id));

        $res_allsale = $m_paymentrecord->getDataList('*',array('sale_id'=>$res_info['sale_id']),'id desc');
        $sale_payment_id = 0;
        foreach ($res_allsale as $v){
            if($v['id']!=$id && $sale_payment_id==0){
                $sale_payment_id = $v['sale_payment_id'];
            }
        }
        $status=$ptype=0;
        if($sale_payment_id){
            $status = 2;
            $ptype = 2;
        }
        $m_sale = new \Admin\Model\SaleModel();
        $res_sale = $m_sale->getInfo(array('id'=>$res_info['sale_id']));
        $now_pay_money = sprintf("%.2f",$res_sale['pay_money']-$res_info['pay_money']);

        $m_sale->updateData(array('id'=>$res_info['sale_id']),array('status'=>$status,'sale_payment_id'=>$sale_payment_id,
            'ptype'=>$ptype,'pay_money'=>$now_pay_money));

        $this->output('操作成功!', 'salepayment/linksalelist',2);
    }

}