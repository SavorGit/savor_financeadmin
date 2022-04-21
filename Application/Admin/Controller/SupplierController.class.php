<?php
namespace Admin\Controller;

class SupplierController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');

        $m_supplier = new \Admin\Model\SupplierModel();
        $where = array();
        if(!empty($keyword)){
            $where['name'] = array('like',"$keyword");
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_supplier->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        $all_status = C('MANGER_STATUS');
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $v['status_str'] = $all_status[$v['status']];
                $data_list[] = $v;
            }
        }
        $this->assign('keyword',$keyword);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function supplieradd(){
        $id = I('id',0,'intval');
        $m_supplier = new \Admin\Model\SupplierModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $contacts = I('post.contacts','','trim');
            $addr = I('post.addr','','trim');
            $mobile = I('post.mobile','','trim');
            $desc = I('post.desc','','trim');
            $media_id = I('post.media_id',0,'intval');
            $status = I('post.status',0,'intval');

            $data = array('name'=>$name,'contacts'=>$contacts,'addr'=>$addr,'mobile'=>$mobile,
                'desc'=>$desc,'media_id'=>$media_id,'status'=>$status);
            if($id){
                $result = $m_supplier->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_supplier->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'supplier/datalist');
            }else{
                $this->output('操作失败', 'supplier/supplieradd',2,0);
            }
        }else{
            $vinfo = array('status'=>1);
            if($id){
                $vinfo = $m_supplier->getInfo(array('id'=>$id));
                if($vinfo['media_id']){
                    $m_media = new \Admin\Model\MediaModel();
                    $res_media = $m_media->getMediaInfoById($vinfo['media_id']);
                    $vinfo['oss_addr'] = $res_media['oss_addr'];
                }
            }
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function supplylist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $supplier_id = I('supplier_id',0,'intval');

        $m_purchase = new \Admin\Model\PurchaseModel();
        $where = array('supplier_id'=>$supplier_id);
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_purchase->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_stock_record = new \Admin\Model\StockRecordModel();
            $m_stock = new \Admin\Model\StockModel();
            $m_department = new \Admin\Model\DepartmentModel();
            foreach ($res_list['list'] as $v){
                $res_dinfo = $m_department->getInfo(array('id'=>$v['department_id']));
                $v['department'] = $res_dinfo['name'];

                $res_stock = $m_stock->getDataList('id',array('purchase_id'=>$v['id']),'id desc');
                $now_amount = 0;
                if(!empty($res_stock)){
                    $stock_ids = array();
                    foreach ($res_stock as $sv){
                        $stock_ids[]=$sv['id'];
                    }
                    $field='sum(total_amount) as total_amount';
                    $res_stock_record = $m_stock_record->getRow($field,array('stock_id'=>array('in',$stock_ids),'type'=>1));
                    if(!empty($res_stock_record['total_amount'])){
                        $now_amount = intval($res_stock_record['total_amount']);
                    }
                }
                $v['now_amount'] = $now_amount;
                $data_list[] = $v;
            }
        }
        $this->assign('supplier_id',$supplier_id);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

}