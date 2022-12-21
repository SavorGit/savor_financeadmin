<?php
namespace Admin\Controller;

class SaleissueController extends BaseController {
    private $required_arr = array(
        'idcode'=>'请填写商品识别码',
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
        $fileds = "a.id";
        
        $result = $m_sale->getList($fileds,$where, $orders, $start,$size);
        
        
        
        
        $this->assign('list',$result['list']);
        $this->assign('page',$result['page']);
        $this->display();
    }
    public function add(){
        
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
                $m_media = new \admin\Model\MediaModel();
                $media_info = $m_media->field('oss_addr')->where(array('id'=>$pay_media_id))->find();
                $pay_image  = $media_info['oss_addr'];
            }else{
                $pay_image  = '';
            }
            $status          = I('post.status',0,'intval');
            $pay_money       = I('post.pay_money','','trim');
            $pay_time        = I('post.pay_time','','trim');
            
            $data = [];
            $data['goods_id']          = $goods_info['goods_id'];               //商品id
            $data['idcode']            = $idcode;                               //商品唯一识别码
            $data['cost_price']        = $goods_info['cost_price'];             //商品成本价
            $data['settlement_price']  = $goods_info['settlement_price'];       //商品成交价
            
            $data['hotel_id']          = $hotel_id;                             //酒楼id
            $data['sale_openid']       = $sale_openid;                          //销售经理openid
            
            $data['guest_openid']      = $guest_openid;                         //客人openid
            $data['guest_mobile']      = $guest_mobile;                         //客人手机号
             
            $data['payer_name']        = $payer_name;                           //付款人名称
            $data['payer_account']     = $payer_account;                        //付款人账号
            $data['pay_image']         = $pay_image;                            //付款截图凭证
            $data['status']            = $status;                               //收款状态
            $data['pay_money']         = $pay_money;                            //收款金额
            $data['pay_time']          = $pay_time;                             //收款时间
             
            $m_sale = new \Admin\Model\SaleModel();
            $ret  = $m_sale->add($data);
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
}