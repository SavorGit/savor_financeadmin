<?php
namespace Admin\Controller;

class SaleissueController extends BaseController {
    private $required_arr = array(
        'type'=>'请选择售卖类型','idcode'=>'请填写商品识别码',
    );
    
    public function __construct() {
        parent::__construct();
        
    }

    public function index(){
        $size   = I('numPerPage',50);//显示每页记录数
        $pageNum = I('pageNum',1);
        $order = I('_order','a.id');
        $sort = I('_sort','desc');
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $type       = I('type',0,'intval');
        $ptype       = I('ptype',99,'intval');
        $idcode     = I('idcode','','trim');

        $orders = $order.' '.$sort;
        $start  = ($pageNum-1) * $size;
        $where  = array();
        if(empty($start_date) || empty($end_date)){
            $start_date = date('Y-m-d',strtotime("-1 month"));
            $end_date = date('Y-m-d');
        }
        $where['a.add_time']= array(array('EGT',$start_date.' 00:00:00'),array('ELT',$end_date.' 23:59:59'));
        $where['a.hotel_id'] = array('not in',C('TEST_HOTEL'));
        if(!empty($type)){
            $where['a.type'] = $type;
        }
        if($ptype!=99){
            $where['a.ptype'] = $ptype;
        }
        if(!empty($idcode)){
            $where['a.idcode'] = $idcode;
        }
        $all_types = C('SALE_TYPES');
        $all_stock_types = C('STOCK_USE_TYPE');
        $all_status = C('PAY_STATUS');
        $all_wo_status = C('STOCK_WRITEOFF_STATUS');
        $all_ptype = C('PAY_TYPE');
        $m_sale = new \Admin\Model\SaleModel();
        $fileds = "a.id,a.settlement_price,goods.name goods_name,a.idcode,hotel.id as hotel_id,hotel.name hotel_name,a.add_time,a.type,a.ptype,a.status,record.wo_status";
        $result = $m_sale->getList($fileds,$where, $orders, $start,$size);
        $datalist = $result['list'];
        foreach ($datalist as $k=>$v){
            $status_str = '';
            if(isset($all_status[$v['status']])){
                $status_str = $all_status[$v['status']];
            }
            $pay_type_str = '';
            if(isset($all_ptype[$v['ptype']])){
                $pay_type_str = $all_ptype[$v['ptype']];
            }
            if($v['type']==1){
                $type_str = $all_stock_types[$v['type']];
            }else{
                $type_str = $all_types[$v['type']];
            }
            $wo_status_str = $all_wo_status[$v['wo_status']];
            $datalist[$k]['status_str'] = $status_str;
            $datalist[$k]['type_str'] = $type_str;
            $datalist[$k]['pay_type_str'] = $pay_type_str;
            $datalist[$k]['wo_status_str'] = $wo_status_str;
        }

        $this->assign('list',$datalist);
        $this->assign('page',$result['page']);
        $this->assign('pageNum',$pageNum);
        $this->assign('numPerPage',$size);
        $this->assign('_order',$order);
        $this->assign('_sort',$sort);
        $this->assign('idcode',$idcode);
        $this->assign('ptype',$ptype);
        $this->assign('all_ptype',$all_ptype);
        $this->assign('all_types',$all_types);
        $this->assign('type',$type);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->display();
    }
    public function add(){
        
        $m_opuser_role = new \Admin\Model\OpuserroleModel(); 
        $fields = 'a.user_id sale_user_id,user.remark user_name';
        
        $mps = [];
        $mps['a.state'] = 1;
        $mps['user.status'] = 1;
        $mps['a.role_id'] = array('in',array(1,3));
        $mps['user.id'] = array('gt',0);
        $sale_user_list = $m_opuser_role->getAllRole($fields,$mps,'' );
        
        /*$l_c = count($user_info);
        $user_info[$l_c] = array(
            'main_id'=>0,
            'remark'=>'无',
        );*/
        
        
        //售酒餐厅
        $m_hotel = new \Admin\Model\HotelModel();
        $fields = "a.id hotel_id,a.name hotel_name";
        $where = array('a.state'=>1,'a.flag'=>0,'ext.is_salehotel'=>1);
        $hotel_list = $m_hotel->alias('a')
                              ->join('savor_hotel_ext ext on a.id = ext.hotel_id','left')
                              ->field($fields)->where($where)->select();
        $host_name = get_host_name();
        $this->assign('honame',$host_name);
        $this->assign('hotel_list',$hotel_list);
        $this->assign('sale_user_list',$sale_user_list);
        $this->display();
    }

    public function doadd(){
        if(IS_POST){
            foreach($this->required_arr as $key=>$v){
                $tmp = I('post.'.$key);
                if(empty($tmp)){
                    $this->error($v);
                    break;
                }
            }
            $settlement_price = I('post.settlement_price',0,'intval');
            $type   = I('post.type',0,'intval');
            $idcode = I('post.idcode','','trim');
            $all_idcodes = explode("\n",$idcode);
            $m_stock_record = new \Admin\Model\StockRecordModel();
            $fileds = 'a.id,a.type,a.idcode,goods.name as goods_name,goods.id goods_id,a.price as cost_price,unit.name as unit_name,
                      stock.area_id,unit.convert_type,a.wo_status,a.dstatus,a.add_time';
            $res_list = $m_stock_record->getStockRecordList($fileds,array('a.idcode'=>trim($all_idcodes[0]),'a.dstatus'=>1),'a.id desc','0,1','');
            if(empty($res_list)){
                $this->error('商品识别码异常');
            }
            if($res_list[0]['convert_type']>1){
                $this->error('商品识别码异常,请录入瓶码');
            }
            $sale_user_id =0;
            if($type==2){//团购售卖
                $sale_user_id = I('post.sale_user_id',0,'intval');//销售人员id
                if(count($all_idcodes)>1){
                    foreach ($all_idcodes as $v){
                        if(!empty($v)){
                            $res_info = $m_stock_record->getStockRecordList($fileds,array('a.idcode'=>trim($v),'a.dstatus'=>1),'a.id desc','0,1','');
                            if(empty($res_info)){
                                $this->error("商品识别码{$v}异常");
                            }
                            if($res_info[0]['convert_type']>1){
                                $this->error('商品识别码异常,请录入瓶码');
                            }
                            if($res_list[0]['goods_name']!=$res_info[0]['goods_name']){
                                $this->error("团购商品识别码,必须是同一种商品");
                            }
                        }
                    }
                }
            }

            $goods_info  = $res_list[0];
            //酒楼信息
            $hotel_id = I('post.hotel_id',0,'intval');
            $sale_openid = I('post.sale_openid','','trim');
            if(empty($settlement_price) && in_array($type,array(2,3))){
                $m_price_template_hotel = new \Admin\Model\PriceTemplateHotelModel();
                $settlement_price = $m_price_template_hotel->getHotelGoodsPrice(0,$goods_info['goods_id'],0);
            }
            //客人信息
            $guest_openid = I('post.guest_openid','','trim');
            $guest_mobile = I('post.guest_mobile','','trim');
            
            //发票信息
            $invoice_time    = I('post.invoice_time','','trim');
            $invoice_money   = I('post.invoice_money','','trim');
            $invoice_type    = I('post.invoice_type',0,'intval');
            $invoice_number  = I('post.invoice_number','','trim');
            $invoice_payname = I('post.invoice_payname','','trim');
            
            //物流信息
            $is_express      = I('post.is_express',0,'intval');
            $express_name    = I('post.express_name','','trim');
            $express_number  = I('post.express_number','','trim');

            $data = [];
            $data['type']              = $type;                                 //售卖类型
            $data['goods_id']          = $goods_info['goods_id'];               //商品id
            $data['idcode']            = $idcode;                               //商品唯一识别码
            $data['cost_price']        = abs($goods_info['cost_price']);        //商品成本价
            $data['hotel_id']          = $hotel_id;                             //酒楼id
            $data['sale_openid']       = $sale_openid;                          //销售经理openid
            if($type==2){
                $data['maintainer_id'] = $sale_user_id;
                $data['area_id'] = $goods_info['area_id'];
            }
            if(!empty($settlement_price)){
                $data['settlement_price'] = $settlement_price;
            }
            $data['guest_openid']      = $guest_openid;                         //客人openid
            $data['guest_mobile']      = $guest_mobile;                         //客人手机号
            $data['invoice_time ']     = !empty($invoice_time) ?$invoice_time : '0000-00-00 00:00:00'; //开票时间
            $data['invoice_money']     = !empty($invoice_money) ?$invoice_money:0; //开票金额
            $data['invoice_type']      = $invoice_type;                         //发票类型
            $data['invoice_number']    = $invoice_number;                       //发票编号
            $data['invoice_payname']   = $invoice_payname;                      //付款方名称
            $data['is_express ']       = $is_express ;                          //是否需要快递
            $data['express_name']      = $express_name;                         //快递名称
            $data['express_number']    = $express_number;                       //快递编号
            $data['add_time']          = date('Y-m-d H:i:s');
            $m_sale = new \Admin\Model\SaleModel();
            /*
            $index_voucher_no = 10001;
            $res_data = $m_sale->getAll($field='id,jd_voucher_no','',0,1,'id desc');
            if(!empty($res_data[0]['jd_voucher_no'])){
                $jd_voucher_no = $res_data[0]['jd_voucher_no']+1;
            }else{
                $jd_voucher_no = $index_voucher_no;
            }
            $data['jd_voucher_no'] = $index_voucher_no;
            */

            $ret = $m_sale->addData($data);
            if($ret){
                $this->output('添加成功!', 'saleissue/index');
            }else{
                $this->error('添加失败');
            }
        }
    }

    public function getSalelist(){
        $hotel_id = I('post.hotel_id',0,'intval');
        if(!empty($hotel_id)){
            $m_staff = new \Admin\Model\StaffModel();
            $fields = 'user.nickName nickname,a.openid';
            $where = array('merchant.hotel_id'=>$hotel_id,'a.level'=>array('in',array('1','2')),'a.status'=>1);
            $staff_list = $m_staff->getMerchantStaff($fields,$where);
            $msg = '';
            $res = array('code'=>1,'msg'=>$msg,'data'=>$staff_list);
            echo json_encode($res);
        }
    }

    public function edit(){
        $id = I('get.id',0,'intval');
        $m_sale = new \Admin\Model\SaleModel();
        
        $info = $m_sale->where(array('id'=>$id))->find();
        if($info['invoice_time']=='0000-00-00 00:00:00'){
            $info['invoice_time'] = '';
        }
        $staff_list = array();
        if(!empty($info['hotel_id'])){
            $m_staff = new \Admin\Model\StaffModel();
            $fields = 'user.nickName nickname,a.openid';
            $where = array('merchant.hotel_id'=>$info['hotel_id'],'a.level'=>array('in','1,2'),'a.status'=>1);
            $staff_list = $m_staff->getMerchantStaff($fields,$where);
        }
        //售酒餐厅
        $m_hotel = new \Admin\Model\HotelModel();
        $fields = 'a.id hotel_id,a.name hotel_name';
        $where = array('a.state'=>1,'a.flag'=>0,'ext.is_salehotel'=>1);
        $hotel_list = $m_hotel->alias('a')
                              ->join('savor_hotel_ext ext on a.id = ext.hotel_id','left')
                              ->field($fields)->where($where)->select();
        $host_name = get_host_name();
        $pay_info = array();
        if($info['sale_payment_id']){
            $m_salepayment = new \Admin\Model\SalePaymentModel();
            $pay_info = $m_salepayment->getInfo(array('id'=>$info['sale_payment_id']));

            $m_paymentrecord = new \Admin\Model\SalePaymentRecordModel();
            $res_money = $m_paymentrecord->getAllData('sum(pay_money) as all_pay_money',array('sale_id'=>$id));
            $pay_info['pay_money'] = intval($res_money[0]['all_pay_money']);
        }
        
        $m_opuser_role = new \Admin\Model\OpuserroleModel();
        $fields = 'a.user_id sale_user_id,user.remark user_name';
        
        $mps = [];
        $mps['a.state'] = 1;
        $mps['user.status'] = 1;
        $mps['a.role_id'] = array('in',array(1,3));
        $mps['user.id'] = array('gt',0);
        $sale_user_list = $m_opuser_role->getAllRole($fields,$mps,'' );
        
        
        $this->assign('honame',$host_name);
        $this->assign('hotel_list',$hotel_list);
        $this->assign('staff_list',$staff_list);
        $this->assign('vinfo',$info);
        $this->assign('pay_info',$pay_info);
        $this->assign('sale_user_list',$sale_user_list);
        $this->display();
    }
    public function doedit(){
        $id = I('post.id',0,'intval');
        if(IS_POST){
            foreach($this->required_arr as $key=>$v){
                $tmp = I('post.'.$key);
                if(empty($tmp)){
                    $this->error($v);
                    break;
                }
            }
            $type   = I('post.type',0,'intval');
            $idcode = I('post.idcode','','trim');
            $all_idcodes = explode("\n",$idcode);
            $m_stock_record = new \Admin\Model\StockRecordModel();
            $fileds = 'a.id,a.type,a.idcode,goods.name as goods_name,goods.id goods_id,a.price as cost_price,unit.name as unit_name,
                      stock.area_id,unit.convert_type,a.wo_status,a.dstatus,a.add_time';
            $res_list = $m_stock_record->getStockRecordList($fileds,array('a.idcode'=>trim($all_idcodes[0]),'a.dstatus'=>1),'a.id desc','0,1','');
            if(empty($res_list)){
                $this->error('商品识别码异常');
            }
            if($res_list[0]['convert_type']>1){
                $this->error('商品识别码异常,请录入瓶码');
            }
            $sale_user_id =0;
            if($type==2){//团购售卖
                $sale_user_id = I('post.sale_user_id',0,'intval');//销售人员id
                if(count($all_idcodes)>1){
                    foreach ($all_idcodes as $v){
                        if(!empty($v)){
                            $res_info = $m_stock_record->getStockRecordList($fileds,array('a.idcode'=>trim($v),'a.dstatus'=>1),'a.id desc','0,1','');
                            if(empty($res_info)){
                                $this->error("商品识别码{$v}异常");
                            }
                            if($res_info[0]['convert_type']>1){
                                $this->error('商品识别码异常,请录入瓶码');
                            }
                            if($res_list[0]['goods_name']!=$res_info[0]['goods_name']){
                                $this->error("团购商品识别码,必须是同一种商品");
                            }
                        }
                    }
                }
            }

            $goods_info  = $res_list[0];
            //酒楼信息
            $hotel_id = I('post.hotel_id',0,'intval');
            $sale_openid = I('post.sale_openid','','trim');
            $settlement_price = I('post.settlement_price');
            if(empty($settlement_price) && in_array($type,array(2,3))){
                $m_price_template_hotel = new \Admin\Model\PriceTemplateHotelModel();
                $settlement_price = $m_price_template_hotel->getHotelGoodsPrice(0,$goods_info['goods_id'],0);
            }
            //客人信息
            $guest_openid = I('post.guest_openid','','trim');
            $guest_mobile = I('post.guest_mobile','','trim');
            
            //发票信息
            $invoice_time    = I('post.invoice_time','','trim');
            $invoice_money   = I('post.invoice_money','','trim');
            $invoice_type    = I('post.invoice_type',0,'intval');
            $invoice_number  = I('post.invoice_number','','trim');
            $invoice_payname = I('post.invoice_payname','','trim');
            
            //物流信息
            $is_express      = I('post.is_express',0,'intval');
            $express_name    = I('post.express_name','','trim');
            $express_number  = I('post.express_number','','trim');

            $data = [];
            $data['type']              = $type;                                 //售卖类型
            $data['goods_id']          = $goods_info['goods_id'];               //商品id
            $data['idcode']            = $idcode;                               //商品唯一识别码
            $data['cost_price']        = abs($goods_info['cost_price']);        //商品成本价
            
            $data['hotel_id']          = $hotel_id;                             //酒楼id
            $data['sale_openid']       = $sale_openid;                          //销售经理openid
            if($type==2){
                $data['maintainer_id'] = $sale_user_id;
                $data['area_id'] = $goods_info['area_id'];
            }
            if(!empty($settlement_price)){
                $data['settlement_price'] = $settlement_price;
            }

            $data['guest_openid']      = $guest_openid;                         //客人openid
            $data['guest_mobile']      = $guest_mobile;                         //客人手机号
            $data['invoice_time ']     = !empty($invoice_time) ?$invoice_time : '0000-00-00 00:00:00'; //开票时间
            $data['invoice_money']     = !empty($invoice_money) ?$invoice_money:0; //开票金额
            $data['invoice_type']      = $invoice_type;                         //发票类型
            $data['invoice_number']    = $invoice_number;                       //发票编号
            $data['invoice_payname']   = $invoice_payname;                      //付款方名称
            $data['is_express ']       = $is_express ;                          //是否需要快递
            $data['express_name']      = $express_name;                         //快递名称
            $data['express_number']    = $express_number;                       //快递编号
            $data['edit_time']         = date('Y-m-d H:i:s');
            $m_sale = new \Admin\Model\SaleModel();
            $res_sale = $m_sale->getInfo(array('id'=>$id));
            if(in_array($res_sale['ptype'],array(1,2))){
                $this->error('当前出库单已收款，无法修改');
            }

            $ret = $m_sale->updateData(array('id'=>$id), $data);
            if($ret){
                $this->output('编辑成功!', 'saleissue/index');
            }else{
                $this->error('编辑失败!');
            }
        }
    }

    public function jddataimport(){
        if(IS_POST){
            $upload = new \Think\Upload();
            $upload->exts = array('xls','xlsx','csv');
            $upload->maxSize = 2097152;
            $upload->rootPath = $this->imgup_path();
            $upload->savePath = '';
            $upload->saveName = time().mt_rand();
            $info = $upload->upload();
            if(!$info){
                $errMsg = $upload->getError();
                $this->output($errMsg, 'saleissue/jddataimport', 0,0);
            }else{
                $file_path = $myimg = SITE_TP_PATH.'/Public/uploads/'.$info['fileup']['savepath'].$info['fileup']['savename'];
                vendor("PHPExcel.PHPExcel.IOFactory");
                vendor("PHPExcel.PHPExcel");
                $inputFileType = \PHPExcel_IOFactory::identify($file_path);
                $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($file_path);

                $sheet = $objPHPExcel->getSheet(0);
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $all_data = array();
                for ($row = 2; $row <= $highestRow; $row++){
                    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
                    $pay_time = date('Y-m-d H:i:s',strtotime($rowData[0][0]));
                    $idcode = $rowData[0][17];
                    $pay_money = $rowData[0][22];
                    if(!empty($idcode)){
                        $all_data[]=array('idcode'=>$idcode,'pay_time'=>$pay_time,'pay_money'=>$pay_money);
                    }
                }
                if(!empty($all_data)){
                    $m_sale = new \Admin\Model\SaleModel();
                    foreach ($all_data as $v){
                        $m_sale->updateData(array('idcode'=>$v['idcode']),array('status'=>2,'pay_time'=>$v['pay_time'],'pay_money'=>$v['pay_money']));
                    }
                }
                $this->output('导入成功!', 'saleissue/index');
            }
        }else{
            $this->display();
        }

    }
}