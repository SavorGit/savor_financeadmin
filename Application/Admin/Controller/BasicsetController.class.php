<?php
namespace Admin\Controller;

class BasicsetController extends BaseController {

    public function categorylist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');

        $m_category = new \Admin\Model\CategoryModel();
        $where = array();
        if(!empty($keyword)){
            $where['name'] = array('like',"$keyword");
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'sort asc';
        $res_list = $m_category->getDataList('*',$where,$orderby,$start,$size);
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

    public function categoryadd(){
        $id = I('id',0,'intval');
        $m_category = new \Admin\Model\CategoryModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');
            $sort = I('post.sort',0,'intval');
            $status = I('post.status',0,'intval');

            $where = array('name'=>$name);
            if($id){
                $where['id']= array('neq',$id);
                $res_cate = $m_category->getInfo($where);
            }else{
                $res_cate = $m_category->getInfo($where);
            }
            if(!empty($res_cate)){
                $this->output('名称不能重复', "basicset/categoryadd", 2, 0);
            }

            $data = array('name'=>$name,'media_id'=>$media_id,'sort'=>$sort,'status'=>$status);
            if($id){
                $result = $m_category->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_category->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'basicset/categorylist');
            }else{
                $this->output('操作失败', 'basicset/categoryadd',2,0);
            }
        }else{
            $vinfo = array('status'=>1);
            if($id){
                $vinfo = $m_category->getInfo(array('id'=>$id));
            }
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function specificationlist(){
        $category_id = I('category_id',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');

        $m_spec = new \Admin\Model\SpecificationModel();
        $where = array('category_id'=>$category_id);
        if(!empty($keyword)){
            $where['name'] = array('like',"$keyword");
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'sort asc';
        $res_list = $m_spec->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        $all_status = C('MANGER_STATUS');
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $v['status_str'] = $all_status[$v['status']];
                $data_list[] = $v;
            }
        }
        $this->assign('category_id',$category_id);
        $this->assign('keyword',$keyword);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function specificationadd(){
        $id = I('id',0,'intval');
        $category_id = I('category_id',0,'intval');
        $m_spec = new \Admin\Model\SpecificationModel();

        if(IS_POST){
            $name = I('post.name','','trim');
            $sort = I('post.sort',0,'intval');
            $status = I('post.status',0,'intval');

            $data = array('name'=>$name,'category_id'=>$category_id,'sort'=>$sort,'status'=>$status);
            if($id){
                $result = $m_spec->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_spec->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'basicset/specificationlist');
            }else{
                $this->output('操作失败', 'basicset/specificationadd',2,0);
            }
        }else{
            $vinfo = array('status'=>1,'category_id'=>$category_id);
            if($id){
                $vinfo = $m_spec->getInfo(array('id'=>$id));
            }
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function specificationdel(){
        $id = I('get.id',0,'intval');
        $m_spec = new \Admin\Model\SpecificationModel();
        $result = $m_spec->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'basicset/specificationlist',2);
        }else{
            $this->output('操作失败', 'basicset/specificationlist',2,0);
        }
    }

    public function unitlist(){
        $category_id = I('category_id',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');

        $m_unit = new \Admin\Model\UnitModel();
        $where = array('category_id'=>$category_id);
        if(!empty($keyword)){
            $where['name'] = array('like',"$keyword");
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'sort asc';
        $res_list = $m_unit->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        $all_status = C('MANGER_STATUS');
        $all_types = C('UNIT_TYPE');
        $all_convert_types = C('UNIT_CONVERT_TYPE');
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $v['status_str'] = $all_status[$v['status']];
                $v['type_str'] = $all_types[$v['type']];
                $v['convert_type_str'] = $all_convert_types[$v['convert_type']];
                $data_list[] = $v;
            }
        }
        $this->assign('category_id',$category_id);
        $this->assign('keyword',$keyword);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function unitadd(){
        $id = I('id',0,'intval');
        $category_id = I('category_id',0,'intval');
        $m_unit = new \Admin\Model\UnitModel();

        if(IS_POST){
            $name = I('post.name','','trim');
            $type = I('post.type',0,'intval');
            $convert_type = I('post.convert_type',0,'intval');
            $sort = I('post.sort',0,'intval');
            $status = I('post.status',0,'intval');

            $data = array('name'=>$name,'category_id'=>$category_id,'type'=>$type,'convert_type'=>$convert_type,'sort'=>$sort,'status'=>$status);
            if($id){
                $result = $m_unit->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_unit->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'basicset/unitlist');
            }else{
                $this->output('操作失败', 'basicset/unitadd',2,0);
            }
        }else{
            $vinfo = array('status'=>1,'category_id'=>$category_id);
            if($id){
                $vinfo = $m_unit->getInfo(array('id'=>$id));
            }
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function unitdel(){
        $id = I('get.id',0,'intval');
        $m_unit = new \Admin\Model\UnitModel();
        $result = $m_unit->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'basicset/unitlist',2);
        }else{
            $this->output('操作失败', 'basicset/unitlist',2,0);
        }
    }

    public function departmentlist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');

        $m_department = new \Admin\Model\DepartmentModel();
        $where = array();
        if(!empty($keyword)){
            $where['name'] = array('like',"$keyword");
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'sort asc';
        $res_list = $m_department->getDataList('*',$where,$orderby,$start,$size);
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

    public function departmentadd(){
        $id = I('id',0,'intval');
        $m_department = new \Admin\Model\DepartmentModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $sort = I('post.sort',0,'intval');
            $status = I('post.status',0,'intval');

            $data = array('name'=>$name,'sort'=>$sort,'status'=>$status);
            if($id){
                $result = $m_department->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_department->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'basicset/departmentlist');
            }else{
                $this->output('操作失败', 'basicset/departmentadd',2,0);
            }
        }else{
            $vinfo = array('status'=>1);
            if($id){
                $vinfo = $m_department->getInfo(array('id'=>$id));
            }
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function departmentuserlist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');
        $department_id = I('department_id',0,'intval');

        $m_departmentuser = new \Admin\Model\DepartmentUserModel();
        $where = array('department_id'=>$department_id);
        if(!empty($keyword)){
            $where['name'] = array('like',"$keyword");
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'sort asc';
        $res_list = $m_departmentuser->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        $all_status = C('MANGER_STATUS');
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $v['status_str'] = $all_status[$v['status']];
                $data_list[] = $v;
            }
        }
        $this->assign('department_id',$department_id);
        $this->assign('keyword',$keyword);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function departmentuseradd(){
        $id = I('id',0,'intval');
        $department_id = I('department_id',0,'intval');
        $m_departmentuser = new \Admin\Model\DepartmentUserModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $status = I('post.status',0,'intval');

            $data = array('name'=>$name,'department_id'=>$department_id,'status'=>$status);
            if($id){
                $result = $m_departmentuser->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_departmentuser->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'basicset/departmentuserlist');
            }else{
                $this->output('操作失败', 'basicset/departmentuseradd',2,0);
            }
        }else{
            $vinfo = array('status'=>1,'department_id'=>$department_id);
            if($id){
                $vinfo = $m_departmentuser->getInfo(array('id'=>$id));
            }
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function brandlist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');

        $m_brand = new \Admin\Model\BrandModel();
        $where = array();
        if(!empty($keyword)){
            $where['name'] = array('like',"$keyword");
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'sort asc';
        $res_list = $m_brand->getDataList('*',$where,$orderby,$start,$size);
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

    public function brandadd(){
        $id = I('id',0,'intval');
        $m_brand = new \Admin\Model\BrandModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $sort = I('post.sort',0,'intval');
            $status = I('post.status',0,'intval');

            $data = array('name'=>$name,'sort'=>$sort,'status'=>$status);
            if($id){
                $result = $m_brand->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_brand->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'basicset/brandlist');
            }else{
                $this->output('操作失败', 'basicset/brandadd',2,0);
            }
        }else{
            $vinfo = array('status'=>1);
            if($id){
                $vinfo = $m_brand->getInfo(array('id'=>$id));
            }
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function serieslist(){
        $brand_id = I('brand_id',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keyword = I('keyword','','trim');

        $m_series = new \Admin\Model\SeriesModel();
        $where = array('brand_id'=>$brand_id);
        if(!empty($keyword)){
            $where['name'] = array('like',"$keyword");
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'sort asc';
        $res_list = $m_series->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        $all_status = C('MANGER_STATUS');
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $v['status_str'] = $all_status[$v['status']];
                $data_list[] = $v;
            }
        }
        $this->assign('brand_id',$brand_id);
        $this->assign('keyword',$keyword);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function seriesadd(){
        $id = I('id',0,'intval');
        $brand_id = I('brand_id',0,'intval');
        $m_series = new \Admin\Model\SeriesModel();

        if(IS_POST){
            $name = I('post.name','','trim');
            $brand_id = I('post.brand_id',0,'intval');
            $sort = I('post.sort',0,'intval');
            $status = I('post.status',0,'intval');

            $data = array('name'=>$name,'brand_id'=>$brand_id,'sort'=>$sort,'status'=>$status);
            if($id){
                $result = $m_series->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_series->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'basicset/serieslist');
            }else{
                $this->output('操作失败', 'basicset/seriesadd',2,0);
            }
        }else{
            $vinfo = array('status'=>1,'brand_id'=>$brand_id);
            if($id){
                $vinfo = $m_series->getInfo(array('id'=>$id));
            }
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function seriesdel(){
        $id = I('get.id',0,'intval');
        $m_series = new \Admin\Model\SeriesModel();
        $result = $m_series->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'basicset/serieslist',2);
        }else{
            $this->output('操作失败', 'basicset/serieslist',2,0);
        }
    }

    public function getUserByDepartmentId(){
        $department_id = I('department_id',0,'intval');
        $department_user_id = I('department_user_id',0,'intval');

        $m_department_user = new \Admin\Model\DepartmentUserModel();
        $where = array('status'=>1);
        if($department_id){
            $where['department_id'] = $department_id;
        }
        $res_department_users = $m_department_user->getAll('id,name',$where,0,10000,'id asc');
        $users = array();
        foreach ($res_department_users as $v){
            $is_select = '';
            if($v['id']==$department_user_id){
                $is_select = 'selected';
            }
            $v['is_select'] = $is_select;
            $users[]=$v;
        }
        $data = array('users'=>$users);
        die(json_encode($data));
    }

}