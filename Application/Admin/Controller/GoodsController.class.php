<?php
namespace Admin\Controller;

class GoodsController extends BaseController {

    public function datalist(){
        $category_id = I('category_id',0,'intval');
        $status = I('status',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');

        $m_goods = new \Admin\Model\GoodsModel();
        $where = array();
        if(!empty($keyword)){
            $where['name'] = array('like',"%$keyword%");
        }
        if($category_id){
            $where['category_id']=$category_id;
        }
        if($status){
            $where['status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_goods->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();

        $m_category = new \Admin\Model\CategoryModel();
        $all_category = $m_category->getDataList('id,name',array('status'=>1),'id desc');
        $categorys = array();
        foreach ($all_category as $v){
            $categorys[$v['id']] = $v;
        }

        $m_brand = new \Admin\Model\BrandModel();
        $all_brand = $m_brand->getDataList('id,name',array('status'=>1),'id desc');
        $brands = array();
        foreach ($all_brand as $v){
            $brands[$v['id']]=$v;
        }
        $all_status = C('MANGER_STATUS');
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $v['status_str'] = $all_status[$v['status']];
                $v['brand'] = $brands[$v['brand_id']]['name'];
                $v['category'] = $categorys[$v['category_id']]['name'];
                $data_list[] = $v;
            }
        }
        $this->assign('categorys',$categorys);
        $this->assign('status',$status);
        $this->assign('category_id',$category_id);
        $this->assign('brands',$brands);
        $this->assign('keyword',$keyword);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function goodsconfiglist(){
        $goods_id = I('goods_id',0,'intval');
        $type = I('type',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $m_goods_config = new \Admin\Model\GoodsConfigModel();
        $where = array('goods_id'=>$goods_id);
        if($type){
            $where['type'] = $type;
        }else{
            $where['type'] = array('in',array(1,2,3,20,21));
        }
        $all_types = C('STOCK_REASON');
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_goods_config->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $is_required_str = '否';
                if($v['is_required']){
                    $is_required_str = '是';
                }
                $v['is_required_str'] = $is_required_str;
                $status_str = '禁用';
                if($v['status']==1){
                    $status_str = '正常';
                }
                $v['status_str'] = $status_str;
                $v['type_str'] = $all_types[$v['type']]['name'];
                $data_list[] = $v;
            }
        }
        $this->assign('goods_id',$goods_id);
        $this->assign('type',$type);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }


    public function goodsconfigadd(){
        $id = I('id',0,'intval');
        $goods_id = I('goods_id',0,'intval');
        $m_goods_config = new \Admin\Model\GoodsConfigModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');
            $is_required = I('post.is_required',0,'intval');
            $status = I('post.status',0,'intval');
            $type = I('post.type',1,'intval');
            if($type==20){
                if($media_id==0){
                    $this->output('请上传实物图片', 'goods/goodsconfigadd',2,0);
                }
            }
            $data = array('name'=>$name,'goods_id'=>$goods_id,'media_id'=>$media_id,'is_required'=>$is_required,'status'=>$status,'type'=>$type);
            if($id){
                $result = $m_goods_config->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_goods_config->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'goods/goodsconfiglist');
            }else{
                $this->output('操作失败', 'goods/goodsconfigadd',2,0);
            }
        }else{
            $vinfo = array('status'=>1,'is_required'=>1,'goods_id'=>$goods_id,'type'=>1);
            if($id){
                $vinfo = $m_goods_config->getInfo(array('id'=>$id));
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

    public function goodsadd(){
        $id = I('id',0,'intval');
        $m_goods = new \Admin\Model\GoodsModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $price = I('post.price','','trim');
            $barcode = I('post.barcode','','trim');
            $category_id = I('post.category_id',0,'intval');
            $specification_id = I('post.specification_id',0,'intval');
            $unit_id = I('post.unit_id',0,'intval');
            $supplier_id = I('post.supplier_id',0,'intval');
            $brand_id = I('post.brand_id',0,'intval');
            $series_id = I('post.series_id',0,'intval');
            $desc = I('post.desc','','trim');
            $media_id = I('post.media_id',0,'intval');
            $status = I('post.status',0,'intval');
            $link_type = I('post.link_type',0,'intval');

            $data = array('name'=>$name,'price'=>$price,'barcode'=>$barcode,'category_id'=>$category_id,'specification_id'=>$specification_id,
                'unit_id'=>$unit_id,'supplier_id'=>$supplier_id,'brand_id'=>$brand_id,'series_id'=>$series_id,
                'desc'=>$desc,'media_id'=>$media_id,'status'=>$status,'link_type'=>$link_type);
            if($id){
                $result = $m_goods->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_goods->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'goods/datalist');
            }else{
                $this->output('操作失败', 'goods/goodsadd',2,0);
            }
        }else{
            $vinfo = array('status'=>1);
            if($id){
                $vinfo = $m_goods->getInfo(array('id'=>$id));
                if($vinfo['media_id']){
                    $m_media = new \Admin\Model\MediaModel();
                    $res_media = $m_media->getMediaInfoById($vinfo['media_id']);
                    $vinfo['oss_addr'] = $res_media['oss_addr'];
                }
            }
            $m_category = new \Admin\Model\CategoryModel();
            $all_category = $m_category->getDataList('id,name',array('status'=>1),'id desc');
            $m_brand = new \Admin\Model\BrandModel();
            $all_brand = $m_brand->getDataList('id,name',array('status'=>1),'id desc');
            $m_supplier = new \Admin\Model\SupplierModel();
            $all_supplier = $m_supplier->getDataList('id,name',array('status'=>1),'id desc');
            $this->assign('all_category',$all_category);
            $this->assign('all_brand',$all_brand);
            $this->assign('all_supplier',$all_supplier);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function goodsintegral(){
        $goods_id = I('goods_id',0,'intval');
        $m_goods_config = new \Admin\Model\GoodsConfigModel();
        $res_integral = $m_goods_config->getInfo(array('goods_id'=>$goods_id,'type'=>10));
        if(IS_POST){
            $integral = I('post.integral',0,'intval');
            $open_integral = I('post.open_integral',0,'intval');
            $media_id = I('post.media_id',0,'intval');
            $area_ids = I('post.area_ids','');

            if($open_integral>0){
                if($media_id==0){
                    $this->output('请上传实物图片', 'goods/goodsintegral',2,0);
                }
                if(empty($area_ids)){
                    $this->output('请选择城市', 'goods/goodsintegral',2,0);
                }
            }
            $data = array('goods_id'=>$goods_id,'integral'=>$integral,'open_integral'=>$open_integral,'media_id'=>$media_id,'type'=>10,'status'=>1);
            $open_area_ids = '';
            if(!empty($area_ids)){
                $open_area_ids = join(',',$area_ids);
            }
            $data['open_area_ids'] = $open_area_ids;
            if(!empty($res_integral)){
                $result = $m_goods_config->updateData(array('id'=>$res_integral['id']),$data);
            }else{
                $result = $m_goods_config->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'goods/datalist');
            }else{
                $this->output('操作失败', 'goods/goodsadd',2,0);
            }
        }else{
            $vinfo = array('oss_addr'=>'','integral'=>'','open_integral'=>'');
            $m_area  = new \Admin\Model\AreaModel();
            $area_arr = $m_area->getHotelAreaList();
            $area_ids = array();
            if(!empty($res_integral)){
                $vinfo = $res_integral;
                if(!empty($res_integral['open_area_ids'])){
                    $area_ids = explode(',',$res_integral['open_area_ids']);
                }
                if($res_integral['media_id']){
                    $m_media = new \Admin\Model\MediaModel();
                    $res_media = $m_media->getMediaInfoById($res_integral['media_id']);
                    $vinfo['oss_addr'] = $res_media['oss_addr'];
                }
            }
            foreach ($area_arr as $k=>$v){
                $select_str = '';
                if(in_array($v['id'],$area_ids)){
                    $select_str = 'selected';
                }
                $area_arr[$k]['is_select'] = $select_str;
            }
            $this->assign('areas',$area_arr);
            $this->assign('vinfo',$vinfo);
            $this->assign('goods_id',$goods_id);
            $this->display();
        }
    }


    public function getAjaxOtherinfo(){
        $category_id = I('category_id',0,'intval');
        $brand_id = I('brand_id',0,'intval');
        $goods_id = I('goods_id',0,'intval');

        $specification_id = $unit_id = $series_id = 0;
        if($goods_id){
            $m_goods = new \Admin\Model\GoodsModel();
            $res_goods = $m_goods->getInfo(array('id'=>$goods_id));
            $specification_id = $res_goods['specification_id'];
            $unit_id = $res_goods['unit_id'];
            $series_id = $res_goods['series_id'];
        }
        $specifications = $units = $series = array();
        if($category_id){
            $m_spec = new \Admin\Model\SpecificationModel();
            $res_spec = $m_spec->getDataList('id,name',array('category_id'=>$category_id,'status'=>1),'id desc');
            if(!empty($res_spec)){
                foreach ($res_spec as $v){
                    $is_select = '';
                    if($v['id']==$specification_id){
                        $is_select = 'selected';
                    }
                    $v['is_select'] = $is_select;
                    $specifications[]=$v;
                }
            }
            $m_unit = new \Admin\Model\UnitModel();
            $res_unit = $m_unit->getDataList('id,name',array('category_id'=>$category_id,'status'=>1),'id desc');
            if(!empty($res_unit)){
                foreach ($res_unit as $v){
                    $is_select = '';
                    if($v['id']==$unit_id){
                        $is_select = 'selected';
                    }
                    $v['is_select'] = $is_select;
                    $units[]=$v;
                }
            }
        }
        if($brand_id){
            $m_series = new \Admin\Model\SeriesModel();
            $res_series = $m_series->getDataList('id,name',array('brand_id'=>$brand_id,'status'=>1),'id desc');
            if(!empty($res_series)){
                foreach ($res_series as $v){
                    $is_select = '';
                    if($v['id']==$series_id){
                        $is_select = 'selected';
                    }
                    $v['is_select'] = $is_select;
                    $series[]=$v;
                }
            }
        }

        $res_data = array('specifications'=>$specifications,'units'=>$units,'series'=>$series);
        die(json_encode($res_data));
    }

}