<?php
namespace Admin\Controller;

class SalepaymentController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');

        $where = array();
        if(!empty($keyword)){
            $where['hotel.name'] = array('like',"%$keyword%");
        }
        $start = ($pageNum-1)*$size;
        $m_salepayment = new \Admin\Model\SalePaymentModel();
        $res_list = $m_salepayment->getList('a.*,hotel.name as hotel_name',$where,'a.id desc', $start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_paymentrecord = new \Admin\Model\SalePaymentRecordModel();
            $data_list = $res_list['list'];
            foreach ($data_list as $k=>$v){
                $res_linknum = $m_paymentrecord->getAllData('count(id) as num',array('sale_payment_id'=>$v['id']));
                $data_list[$k]['link_sale_num'] = intval($res_linknum[0]['num']);
            }
        }
        $this->assign('keyword',$keyword);
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
            $hotel_id = I('post.hotel_id',0,'intval');
            $pay_time = I('post.pay_time','','trim');
            $userInfo = session('sysUserInfo');
            $sysuser_id = $userInfo['id'];

            $data = array('hotel_id'=>$hotel_id,'tax_rate'=>$tax_rate,'pay_money'=>$pay_money,'pay_time'=>$pay_time,'sysuser_id'=>$sysuser_id);
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
            $vinfo = array('tax_rate'=>13);
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

    public function linksaleadd(){
        $sale_payment_id = I('sale_payment_id',0,'intval');

        $m_paymentrecord = new \Admin\Model\SalePaymentRecordModel();
        $m_sale = new \Admin\Model\SaleModel();
        if(IS_GET){
            $m_salepayment = new \Admin\Model\SalePaymentModel();
            $res_salepayment = $m_salepayment->getInfo(array('id'=>$sale_payment_id));
            $res_money = $m_paymentrecord->getAllData('sum(pay_money) as all_pay_money',array('sale_payment_id'=>$sale_payment_id));
            $remain_money = $res_salepayment['pay_money']-intval($res_money[0]['all_pay_money']);

            $fileds = "a.id,a.idcode,hotel.name hotel_name,a.add_time,a.sale_payment_id,a.settlement_price,
            a.goods_id,goods.name as goods_name,a.sale_openid";
            $where = array('a.hotel_id'=>$res_salepayment['hotel_id'],'record.wo_status'=>2);
            $all_sales = $m_sale->getList($fileds,$where,'a.id desc', 0,0);
            foreach ($all_sales as $k=>$v){
                $is_select = '';
                if($sale_payment_id>0 && $v['sale_payment_id']==$sale_payment_id){
                    $is_select = 'selected';
                }
                $all_sales[$k]['is_select'] = $is_select;
                $all_sales[$k]['name'] = "{$v['id']}-{$v['add_time']}-{$v['goods_name']}-{$v['settlement_price']}";
            }

            $this->assign('all_sales',$all_sales);
            $this->assign('sale_payment_id',$sale_payment_id);
            $this->assign('remain_money',$remain_money);
            $this->display();
        }else{
            $sale_id = I('post.sale_id',0,'intval');
            $remain_money = I('post.remain_money',0);
            if($remain_money==0){
                $this->output('已完成收款', 'salepayment/linksaleadd',2,0);
            }
            $res_record = $m_paymentrecord->getAllData('*',array('sale_id'=>$sale_id,'sale_payment_id'=>$sale_payment_id));
            if(!empty($res_record)){
                $this->output('请勿重复收款', 'salepayment/linksaleadd',2,0);
            }
            $res_sale = $m_sale->getInfo(array('id'=>$sale_id));
            $res_sale_money = $m_paymentrecord->getAllData('sum(pay_money) as all_pay_money',array('sale_id'=>$sale_id));
            $remian_settlement_price = $res_sale['settlement_price']-intval($res_sale_money[0]['all_pay_money']);
            if($remian_settlement_price>$remain_money){
                $pay_money = $remain_money;
                $ptype = 2;
            }else{
                $pay_money = $remian_settlement_price;
                $ptype = 1;
            }

            $m_sale->updateData(array('id'=>$sale_id),array('status'=>2,'sale_payment_id'=>$sale_payment_id,'ptype'=>$ptype));
            $m_paymentrecord->add(array('sale_id'=>$sale_id,'sale_payment_id'=>$sale_payment_id,'pay_money'=>$pay_money));

            $this->output('操作成功', 'salepayment/linksalelist');
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
        $all_ptype = array('1'=>'完全收款','2'=>'部分收款');
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
        $m_paymentrecord = new \Admin\Model\SalePaymentRecordModel();
        $res_info = $m_paymentrecord->getInfo(array('id'=>$id));
        $m_sale = new \Admin\Model\SaleModel();
        $m_sale->updateData(array('id'=>$res_info['sale_id']),array('status'=>0,'sale_payment_id'=>0,'ptype'=>0));
        $m_paymentrecord->delData(array('id'=>$id));

        $this->output('操作成功!', 'salepayment/linksalelist',2);
    }

}