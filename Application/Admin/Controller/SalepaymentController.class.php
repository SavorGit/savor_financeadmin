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
            foreach ($data_list as $k=>$v){
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
            $payer_name = I('post.payer_name','','trim');
            $payer_account = I('post.payer_account','','trim');
            $pay_media_id = I('post.pay_media_id',0,'intval');
            $tax_rate = I('post.tax_rate',0,'intval');
            $pay_money = I('post.pay_money','','trim');
            $pay_time = I('post.pay_time','','trim');
            $sale_ids = I('post.sale_ids');
            $pay_image = '';
            if(!empty($pay_media_id)){
                $m_media = new \Admin\Model\MediaModel();
                $media_info = $m_media->getMediaInfoById($pay_media_id);
                $pay_image  = $media_info['oss_path'];
            }
            $userInfo = session('sysUserInfo');
            $sysuser_id = $userInfo['id'];
            $data = array('payer_name'=>$payer_name,'payer_account'=>$payer_account,'pay_image'=>$pay_image,'tax_rate'=>$tax_rate,
                'pay_money'=>$pay_money,'pay_time'=>$pay_time,'sysuser_id'=>$sysuser_id
            );
            if(!empty($id)){
                $res_info = $m_salepayment->getInfo(array('id'=>$id));
                if(!empty($res_info['sale_ids'])){
                    $old_sale_ids = trim($res_info['sale_ids'],',');
                    $m_sale->updateData(array('id'=>array('in',$old_sale_ids)),array('status'=>0,'sale_payment_id'=>0));
                }
                if(!empty($sale_ids)){
                    $sale_ids = join(',',$sale_ids);
                    $data['sale_ids'] = ",$sale_ids,";
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
            $fileds = "a.id,a.idcode,hotel.name hotel_name,a.add_time,a.sale_payment_id";
            $where = array('record.wo_status'=>2);
            $all_sales = $m_sale->getList($fileds,$where,'a.id desc', 0,0);
            foreach ($all_sales as $v){
                $is_select = '';
                if($id>0 && $v['sale_payment_id']==$id){
                    $is_select = 'selected';
                }
                $v['is_select'] = $is_select;
                $all_sales[]=$v;
            }
            $vinfo = array('tax_rate'=>13);
            if($id){
                $vinfo = $m_salepayment->getInfo(array('id'=>$id));
            }
            $this->assign('vinfo',$vinfo);
            $this->assign('all_sales',$all_sales);
            $this->display();
        }
    }

}