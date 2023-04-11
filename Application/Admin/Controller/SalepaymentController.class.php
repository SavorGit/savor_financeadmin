<?php
namespace Admin\Controller;

class SalepaymentController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');

        $where = array();
        if(!empty($keyword)){
            $where['payer_name'] = array('like',"%$keyword%");
        }
        $start = ($pageNum-1)*$size;
        $m_salepayment = new \Admin\Model\SalePaymentModel();
        $res_list = $m_salepayment->getDataList('*',$where,'id desc', $start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $data_list = $res_list['list'];
            $oss_host = get_oss_host();
            foreach ($data_list as $k=>$v){
                if(!empty($v['pay_image'])){
                    $data_list[$k]['pay_image'] = $oss_host.$v['pay_image'];
                }
                $data_list[$k]['sale_ids'] = trim($v['sale_ids'],',');
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
        $m_sale = new \Admin\Model\SaleModel();
        if(IS_POST){
            $tax_rate = I('post.tax_rate',0,'intval');
            $pay_money = I('post.pay_money',0,'intval');
            $hotel_id = I('post.hotel_id',0,'intval');
            $pay_time = I('post.pay_time','','trim');
            $sale_ids = I('post.sale_ids');
            $userInfo = session('sysUserInfo');
            $sysuser_id = $userInfo['id'];
            $nowdate = date('Ymd');
            $field = 'count(id) as num';
            $where = array('DATE_FORMAT(add_time, "%Y%m%d")'=>$nowdate);
            $res_salepayment = $m_salepayment->getAllData($field,$where);
            if($res_salepayment[0]['num']>0){
                $number = $res_salepayment[0]['num']+1;
            }else{
                $number = 1;
            }
            $num_str = str_pad($number,4,'0',STR_PAD_LEFT);
            $serial_number = "SKD-$nowdate-$num_str";
            $data = array('serial_number'=>$serial_number,'hotel_id'=>$hotel_id,'tax_rate'=>$tax_rate,'pay_money'=>$pay_money,'pay_time'=>$pay_time,'sysuser_id'=>$sysuser_id);
            if(!empty($sale_ids)){
                $res_money = $m_sale->getAllData('sum(settlement_price) as all_money',array('id'=>array('in',$sale_ids)));
                $all_money = intval($res_money[0]['all_money']);
                if($pay_money-$all_money<0){
                    $this->output('出库单结算价大于收款金额', 'salepayment/addpayment', 2, 0);
                }
            }

            if(!empty($id)){
                $res_info = $m_salepayment->getInfo(array('id'=>$id));
                if(!empty($res_info['sale_ids'])){
                    $old_sale_ids = trim($res_info['sale_ids'],',');
                    $m_sale->updateData(array('id'=>array('in',$old_sale_ids)),array('status'=>0,'sale_payment_id'=>0));
                }
                $data['update_time'] = date('Y-m-d H:i:s');
                $m_salepayment->updateData(array('id'=>$id),$data);
            }else{
                $id = $m_salepayment->add($data);
            }
            if(!empty($sale_ids)){
                $m_sale->updateData(array('id'=>array('in',$sale_ids)),array('status'=>2,'sale_payment_id'=>$id));
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

}