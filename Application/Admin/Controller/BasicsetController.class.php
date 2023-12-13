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
        $where['parent_id'] = 0;
        if(!empty($keyword)){
            $where['name'] = array('like',"$keyword");
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'sort asc';
        $res_list = $m_department->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        $department_list_tree = [];
        $all_status = C('MANGER_STATUS');
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $v['status_str'] = $all_status[$v['status']];
                $data_list[] = $v;
                
                
                $department_list_tree[] = $v;
                $map = [];
                $map['status'] = 1;
                $map['parent_id'] = $v['id'];
                $f_department_list = $m_department->where($map)->select();
                if(!empty($f_department_list)){
                    foreach($f_department_list as $kk=>$vv){
                        
                        $vv['name'] = '&nbsp;&nbsp;&nbsp;&nbsp;|-'.$vv['name'];
                        $vv['status_str'] = $all_status[$vv['status']];
                        $department_list_tree[] = $vv;
                        
                        $tps = [];
                        $tps['status'] = 1;
                        $tps['parent_id'] = $vv['id'];
                        $s_department_list = $m_department->where($tps)->select();
                        
                        if(!empty($s_department_list)){
                            foreach($s_department_list as $kkk=>$vvv){
                                
                                $vvv['name'] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-".$vvv['name'];
                                $vvv['status_str'] = $all_status[$vvv['status']];
                                $department_list_tree[] = $vvv;
                                
                            }
                        }
                    }
                    
                }
            }
        }
        //print_r($department_list_tree);exit;
        $this->assign('keyword',$keyword);
        $this->assign('data',$department_list_tree);
        //$this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function departmentadd(){
        $id = I('id',0,'intval');
        
        
        $m_department = new \Admin\Model\DepartmentModel();
        $department_list_tree = [];
        if(IS_POST){
            
            $department_id = I('department_id',0,'intval');
            
            $name = I('post.name','','trim');
            $sort = I('post.sort',0,'intval');
            $status = I('post.status',0,'intval');

            $data = array('name'=>$name,'sort'=>$sort,'status'=>$status,'parent_id'=>$department_id);
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
            $department_list_tree = $this->getDepartmentTree(1);    
            
            
            $this->assign('department_list_tree',$department_list_tree);
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
            $department_list_tree = $this->getDepartmentTree(2);
            
            
            $this->assign('department_list_tree',$department_list_tree);
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

    public function getHotelSales(){
        $hotel_id = I('hotel_id',0,'intval');
        $sale_payment_id = I('sale_payment_id',0,'intval');

        $fileds = "a.id,a.idcode,hotel.name hotel_name,a.add_time,a.sale_payment_id,a.settlement_price,
        a.goods_id,goods.name as goods_name,a.sale_openid";
        $where = array('a.hotel_id'=>$hotel_id,'record.wo_status'=>2);
        $m_sale = new \Admin\Model\SaleModel();
        $all_sales = $m_sale->getList($fileds,$where,'a.id desc', 0,0);
        foreach ($all_sales as $k=>$v){
            $is_select = '';
            if($sale_payment_id>0 && $v['sale_payment_id']==$sale_payment_id){
                $is_select = 'selected';
            }
            $all_sales[$k]['is_select'] = $is_select;
            $all_sales[$k]['name'] = "{$v['id']}-{$v['add_time']}-{$v['goods_name']}-{$v['settlement_price']}";
        }
        $data = array('datalist'=>$all_sales);
        die(json_encode($data));
    }
    public function companybank(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        
        $keyword = I('keyword','','trim');
        $m_company_bank = new \Admin\Model\CompanyBankModel();
        $where = array();
        if(!empty($keyword)){
            $where['company_name'] = array('like',"%$keyword%");
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id asc';
        $res_list = $m_company_bank->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        $m_bank_type = new \Admin\Model\U8\BankTypeModel();
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $map = [];
                $map['pk_banktype'] = $v['bank_type_id'];
                $bank_type_info = $m_bank_type->getInfo($map);
                
                $v['bank_type_str'] = $bank_type_info['banktypename'];
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
    public function companybankadd(){
        $id = I('id',0,'intval');
        
        
        $m_company_bank = new \Admin\Model\CompanyBankModel();
        $department_list_tree = [];
        if(IS_POST){
            
            $id = I('id',0,'intval');
            
            $company_name = I('post.company_name','','trim');
            $bank_type_id = I('post.bank_type_id',0,'intval');
            $bank_name    = I('post.bank_name','','trim');
            $bank_branch_name = I('post.bank_branch_name','','trim');
            $account_number   = I('post.account_number','','trim');
            $status = I('post.status',1,'intval');
            
            $data = array('company_name'=>$company_name,'bank_type_id'=>$bank_type_id,'bank_name'=>$bank_name,'bank_branch_name'=>$bank_branch_name, 
                          'account_number'=>$account_number,'status'=>$status);
            if($id){
                $result = $m_company_bank->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_company_bank->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'basicset/companybank');
            }else{
                $this->output('操作失败', 'basicset/companybankadd',2,0);
            }
        }else{
            $vinfo = array('status'=>1);
            if($id){
                $vinfo = $m_company_bank->getInfo(array('id'=>$id));
            }
            $m_bank_type = new \Admin\Model\U8\BankTypeModel();
            $bank_type_arr = [];
            $bank_type_arr = $m_bank_type->getAll('id,banktypename as name');
            
            $this->assign('bank_type_arr',$bank_type_arr);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }
    public function getAjaxDepartmentUsers(){
        $department_id = I('post.department_id');
        $m_department_user = new \Admin\Model\DepartmentUserModel();
        $where = [];
        $where['department_id'] = $department_id;
        $where['status'] =1;
        $user_list = $m_department_user->getAllData('id,name',$where,'sort asc,id asc');
        $res_data = array('user_list'=>$user_list);
        echo json_encode($res_data);
    }
    
    public function companybanksyncU8(){
        
        $id = I('get.id', 0, 'int');
        $userinfo = session('sysUserInfo');
        if(!empty($id)){
            $field = 'a.*,b.banktypecode';
            $where = [];
            $where['a.id'] = $id;
            $m_company_bank = new \Admin\Model\CompanyBankModel();
            $bank_info = $m_company_bank->alias('a')
                           ->join('savor_u8_banktype b on a.bank_type_id=b.id','left')
                           ->field($field)
                           ->where($where)
                           ->find();
            
           $u8 = new \Common\Lib\U8cloud();
           $params = [];
           $data = [];
           $data['account'] = $bank_info['account_number'];
           $data['accountcode'] = $bank_info['id'];
           $data['accountname'] = $bank_info['company_name'];
           
           $data['acctype']     = '0';  //账户类型： 0活期     1协定 2定期 3通知 4保证金户
           $data['arapprop']    = '0';  //收付属性： 0收入     1支出 2收支
           $data['genebranprop']= '0';  //总分属性： 0总账户 1分账户 2独立账户 
           $data['groupaccount']= 'N';  //集团账户： N否         Y是
           
           $data['creator'] = $userinfo['id'];  //系统账号id
           $data['ownercorp'] = '1005';
           $data['pk_banktype'] = $bank_info['pk_banktype'];
           $data['pk_currtype'] = 'CNY';
           
           $params['bankaccbasvo'][]= $data;
           $u8->addBankAccountInfo($data);
        }
    }
    

}