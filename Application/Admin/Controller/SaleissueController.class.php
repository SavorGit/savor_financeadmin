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
        $ajaxversion   = I('ajaxversion',0,'intval');//1 版本升级酒店列表
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','a.id');
        
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where  = [];
        
        
        $m_sale = new \Admin\Model\SaleModel();
        $fileds = "a.id,goods.name goods_name,a.idcode,hotel.name hotel_name,a.add_time,case a.type
				   when 1 then '餐厅售卖'
				   when 2 then '团购售卖'
                   when 3 then '其它售卖' END AS type";
        
        $result = $m_sale->getList($fileds,$where, $orders, $start,$size);
        
        
        
        
        $this->assign('list',$result['list']);
        $this->assign('page',$result['page']);
        $this->display();
    }
    public function add(){
        
        //售酒餐厅
        $m_hotel = new \Admin\Model\HotelModel();
        $fields = "a.id hotel_id,a.name hotel_name";
        $where = [];
        $where['a.state'] = 1;
        $where['a.flag'] = 0;
        $where['ext.is_salehotel'] = 1;
        $hotel_list = $m_hotel->alias('a')
                              ->join('savor_hotel_ext ext on a.id = ext.hotel_id','left')
                              ->field($fields)->where($where)->select();
        $host_name = get_host_name();
        $this->assign('honame',$host_name);
        $this->assign('hotel_list',$hotel_list);
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
            $type   = I('post.type',0,'intval');
            
            $idcode = I('post.idcode','','trim');
            $m_stock_record = new \Admin\Model\StockRecordModel();
            $fileds = 'a.id,a.type,a.idcode,goods.name as goods_name,goods.id goods_id,goods.price as cost_price,unit.name as unit_name,
                      a.wo_status,a.dstatus,a.add_time';
            $res_list = $m_stock_record->getStockRecordList($fileds,array('a.idcode'=>$idcode,'a.dstatus'=>1),'a.id desc','0,1','');
            //echo $m_stock_record->getLastSql();exit;
            if(empty($res_list)){
                $this->error('商品识别码异常');
            }
            $goods_info  = $res_list[0];
            
            //酒楼信息
            $hotel_id = I('post.hotel_id',0,'intval');
            $sale_openid = I('post.sale_openid','','trim');
            $maintainer_id = 0;
            if(!empty($hotel_id)){
                $m_hotel = new \Admin\Model\HotelModel();
                $hotel_info = $m_hotel->getHotelById('ext.maintainer_id',array('hotel.id'=>$hotel_id));
                $maintainer_id = $hotel_info['maintainer_id'];
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
            
            //收款信息
            $payer_name      = I('post.payer_name','','trim');
            $payer_account   = I('post.payer_account','','trim');
            $pay_media_id    = I('post.pay_media_id',0,'intval');
            if(!empty($pay_media_id)){
                $m_media = new \Admin\Model\MediaModel();
                $media_info = $m_media->field('oss_addr')->where(array('id'=>$pay_media_id))->find();
                $pay_image  = $media_info['oss_addr'];
            }else{
                $pay_image  = '';
            }
            $status          = I('post.status',0,'intval');
            $tax_rate        = I('post.tax_rate',0,'intval');
            $pay_money       = I('post.pay_money','','trim');
            $pay_time        = I('post.pay_time','','trim');
            
            $data = [];
            $data['type']              = $type;                                 //售卖类型
            $data['goods_id']          = $goods_info['goods_id'];               //商品id
            $data['idcode']            = $idcode;                               //商品唯一识别码
            $data['cost_price']        = $goods_info['cost_price'];             //商品成本价
            $data['settlement_price']  = 0.0;                                   //商品成交价
            
            $data['hotel_id']          = $hotel_id;                             //酒楼id
            $data['sale_openid']       = $sale_openid;                          //销售经理openid
            $data['maintainer_id']     = $maintainer_id;                       //合作维护人id
            
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
            
            $data['payer_name']        = $payer_name;                           //付款人名称
            $data['payer_account']     = $payer_account;                        //付款人账号
            $data['pay_image']         = $pay_image;                            //付款截图凭证
            $data['status']            = $status;                               //收款状态
            $data['tax_rate']          = $tax_rate;                             //税率
            $data['pay_money']         = !empty($pay_money) ? $pay_money:0;     //收款金额
            $data['pay_time']          = !empty($pay_time) ? $pay_time :'0000-00-00 00:00:00';  //收款时间
            $data['add_time']          = date('Y-m-d H:i:s'); 
            $m_sale = new \Admin\Model\SaleModel();
            $ret  = $m_sale->addData($data);
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
            $where = [];
            $where['merchant.hotel_id'] = $hotel_id;
            $where['a.level'] = array('in',array('1','2'));
            $where['a.status'] = 1;
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
        if($info['pay_time']=='0000-00-00 00:00:00'){
            $info['pay_time'] = '';
        }
        $staff_list = [];
        if(!empty($info['hotel_id'])){
            $m_staff = new \Admin\Model\StaffModel();
            $fields = 'user.nickName nickname,a.openid';
            $where = [];
            $where['merchant.hotel_id'] = $info['hotel_id'];
            $where['a.level'] = array('in',array('1','2'));
            $where['a.status'] = 1;
            $staff_list = $m_staff->getMerchantStaff($fields,$where);
        }
        if(!empty($info['pay_image'])){
            $m_media = new \Admin\Model\MediaModel();
            $media_info = $m_media->field('id')->where(array('oss_addr'=>$info['pay_image']))->find();
            $info['pay_image'] = get_oss_host().$info['pay_image'];
            $info['pay_image_media_id'] = $media_info['id'];
            
        }
        
        //售酒餐厅
        $m_hotel = new \Admin\Model\HotelModel();
        $fields = 'a.id hotel_id,a.name hotel_name';
        $where = [];
        $where['a.state'] = 1;
        $where['a.flag'] = 0;
        $where['ext.is_salehotel'] = 1;
        $hotel_list = $m_hotel->alias('a')
                              ->join('savor_hotel_ext ext on a.id = ext.hotel_id','left')
                              ->field($fields)->where($where)->select();
        $host_name = get_host_name();
        
        $this->assign('honame',$host_name);
        $this->assign('hotel_list',$hotel_list);
        $this->assign('staff_list',$staff_list);
        $this->assign('vinfo',$info);
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
            $m_stock_record = new \Admin\Model\StockRecordModel();
            $fileds = 'a.id,a.type,a.idcode,goods.name as goods_name,goods.id goods_id,goods.price as cost_price,unit.name as unit_name,
                      a.wo_status,a.dstatus,a.add_time';
            $res_list = $m_stock_record->getStockRecordList($fileds,array('a.idcode'=>$idcode,'a.dstatus'=>1),'a.id desc','0,1','');
            //echo $m_stock_record->getLastSql();exit;
            if(empty($res_list)){
                $this->error('商品识别码异常');
            }
            $goods_info  = $res_list[0];
            
            //酒楼信息
            $hotel_id = I('post.hotel_id',0,'intval');
            $sale_openid = I('post.sale_openid','','trim');
            $maintainer_id = 0;
            if(!empty($hotel_id)){
                $m_hotel = new \Admin\Model\HotelModel();
                $hotel_info = $m_hotel->getHotelById('ext.maintainer_id',array('hotel.id'=>$hotel_id));
                $maintainer_id = $hotel_info['maintainer_id'];
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
            
            //收款信息
            $payer_name      = I('post.payer_name','','trim');
            $payer_account   = I('post.payer_account','','trim');
            $pay_media_id    = I('post.pay_media_id',0,'intval');
            if(!empty($pay_media_id)){
                $m_media = new \Admin\Model\MediaModel();
                $media_info = $m_media->field('oss_addr')->where(array('id'=>$pay_media_id))->find();
                $pay_image  = $media_info['oss_addr'];
            }else{
                $pay_image  = '';
            }
            $status          = I('post.status',0,'intval');
            $tax_rate        = I('post.tax_rate',0,'intval');
            $pay_money       = I('post.pay_money','','trim');
            $pay_time        = I('post.pay_time','','trim');
            
            $data = [];
            $data['type']              = $type;                                 //售卖类型
            $data['goods_id']          = $goods_info['goods_id'];               //商品id
            $data['idcode']            = $idcode;                               //商品唯一识别码
            $data['cost_price']        = $goods_info['cost_price'];             //商品成本价
            $data['settlement_price']  = 0.0;                                   //商品成交价
            
            $data['hotel_id']          = $hotel_id;                             //酒楼id
            $data['sale_openid']       = $sale_openid;                          //销售经理openid
            $data['maintainer_id']     = $maintainer_id;                       //合作维护人id
            
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
            
            $data['payer_name']        = $payer_name;                           //付款人名称
            $data['payer_account']     = $payer_account;                        //付款人账号
            $data['pay_image']         = $pay_image;                            //付款截图凭证
            $data['status']            = $status;                               //收款状态
            $data['tax_rate']          = $tax_rate;                             //税率
            $data['pay_money']         = !empty($pay_money) ? $pay_money:0;     //收款金额
            $data['pay_time']          = !empty($pay_time) ? $pay_time :'0000-00-00 00:00:00';  //收款时间
            
            $m_sale = new \Admin\Model\SaleModel();
            $where = [];
            $where['id'] = $id;
            $ret = $m_sale->updateData($where, $data);
            if($ret){
                $this->output('编辑成功!', 'saleissue/index');
            }else{
                $this->error('编辑失败!');
            }
        }
    }
}