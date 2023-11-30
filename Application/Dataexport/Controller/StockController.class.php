<?php
namespace Dataexport\Controller;

class StockController extends BaseController {
    
    public function writeofflist() {
        $wo_status = I('wo_status',0,'intval');
        $area_id = I('area_id',0,'intval');
        $recycle_status = I('recycle_status',0,'intval');
        $hotel_name = I('hotel_name','','trim');
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        $where = array('a.type'=>7);
        if($wo_status){
            $where['a.wo_status'] = $wo_status;
        }
        if($recycle_status){
            $where['a.recycle_status'] = $recycle_status;
        }
        if($area_id){
            $where['hotel.area_id'] = $area_id;
        }
        if(!empty($hotel_name)){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }
        if(!empty($start_time) && !empty($end_time)){
            $now_start_time = date('Y-m-d 00:00:00',strtotime($start_time));
            $now_end_time = date('Y-m-d 23:59:59',strtotime($end_time));
            $where['a.add_time'] = array(array('egt',$now_start_time),array('elt',$now_end_time));
        }
        $fields = 'a.id,a.idcode,a.goods_id,a.op_openid,a.wo_status,a.wo_reason_type,a.recycle_status,a.add_time,goods.name,goods.specification_id,
        unit.name as unit_name,hotel.name as hotel_name,hotel.id as hotel_id,sale.settlement_price,su.remark as residenter_name';
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $res_list = $m_stock_record->getRecordList($fields,$where, 'a.id desc', 0,0);
        $data_list = array();
        if(!empty($res_list)){
            $all_wo_status = C('STOCK_WRITEOFF_STATUS');
            $all_reason = C('STOCK_USE_TYPE');
            $all_recycle_status = C('STOCK_RECYLE_STATUS');
            $m_user = new \Admin\Model\SmallappUserModel();
            foreach ($res_list as $v){
                $v['wo_reason_type_str'] = $all_reason[$v['wo_reason_type']];
                $v['wo_status_str'] = $all_wo_status[$v['wo_status']];
                $recycle_status_str = '';
                if(isset($all_recycle_status[$v['recycle_status']])){
                    $recycle_status_str = $all_recycle_status[$v['recycle_status']];
                }
                $v['recycle_status_str']=$recycle_status_str;
                $res_user = $m_user->getInfo(array('openid'=>$v['op_openid']));
                $v['username'] = $res_user['nickname'];
                $v['usermobile'] = $res_user['mobile'];
                $data_list[] = $v;
            }
        }

        $cell = array(
            array('id','ID'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('idcode','唯一识别码'),
            array('name','商品名称'),
            array('goods_id','商品编号'),
            array('unit_name','单位'),
            array('settlement_price','结算价'),
            array('wo_reason_type_str','核销原因'),
            array('wo_status_str','状态'),
            array('username','核销人'),
            array('usermobile','核销人手机号码'),
            array('add_time','核销时间'),
            array('recycle_status_str','回收状态'),
            array('residenter_name','驻店人'),
        );
        $filename = '核销管理';
        $this->exportToExcel($cell,$data_list,$filename,1);
    }

    public function hotelstocklist(){
        $area_id = I('area_id',0,'intval');
        $cache_key = 'cronscript:finance:hotelstocklist'.$area_id;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $res = $redis->get($cache_key);
        if(!empty($res)){
            if(is_numeric($res)){
                $now_time = time();
                $diff_time = $now_time - $res;
                $http = check_http();
                $jumpUrl = $http.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                $this->success("数据正在生成中(已执行{$diff_time}秒),请稍后点击下载",$jumpUrl);
            }else{
                //下载
                $file_name = $res;
                $file_path = SITE_TP_PATH.$file_name;
                $file_size = filesize($file_path);
                header("Content-type:application/octet-tream");
                header('Content-Transfer-Encoding: binary');
                header("Content-Length:$file_size");
                header("Content-Disposition:attachment;filename=".$file_name);
                @readfile($file_path);
            }
        }else{
            $shell = "/opt/install/php/bin/php /application_data/web/php/savor_financeadmin/cli.php dataexport/stock/hotelstocklistscript/area_id/$area_id > /tmp/null &";
            system($shell);
            $now_time = time();
            $redis->set($cache_key,$now_time,3600);
            $this->success('数据正在生成中,请稍后点击下载');
        }
    }

    public function hotelstocklistscript(){
        $area_id = I('area_id',0,'intval');

        $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }
        $where = array('stock.hotel_id'=>array('gt',0),'stock.type'=>20);
        $sysuserInfo = session('sysUserInfo');
        if(!in_array($sysuserInfo['id'],array(344,345,361,362,363,364))){
            $test_hotels = C('TEST_HOTEL');
            $test_hotels[]=0;
            $where['stock.hotel_id'] = array('not in',$test_hotels);
        }

        if($area_id){
            $where['stock.area_id'] = $area_id;
        }
        $fileds = 'a.goods_id,stock.area_id,goods.name,goods.barcode,cate.name as cate_name,spec.name as sepc_name,a.unit_id,unit.name as unit_name,hotel.id as hotel_id,hotel.name as hotel_name';
        $group = 'stock.hotel_id,a.goods_id,a.unit_id';
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $res_list = $m_stock_detail->getHotelStockGoods($fileds,$where,$group,0,0);
        $data_list = array();
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $m_price_template_hotel = new \Admin\Model\PriceTemplateHotelModel();
        foreach ($res_list as $v){
            $out_num = $unpack_num = $wo_num = $report_num = 0;
            $price = 0;
            $goods_id = $v['goods_id'];
            $unit_id = $v['unit_id'];
            $rfileds = 'sum(a.total_amount) as total_amount,sum(a.total_fee) as total_fee,a.type';
            $rwhere = array('stock.hotel_id'=>$v['hotel_id'],'stock.type'=>20,'stock.io_type'=>22,'a.goods_id'=>$goods_id,'a.unit_id'=>$unit_id,'a.dstatus'=>1);
            $rwhere['a.type'] = array('in',array(2,3));
            $rgroup = 'a.type';
            $res_record = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','',$rgroup);
            foreach ($res_record as $rv){
                switch ($rv['type']){
                    case 2:
                        $out_num = abs($rv['total_amount']);
                        $total_fee = abs($rv['total_fee']);
                        $price = intval($total_fee/$out_num);
                        break;
                    case 3:
                        $unpack_num = $rv['total_amount'];
                        break;
                }
            }
            $rwhere['a.type']=7;
            $rwhere['a.wo_status']= array('in',array(1,2,4));
            $res_worecord = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','','');
            if(!empty($res_worecord[0]['total_amount'])){
                $wo_num = $res_worecord[0]['total_amount'];
            }

            $rwhere['a.type']=6;
            unset($rwhere['a.wo_status']);
            $rwhere['a.status']= array('in',array(1,2));
            $res_worecord = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','','');
            if(!empty($res_worecord[0]['total_amount'])){
                $report_num = $res_worecord[0]['total_amount'];
            }

//            $stock_num = $out_num+$unpack_num+$wo_num+$report_num;
            $stock_num = $out_num+$wo_num+$report_num;
            $settlement_price = $m_price_template_hotel->getHotelGoodsPrice($v['hotel_id'],$goods_id,1);
            $v['settlement_price'] = $settlement_price;
            $v['price'] = $price;
            $v['stock_num'] = $stock_num;
            $v['total_fee'] = $price*$stock_num;
            $v['area_name'] = $area_arr[$v['area_id']]['region_name'];
            $data_list[] = $v;
        }
        $cell = array(
            array('goods_id','商品ID'),
            array('area_name','城市'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('name','商品名称'),
            array('barcode','商品条码'),
            array('cate_name','商品类型'),
            array('sepc_name','商品规格'),
            array('unit_name','单位'),
            array('stock_num','当前库存'),
            array('settlement_price','结算价'),
        );
        $filename = '酒楼库存';
        $path = $this->exportToExcel($cell,$data_list,$filename,2);
        $cache_key = 'cronscript:finance:hotelstocklist'.$area_id;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $redis->set($cache_key,$path,3600);
    }

    public function allidcodeinfo(){
        $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }
        $io_types = C('STOCK_OUT_TYPES');
        $all_wo_status = C('STOCK_WRITEOFF_STATUS');
        $all_reason = C('STOCK_USE_TYPE');

        $fileds = 'a.id,a.idcode,a.goods_id,a.op_openid,a.amount,a.total_amount,a.add_time,goods.name as goods_name,goods.barcode,unit.name as unit_name,
        hotel.id as hotel_id,hotel.name as hotel_name,stock.area_id';
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $where = array('a.type'=>1,'a.dstatus'=>1);
        $res_record = $m_stock_record->getRecordList($fileds,$where, 'a.id desc', 0,0);
        $data_list = array();
        $m_user = new \Admin\Model\SmallappUserModel();
        $m_qrcode_content = new \Admin\Model\QrcodeContentModel();
        $m_hotel = new \Admin\Model\HotelModel();
        $unpack_idcodes = array();
        $ic = 0;
        foreach ($res_record as $v){
            $area_name = $area_arr[$v['area_id']]['region_name'];
            $hotel_id = intval($v['hotel_id']);
            $hotel_name = $v['hotel_name'];
            $idcode = $v['idcode'];
            $barcode = $v['barcode'];
            $goods_name = $v['goods_name'];
            $in_time = $v['add_time'];
            $in_num = $v['total_amount'];
            $res_user = $m_user->getInfo(array('openid'=>$v['op_openid']));
            $in_username = $res_user['nickname'];

            if($in_num>$v['amount']){//整箱
                $unpack_where = array('a.idcode'=>$idcode,'a.type'=>3,'a.dstatus'=>1);
                $res_unpackrecord = $m_stock_record->getAllStock('a.id',$unpack_where,'a.id desc');
                if(empty($res_unpackrecord)){
                    $all_idcodes = array(array('idcode'=>$idcode,'type'=>1));
                }else{
                    $qrcontent = decrypt_data($idcode);
                    $qr_id = intval($qrcontent);
                    $res_allqrcode = $m_qrcode_content->getDataList('id',array('parent_id'=>$qr_id),'id asc');
                    $all_idcodes = array();
                    foreach ($res_allqrcode as $qv){
                        $qrcontent = encrypt_data($qv['id']);
                        $all_idcodes[]=array('idcode'=>$qrcontent,'type'=>2);
                    }
                    $in_num = 1;
                }
            }else{
                $all_idcodes = array(array('idcode'=>$idcode,'type'=>1));
            }
            foreach ($all_idcodes as $iv){
                $idcode = $iv['idcode'];
                if(in_array($idcode,$unpack_idcodes)){
                    continue;
                }
                if($iv['type']==2){
                    $in_where = array('idcode'=>$idcode,'type'=>1,'dstatus'=>1);
                    $res_inrecord = $m_stock_record->getAll('id',$in_where,0,1,'id asc');
                    if(!empty($res_inrecord)){
                        continue;
                    }
                    $unpack_idcodes[]=$idcode;
                }

                $out_fileds = 'a.op_openid,a.total_amount,a.add_time,stock.io_type,stock.hotel_id';
                $out_where = array('a.idcode'=>$idcode,'a.type'=>2,'a.dstatus'=>1);
                $res_outrecord = $m_stock_record->getAllStock($out_fileds,$out_where,'a.id desc');
                $out_type_str = $out_time = $out_num = $out_username = '';
                $hotel_id = 0;
                if(!empty($res_outrecord)){
                    $hotel_id = intval($res_outrecord[0]['hotel_id']);
                    if($hotel_id>0){
                        $res_hotel = $m_hotel->getInfo(array('id'=>$hotel_id));
                        $hotel_name = $res_hotel['name'];
                    }

                    $out_type_str = $io_types[$res_outrecord[0]['io_type']];
                    $out_time = $res_outrecord[0]['add_time'];
                    $out_num = abs($res_outrecord[0]['total_amount']);
                    $res_user = $m_user->getInfo(array('openid'=>$res_outrecord[0]['op_openid']));
                    $out_username = $res_user['nickname'];
                }

                $wo_time = $wo_status_str = $wo_reason_type_str = $wo_username = $wo_mobile = '';
                $wo_fields = 'a.op_openid,a.wo_status,a.wo_reason_type,a.add_time';
                $wo_where = array('a.idcode'=>$idcode,'a.type'=>7,'a.dstatus'=>1);
                $res_worecord = $m_stock_record->getAllStock($wo_fields,$wo_where,'a.id desc');
                if(!empty($res_worecord)){
                    $wo_reason_type_str = $all_reason[$res_worecord[0]['wo_reason_type']];
                    $wo_status_str = $all_wo_status[$res_worecord[0]['wo_status']];
                    $wo_time = $res_worecord[0]['add_time'];
                    $res_user = $m_user->getInfo(array('openid'=>$res_worecord[0]['op_openid']));
                    $wo_username = $res_user['nickname'];
                    $wo_mobile = $res_user['mobile'];
                }
                if($hotel_id==0){
                    $hotel_id = '';
                    $hotel_name = '';
                }
                $info = array('area_name'=>$area_name,'hotel_id'=>$hotel_id,'hotel_name'=>$hotel_name,'idcode'=>$idcode,'barcode'=>$barcode,
                    'goods_name'=>$goods_name,'out_type_str'=>$out_type_str,'in_time'=>$in_time,'in_num'=>$in_num,'in_username'=>$in_username,
                    'out_time'=>$out_time,'out_num'=>$out_num,'out_username'=>$out_username,
                    'wo_reason_type_str'=>$wo_reason_type_str,'wo_status_str'=>$wo_status_str,'wo_username'=>$wo_username,
                    'wo_mobile'=>$wo_mobile,'wo_time'=>$wo_time
                );
                echo $ic++."\n\r";
                $data_list[]=$info;
            }
        }

        $cell = array(
            array('area_name','仓库名称'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('idcode','唯一识别码'),
            array('barcode','商品条形码'),
            array('goods_name','商品名称'),
            array('out_type_str','出库类型'),
            array('in_time','入库时间'),
            array('in_num','入库数量'),
            array('in_username','入库人'),
            array('out_time','出库时间'),
            array('out_num','出库数量'),
            array('out_username','出库人'),
            array('wo_reason_type_str','核销原因'),
            array('wo_status_str','核销状态'),
            array('wo_username','核销人'),
            array('wo_mobile','核销人手机号码'),
            array('wo_time','核销时间'),
        );
        $filename = '全部唯一码数据';
        $this->exportToExcel($cell,$data_list,$filename,2);
    }
    /**
     * @desc 数据查询 入库明细导表
     */
    public function inlist(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        
        $where = [];
        $where['stock.io_date '] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        $where['a.status']       = 1;
        $where['stock.type']     = 10;
        $where['stock.io_type']  = array('in','11,12,13');
        
        
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $fields = "a.id stock_detail_id,stock.id stock_id,stock.name stock_name,stock.serial_number,stock.io_date,
                   case stock.io_type
				   when 11 then '采购入库'
                   when 12 then '调拨入库'
                   when 13 then '餐厅退回' END AS io_type,
                   case stock.status
                   when 1 then '进行中'
                   when 2 then '已完成'
                   when 3 then '已领取'
                   when 4 then '已验收' END AS status,
            
                   s.name supplier_name,goods.id goods_id,goods.barcode,goods.name goods_name,
                   unit.name u_name,area.id area_id,area.region_name,a.total_amount,a.price,a.rate";
        $result = $m_stock_detail->alias('a')
                                 ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                                 ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                 ->join('savor_area_info area on stock.area_id=area.id','left')
                                 ->join('savor_finance_supplier s on goods.supplier_id= s.id','left')
                                 ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
                                 ->field($fields)
                                 ->where($where)
                                 ->order('stock.id desc')
                                 
                                 ->select();
        
        foreach($result as $key=>$v){
            
            //数量
            $map = [];
            $map['stock_id']        = $v['stock_id'];
            $map['stock_detail_id'] = $v['stock_detail_id'];
            $map['goods_id']        = $v['goods_id'];
            $rt = $m_stock_record->field('sum(abs(total_amount)) as total_amount,sum(abs(total_fee)) as total_fee')
            ->where($map)
            ->find();
            $total_amount = $rt['total_amount'];
            
            
            $result[$key]['rate'] = !empty($v['rate']) ? ($v['rate']*100).'%':'';
            $no_rate_price = round($v['price'] / (1+$v['rate']),2); //不含税单价
            
            $rate_money = $v['price'] - $no_rate_price;
            $total_money = $v['price'] * $total_amount;
            
            
            
            $no_rate_total_money = $no_rate_price * $total_amount;
            
            $result[$key]['no_rate_price']      = $no_rate_price;
            $result[$key]['rate_money']         = $rate_money;
            $result[$key]['total_money']        = $total_money;
            $result[$key]['no_rate_total_money']= $no_rate_total_money;
        }
        $cell = array(
            array('serial_number','入库单编号'),
            array('io_date','入库日期'),
            array('io_type','入库类型'),
            array('status','状态'),
            array('supplier_name','供应商'),
            array('goods_id','商品编码'),
            array('goods_name','商品名称'),
            array('u_name','规格'),
            array('area_id','仓库编号'),
            array('region_name','仓库名称'),
            array('total_amount','数量'),
            array('price','单价'),
            array('rate','税率'),
            array('no_rate_price','不含税单价'),
            array('no_rate_total_money','不含税总金额'),
            array('rate_money','税额'),
            array('total_money','含税总金额'),
        );
        $filename = '入库明细表';
        $this->exportToExcel($cell,$result,$filename,1);
    }
    public function insummary(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        
        $order = 'stock.id desc';
        $where = [];
        $where['stock.io_date '] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        $where['a.status']       = 1;
        $where['stock.type']     = 10;
        $where['stock.io_type']  = array('in','11,12,13');
        
        $fields = 'a.goods_id,goods.barcode,goods.name goods_name,unit.name u_name,
                   brand.name brand_name,sp.name sp_name';
        
        $group = 'a.goods_id';
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $result = $m_stock_detail->alias('a')
                                 ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                                 ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                 ->join('savor_area_info area on stock.area_id=area.id','left')
                                 ->join('savor_finance_supplier s on goods.supplier_id= s.id','left')
                                 ->join('savor_finance_brand brand on goods.brand_id=brand.id','left')
                                 ->join('savor_finance_specification sp on sp.id=goods.specification_id','left')
                                 ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
                                 ->field($fields)
                                 ->where($where)
                                 ->order($order)
                                 ->group($group)
                                 ->select();
        foreach($result as $key=>$v){
            $where = [];
            $where['stock.io_date'] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
            $where['a.goods_id'] = $v['goods_id'];
            $where['a.status']       = 1;
            $where['stock.type']     = 10;
            $where['stock.io_type']  = array('in','11,12,13');
            
            $fields = 'a.id stock_detail_id,stock.id stock_id,a.total_amount,a.price,a.rate,
                       goods.id goods_id,goods.name goods_name,brand.name brand_name';
            $rts = $m_stock_detail->alias('a')
            ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
            ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
            ->join('savor_finance_brand brand on goods.brand_id=brand.id','left')
            ->field($fields)
            ->where($where)
            ->select();
            
            
            
            
            $total_amount = 0;          //数量
            $total_money = 0;           //含税总金额
            $no_rate_total_money = 0;   //不含税总金额
            
            foreach($rts as $kk=>$vv){
                //数量
                $map = [];
                $map['stock_id']        = $vv['stock_id'];
                $map['stock_detail_id'] = $vv['stock_detail_id'];
                $map['goods_id']        = $vv['goods_id'];
                $rt = $m_stock_record->field('sum(abs(total_amount)) as total_amount,sum(abs(total_fee)) as total_fee')
                ->where($map)
                ->find();
                
                
                $total_amount += $rt['total_amount'];
                
                $total_money  += $vv['price'] * $rt['total_amount'];
                $rate_money    = $vv['price'] /(1+$vv['rate']) ;
                $no_rate_total_money += $rate_money * $rt['total_amount'];
                
            }
            $result[$key]['total_amount']         = $total_amount;
            $result[$key]['total_money']          = $total_money;
            $result[$key]['no_rate_total_money']  = $no_rate_total_money;
        }
        
        $cell = array(
            
            array('goods_id','商品编码'),
            array('goods_name','商品名称'),
            array('brand_name','品牌'),
            array('sp_name','规格'),
            array('u_name','单位'),
            array('total_amount','数量'),
            
            array('no_rate_total_money','不含税总金额'),
            
            array('total_money','含税总金额'),
        );
        $filename = '入库汇总表';
        $this->exportToExcel($cell,$result,$filename,1);
    }
    /**
     * @desc 数据查询 唯一识别码跟踪
     */
    public function idcodetrack(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        $orders = 'stock.id desc';
        $where = [];
        $where['stock.io_date '] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        
        $where['stock.type']     = 10;
        $where['stock.io_type']  = array('in','11,12,13');
        
        $m_stock_detail = new \Admin\Model\StockRecordModel();
        
        $fields = 'a.idcode';
        $group  = 'a.idcode';
        $idcode_list  = $m_stock_detail->getAllStock($fields, $where, $orders,$group);
        $data_list  = [];
        $all_type = C('STOCK_RECORD_TYPE');
        $wo_status = C('STOCK_WRITEOFF_STATUS');
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $m_hotel = new \Admin\Model\HotelModel();
        
        foreach($idcode_list as $key=>$v){
            
            $idcode = $v['idcode'];
           
            $fileds = 'a.id,a.type,a.idcode,goods.id goods_id,goods.barcode,goods.name as goods_name,stock.hotel_id, hotel.name hotel_name,
                       stock.area_id,area.region_name,
                       stock.serial_number,unit.name as unit_name,a.wo_status,a.dstatus,a.add_time';
            //$res_record = $m_stock_record->getStockRecordList($fileds,array('a.idcode'=>$idcode),'a.id desc','','');
            $res_record = $m_stock_record->alias('a')
                                         ->field($fileds)
                                         ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                         ->join('savor_area_info area on stock.area_id=area.id','left')
                                         ->join('savor_hotel hotel on stock.hotel_id=hotel.id','left')
                                         ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                                         ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
                                         ->join('savor_finance_category cate on goods.category_id=cate.id','left')
                                         ->join('savor_finance_specification spec on goods.specification_id=spec.id','left')
                                         ->order('a.id desc')
                                         ->where(array('a.idcode'=>$idcode,'dstatus'=>1))
                                         ->select();
            foreach ($res_record as $v){
                $info = $v;
                /*$type_str = $all_type[$info['type']];
                if($info['type']==7){
                    $type_str.="（{$wo_status[$info['wo_status']]}）";
                }
                if($info['dstatus']==2){
                    $dstatus_str = '删除';
                }else{
                    $dstatus_str = '正常';
                }
                $hotel_name = '';
                if($info['hotel_id']>0){
                    $res_hotel = $m_hotel->getInfo(array('id'=>$info['hotel_id']));
                    $hotel_name = $res_hotel['name'];
                }*/
                $type_str = $all_type[$info['type']];
                $info['type_str'] = $type_str;
                if($info['hotel_id']){
                    $info['storage_id'] = $info['hotel_id'];
                    $info['storage_name'] = $info['hotel_name'];
                }else {
                    $info['storage_id'] = $info['area_id'];
                    $info['storage_name'] = $info['region_name'];
                }
                if($info['dstatus']==2){
                    $info['dstatus_str'] = '删除';
                }else{
                    $info['dstatus_str'] = '正常';
                }
                
                $data_list[] = $info;
            }
        }
        $cell = array(
            array('idcode','唯一识别码'),
            array('goods_id','商品编码'),
            array('goods_name','商品名称'),
            array('add_time','日期'),
            array('serial_number','单号'),
            array('storage_id','仓库编号'),
            array('storage_name','仓库名称'),
            array('dstatus_str','状态'),
            array('type_str','类型')
        );
        $filename = '唯一识别码跟踪表';
        $this->exportToExcel($cell,$data_list,$filename,1);
    }

    public function goodsiolist(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');

        $cache_key = 'cronscript:finance:goodsiolist'.$start_date.$end_date;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $res = $redis->get($cache_key);
        if(!empty($res)){
            if(is_numeric($res)){
                $now_time = time();
                $diff_time = $now_time - $res;
                $this->success("数据正在生成中(已执行{$diff_time}秒),请稍后点击下载");
            }else{
                //下载
                $file_name = $res;
                $file_path = SITE_TP_PATH.$file_name;
                $file_size = filesize($file_path);
                header("Content-type:application/octet-tream");
                header('Content-Transfer-Encoding: binary');
                header("Content-Length:$file_size");
                header("Content-Disposition:attachment;filename=".$file_name);
                @readfile($file_path);
            }
        }else{
            $shell = "/opt/install/php/bin/php /application_data/web/php/savor_financeadmin/cli.php dataexport/stock/goodsiolistscript/start_date/$start_date/end_date/$end_date > /tmp/null &";
            system($shell);
            $now_time = time();
            $redis->set($cache_key,$now_time,3600);
            $this->success('数据正在生成中,请稍后点击下载');
        }
    }

    /**
     * @desc 数据查询-商品收发明细表
     */
    public function goodsiolistscript(){
        ini_set("memory_limit","512M");
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        $order = 'stock.id desc';
        
        $where = [];
        $where['stock.io_date'] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        $where['sd.status']       = 1;
        $fields = "a.idcode,stock.id stock_id,a.stock_detail_id,a.goods_id,goods.barcode,goods.name goods_name,
                   unit.name u_name,brand.name brand_name,stock.type,
                   case stock.type
				   when 10 then '入库'
                   when 20 then '出库' END AS type_str,
                   stock.io_date,area.id area_id,area.region_name,hotel.id hotel_id, hotel.name hotel_name,unit.name unit_name";
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $result = $m_stock_record->alias('a')
        ->join('savor_finance_stock_detail sd on a.stock_detail_id=sd.id','left')
        ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
        ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
        ->join('savor_area_info area on stock.area_id=area.id','left')
        ->join('savor_hotel hotel on stock.hotel_id=hotel.id','left')
        ->join('savor_finance_supplier s on goods.supplier_id= s.id','left')
        ->join('savor_finance_brand brand on goods.brand_id=brand.id','left')
        ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
        ->field($fields)
        ->where($where)
        ->order($order)
        ->select();
        $data_list = [];
        foreach($result as $key=>$v){
            $map = [];
            $map['stock_id']        = $v['stock_id'];
            $map['stock_detail_id'] = $v['stock_detail_id'];
            $map['goods_id']        = $v['goods_id'];
            if($v['type']==10){
                $map['type'] = 1;
            }elseif($v['20']==11){
                $map['type'] = 2;
            }
            $map['dstatus']         = 1;
            $rt = $m_stock_record->field('sum(abs(total_amount)) as total_amount,sum(abs(total_fee)) as total_fee')->where($map)->find();
            $v['total_amount'] = $rt['total_amount'];
            $v['total_fee']    = $rt['total_fee'];
            if($v['hotel_id']){
                $v['storage_id'] = $v['hotel_id'];
                $v['storage_name'] = $v['hotel_name'];
                $v['storage_type'] = '前置仓';
            }else {
                $v['storage_id'] = $v['area_id'];
                $v['storage_name'] = $v['region_name'];
                $v['storage_type'] = '周转仓';
            }
            $data_list[] = $v;
        }
        $cell = array(
            array('idcode','唯一识别码'),
            array('goods_id','商品编码'),
            array('goods_name','商品名称'),
            array('type_str','类型'),
            array('io_date','日期'),
            array('serial_number','单号'),
            array('io_date','单据日期'),
            array('region_name','城市'),
            array('storage_type','仓别'),
            array('storage_id','仓库编号'),
            array('storage_name','仓库名称'),
            array('unit_name','单位'),
            array('total_amount','数量'),
            array('total_fee','成本'),
        );
        $filename = '商品收发明细表';
        $path = $this->exportToExcel($cell,$data_list,$filename,2);
        $cache_key = 'cronscript:finance:goodsiolist'.$start_date.$end_date;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $redis->set($cache_key,$path,3600);
    }


    public function outlistcost(){
        $order = 'stock.id desc';
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        
        $where = [];
        $where['stock.io_date'] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        $where['a.status']       = 1;
        $where['stock.type']     = 20;
        $fields = 'a.goods_id,goods.barcode,goods.name goods_name,unit.name u_name';
        $group = 'a.goods_id';
        $m_stock_detail = new \Admin\Model\StockDetailModel();
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $m_avg_price = new \Admin\Model\GoodsAvgpriceModel();
        $result = $m_stock_detail->alias('a')
                                ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                                ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
                                ->field($fields)
                                ->where($where)
                                ->order($order)
                                ->group($group)
                                ->select();
        $data_list = [];
        foreach($result as $key=>$v){
            $where = [];
            $where['stock.io_date'] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
            $where['a.status'] = 1;
            $where['stock.type'] = 20;
            $where['a.goods_id'] = $v['goods_id'];
            $fields = 'stock.id stock_id,a.id stock_detail_id,a.goods_id,area.id area_id,area.region_name,
                       stock.serial_number,hotel.id hotel_id,hotel.name hotel_name,unit.name unit_name';
            $rts =  $m_stock_detail->alias('a')
                                   ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                                   ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                   ->join('savor_area_info area on stock.area_id=area.id','left')
                                   ->join('savor_hotel hotel on stock.hotel_id=hotel.id','left')
                                   ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
                                   ->field($fields)
                                   ->where($where)
                                   ->order($order)
                                   ->select();
            foreach($rts as $kk=>$vv){
                $res_price = $m_avg_price->getAll('price',array('goods_id'=>$vv['goods_id']),0,1,'id desc');
                $avg_price = $res_price[0]['price'];

                $rwhere = array('a.stock_id'=>$vv['stock_id'],'a.stock_detail_id'=>$vv['stock_detail_id'],'a.goods_id'=>$vv['goods_id'],
                    'a.type'=>2,'a.dstatus'=>1);
                $rfields = 'sum(abs(a.total_amount)) as total_amount,sum(abs(a.total_fee)) as total_fee,stock.io_type';
                $rt_out = $m_stock_record->alias('a')
                    ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                    ->field($rfields)->where($rwhere)->select();
                $total_amount = intval($rt_out[0]['total_amount']);
                if($rt_out[0]['io_type']==26){
                    $total_fee = $rt_out[0]['total_fee'];
                }else{
                    $total_fee = $total_amount*$avg_price;
                }

                $vv['total_amount'] = $total_amount;
                $vv['total_fee']    = $total_fee;
                if($vv['hotel_id']){
                    $vv['storage_id'] = $vv['hotel_id'];
                    $vv['storage_name'] = $vv['hotel_name'];
                    $vv['storage_type'] = '前置仓';
                }else{
                    $vv['storage_id'] = $vv['area_id'];
                    $vv['storage_name'] = $vv['region_name'];
                    $vv['storage_type'] = '周转仓';
                }
                $data_list[] = array_merge($v,$vv);
            }
        }
        $cell = array(
            array('goods_id','商品编码'),
            array('goods_name','商品名称'),
            array('region_name','城市'),
            array('storage_type','仓别'),
            array('serial_number','单号'),
            array('storage_id','仓库编号'),
            array('storage_name','仓库名称'),
            array('unit_name','单位'),
            array('total_amount','数量'),
            array('total_fee','成本'),
        );
        $filename = '出库成本核算表';
        $this->exportToExcel($cell,$data_list,$filename,1);
        
        
    }
    /**
     * @desc 数据查询 库龄分析表
     */
    public function stockage(){
        $order = 'stock.id desc';
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        
        $where = [];
        $where['stock.io_date '] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
        $where['stock.type']     = 10;
        $where['a.dstatus']       = 1;
        $m_stock_record = new \Admin\Model\StockRecordModel();
        $fields = 'a.idcode';
        $group  = 'a.idcode';
        $idcode_list = $m_stock_record->alias('a')
                       ->join('savor_finance_stock_detail sd on a.stock_detail_id=sd.id','left')
                       ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                       ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                       ->where($where)
                       ->field($fields)
                       ->group($group)
                       ->select();
                       
       $data_list = [];
       foreach($idcode_list as $key=>$v){
           //判断是否核销了 如果核销了不要
           $map = [];
           $map['idcode'] = $v['idcode'];
           $map['type']   = 7;
           //$map['wo_status'] = array('neq',3);
           $map['dstatus']   = 1;
           $rt = $m_stock_record->field('wo_status')->where($map)->order('id desc')->find();
           
           if(!empty($rt) && $rt['wo_status']!=3){//如果被核销了
               continue;
           }
           //如果未核销
           $where = [];
           //$where['stock.io_date '] = array(array('EGT',$start_date),array('ELT',$end_date)) ;
           $where['a.idcode']       = $v['idcode'];
           //$where['stock.type']     = 10;
           $where['a.dstatus']       = 1;
           $fields = 'stock.serial_number,stock.id stock_id,a.id stock_detail_id,a.idcode,stock.io_date,stock.area_id,stock.hotel_id,
                      area.region_name,hotel.name hotel_name,goods.id goods_id,goods.barcode,
                      goods.name goods_name,unit.name unit_name';
           $order  = 'stock.id desc';
           
           $stock_info = $m_stock_record->alias('a')
                                         ->join('savor_finance_stock_detail sd on a.stock_detail_id=sd.id','left')
                                         ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                                         ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                         ->join('savor_area_info area on stock.area_id=area.id','left')
                                         ->join('savor_hotel hotel on stock.hotel_id=hotel.id','left')
                                         ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
                                         ->where($where)
                                         ->field($fields)
                                         ->order($order)
                                         ->find();
           
           $io_date = $stock_info['io_date'];
           $day_arr = $this->viewDayTime($io_date);
           
           $stock_info['days'] = $day_arr['days'];
           $stock_info['days_str'] = $day_arr['days_str'];
           if($stock_info['hotel_id']){
               $stock_info['storage_id']   = $stock_info['hotel_id'];
               $stock_info['storage_name'] = $stock_info['hotel_name'];
               $stock_info['storage_type'] = '前置仓';
               
               //数量
               $map = [];
               $map['stock_id']        = $stock_info['stock_id'];
               $map['type']            =array('in',"1,2");
               $map['dstatus']         =1;
               $rt = $m_stock_record->field('sum(abs(total_amount)) as total_amount,sum(abs(total_fee)) as total_fee')
               ->where($map)
               ->find();
               $map = [];
               $map['stock_id']        = $stock_info['stock_id'];
               
               $map['dstatus']         =1;
               $map['type']            = 7;
               $rts = $m_stock_record->field('sum(abs(total_amount)) as total_amount,sum(abs(total_fee)) as total_fee')
               ->where($map)
               ->find();
               $stock_info['total_amount'] = $rt['total_amount'] - $rts['total_amount'];
               $stock_info['total_fee']    = $rt['total_fee']    - $rts['total_fee'];
           }else {
               $stock_info['storage_id']   = $stock_info['area_id'];
               $stock_info['storage_name'] = $stock_info['region_name'];
               $stock_info['storage_type'] = '周转仓';
               
               //数量
               $map = [];
               $map['stock_id']        = $stock_info['stock_id'];
               $map['type']            =array('in',"1,2");
               $map['dstatus']         =1;
               $rt = $m_stock_record->field('sum(abs(total_amount)) as total_amount,sum(abs(total_fee)) as total_fee')
               ->where($map)
               ->find();
               $map = [];
               $map['stock_id']        = $stock_info['stock_id'];
               
               $map['dstatus']         =1;
               $map['type']            = 7;
               $rts = $m_stock_record->field('sum(abs(total_amount)) as total_amount,sum(abs(total_fee)) as total_fee')
               ->where($map)
               ->find();
               $stock_info['total_amount'] = $rt['total_amount'] - $rts['total_amount'];
               $stock_info['total_fee']    = $rt['total_fee']    - $rts['total_fee'];
           }
           
           $data_list[] = $stock_info;
       }
       $cell = array(
           array('goods_id','商品编码'),
           array('goods_name','商品名称'),
           array('idcode','商品唯一编码'),
           array('region_name','城市'),
           array('serial_number','单号'),
           
           array('storage_type','仓别'),
           array('storage_id','仓库编号'),
           array('storage_name','仓库名称'),
           array('unit_name','单位'),
           array('total_amount','库存数量'),
           array('total_fee','库存金额'),
           array('days_str','库存天数'),
       );
       $filename = '库龄分析表';
       $this->exportToExcel($cell,$data_list,$filename,1);
       
    }
    private function viewDayTime($start_date,$type=1){
        $now_date = date('Y-m-d');
        $diff_time = time() - strtotime($start_date);
        
        $days = ceil($diff_time / 86400);
        if($type==1){
            if($days>=1 && $days<=30){
                $days_str = '1-30天';
            }else if($days>=31 && $days<=60){
                $days_str = '31-60天';
            }else if($days>=61 && $days<=90){
                $days_str = '61-90天';
            }else{
                $days_str = '61-90天';
            }
        }else if($type==2){
            
        }
        return array('days'=>$days,'days_str'=>$days_str);
        
    }
}