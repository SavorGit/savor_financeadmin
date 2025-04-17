<?php
namespace Dataexport\Controller;

class SalepaymentController extends BaseController {

    public function datalist(){
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $area_id = I('area_id',0,'intval');
        $where = array();
        if(!empty($start_time) && !empty($end_time)){
            $now_start_time = date('Y-m-d',strtotime($start_time));
            $now_end_time = date('Y-m-d',strtotime($end_time));
            $where['a.pay_time'] = array(array('egt',$now_start_time),array('elt',$now_end_time));
        }
        if(!empty($area_id)){
            $where['hotel.area_id'] = $area_id;
        }
        $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }
        $m_salepayment = new \Admin\Model\SalePaymentModel();
        $res_list = $m_salepayment->getList('a.*,hotel.name as hotel_name,hotel.area_id',$where,'a.id desc');
        $m_paymentrecord = new \Admin\Model\SalePaymentRecordModel();
		$m_idcode = new \Admin\Model\IdcodeModel();
        $data_list = array();
        foreach ($res_list as $v){
            $res_idcodes = $m_paymentrecord->getList('a.*,sale.idcode',array('a.sale_payment_id'=>$v['id']),'a.id desc');
            foreach ($res_idcodes as $iv){
                $idcode = $iv['idcode'];
				if(!empty($v['hotel_id'])){
					$goods_info = $m_idcode->alias('a')
										   ->field('goods.name goods_name')
										   ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
										   ->where(array('a.idcode'=>$idcode,'a.io_type'=>22))
										   ->find();
					$goods_name	= $goods_info['goods_name'];
				}else {
					$goods_name = '';
				}
				
                if(is_numeric($idcode)){
                    $idcode = "'$idcode";
                }
                $area_name = '';
                if(!empty($v['area_id'])){
                    $area_name = $area_arr[$v['area_id']]['region_name'];
                }
                $data_list[]=array('id'=>$v['id'],'serial_number'=>$v['serial_number'],'hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],'area_name'=>$area_name,
                    'tax_rate'=>$v['tax_rate'],'pay_time'=>$v['pay_time'],'idcode'=>$idcode,'idcode_pay_money'=>$iv['pay_money'],'goods_name'=>$goods_name
                );
            }
        }
        $cell = array(
            array('id','ID'),
            array('serial_number','收款单标识码'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('area_name','城市'),
            array('tax_rate','税率(%)'),
            array('idcode_pay_money','收款金额'),
            array('pay_time','收款时间'),
            array('idcode','唯一识别码'),
			array('goods_name','商品名称'),
        );
        $filename = '收款列表';
        $this->exportToExcel($cell,$data_list,$filename,1);
    }

    public function paymentdata(){
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $area_id = I('area_id',0,'intval');
        $where = array();
        if(!empty($start_time) && !empty($end_time)){
            $now_start_time = date('Y-m-d',strtotime($start_time));
            $now_end_time = date('Y-m-d',strtotime($end_time));
            $where['a.pay_time'] = array(array('egt',$now_start_time),array('elt',$now_end_time));
        }
        if(!empty($area_id)){
            $where['hotel.area_id'] = $area_id;
        }
        $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }
        $m_salepayment = new \Admin\Model\SalePaymentModel();
        $res_list = $m_salepayment->getSalePayments('a.*,hotel.name as hotel_name,hotel.area_id,sysuser.remark as residenter_name',$where,'a.id desc');

        $data_list = array();
        $all_pay_types = array('1'=>'微信','2'=>'银行','3'=>'企微');
        foreach ($res_list as $v){
            $area_name = '';
            if(!empty($v['area_id'])){
                $area_name = $area_arr[$v['area_id']]['region_name'];
            }
            $residenter_name = !empty($v['residenter_name'])?$v['residenter_name']:'';
            $pay_type_str = isset($all_pay_types[$v['pay_type']])?$all_pay_types[$v['pay_type']]:'';
            $data_list[]=array('pay_time'=>$v['pay_time'],'hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],
                'pay_type_str'=>$pay_type_str,'area_name'=>$area_name,'pay_money'=>$v['pay_money'],'residenter_name'=>$residenter_name,
            );
        }

        $cell = array(
            array('pay_time','收款日期'),
            array('pay_money','金额'),
            array('pay_type_str','收款方式'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('residenter_name','驻店人'),
            array('area_name','城市'),
        );
        $filename = '销售收款日记账';
        $this->exportToExcel($cell,$data_list,$filename,1);
    }

    public function residentage(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $cache_key = 'cronscript:finance:residentage'.$start_date.$end_date;
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
            $shell = "/opt/install/php/bin/php /application_data/web/php/savor_financeadmin/cli.php dataexport/salepayment/residentagescript/start_date/$start_date/end_date/$end_date > /tmp/null &";
            system($shell);
            $now_time = time();
            $redis->set($cache_key,$now_time,3600);
            $this->success('数据正在生成中,请稍后点击下载');
        }
    }

    public function residentagescript(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_time = "$start_date 00:00:00";
        $end_time = "$end_date 23:59:59";
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        $all_citys = array();
        foreach ($res_area as $v){
            $all_citys[$v['id']]=$v;
        }

        $m_opuser_role = new \Admin\Model\OpuserroleModel();
        $fields = 'a.manage_city,user.id as residenter_id,user.remark as residenter_name';
        $where = array('a.state'=>1,'user.status'=>1);
        $res_opusers = $m_opuser_role->getAllRole($fields,$where,'a.id desc');
        $datalist = array();
        $m_sale = new \Admin\Model\SaleModel();
        $m_sale_payment_record = new \Admin\Model\SalePaymentRecordModel();
        foreach ($res_opusers as $v){
            $area_id = $v['manage_city'];

            $salewhere = array('a.residenter_id'=>$v['residenter_id'],'a.ptype'=>1,'record.wo_reason_type'=>1,'record.wo_status'=>2);
            $salewhere['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time));
            $res_avg_paydaydata = $m_sale->alias('a')
                ->field('record.update_time as wo_time,a.idcode,a.id as sale_id,a.settlement_price')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->join('savor_hotel_ext ext on a.hotel_id=ext.hotel_id','left')
                ->where($salewhere)
                ->select();

            $hk_money = 0;
            $hk_day_money = 0;
            foreach ($res_avg_paydaydata as $apd){
                $hk_money +=$apd['settlement_price'];
                $res_payrecord = $m_sale_payment_record->getInfo(array('sale_id'=>$apd['sale_id']));
                $pay_day = round((strtotime($res_payrecord['add_time']) - strtotime($apd['wo_time']))/86400,2);
                $pay_money = $pay_day*$apd['settlement_price'];
                $hk_day_money+=$pay_money;
            }
            $pjhk_day = round($hk_day_money/$hk_money,2);

            $yszlwhere = array('a.residenter_id'=>$v['residenter_id'],'a.ptype'=>0,'record.wo_reason_type'=>1,'record.wo_status'=>2);
            $yszlwhere['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time));
            $res_yszldata = $m_sale->alias('a')
                ->field('record.update_time as wo_time,a.idcode,a.id as sale_id,a.settlement_price')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->join('savor_hotel_ext ext on a.hotel_id=ext.hotel_id','left')
                ->where($yszlwhere)
                ->select();
            $ar_day_money=$ar_money=0;
            $fx_ar_day_money = 0;
            foreach ($res_yszldata as $yszl){
                $ar_day = round((time() - strtotime($yszl['wo_time']))/86400,2);

                $ar_day_money_tmp = $ar_day*$yszl['settlement_price'];
                $ar_day_money+=$ar_day_money_tmp;
                $ar_money+=$yszl['settlement_price'];

                if($ar_day<=15){
                    $rate = 1;
                }elseif($ar_day>15 && $ar_day_money<=30){
                    $rate = 1.5;
                }else{
                    $rate = 2;
                }
                $fx_ar_day_money+=$ar_day_money_tmp*$rate;

            }
            $yszl = round($ar_day_money/$ar_money,2);
            $fx_yszl = round($fx_ar_day_money/$ar_money,2);

            $datalist[]=array('area_id'=>$area_id,'area_name'=>$all_citys[$area_id]['region_name'],'residenter_id'=>$v['residenter_id'],'residenter_name'=>$v['residenter_name'],
                'pjhk_day'=>$pjhk_day,'ar_money'=>$ar_money,'yszl'=>$yszl,'fx_yszl'=>$fx_yszl
            );
        }

        $cell = array(
            array('area_name','城市'),
            array('residenter_name','驻店人'),
            array('pjhk_day','平均回款天数'),
            array('ar_money','应收款总额'),
            array('yszl','平均账龄'),
            array('fx_yszl','有风险的平均账龄'),
        );
        $filename = '驻店回款账龄表';
        $path = $this->exportToExcel($cell,$datalist,$filename,2);
        $cache_key = 'cronscript:finance:residentage'.$start_date.$end_date;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $redis->set($cache_key,$path,3600);
    }

    public function hotelage(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $cache_key = 'cronscript:finance:hotelage'.$start_date.$end_date;
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
            $shell = "/opt/install/php/bin/php /application_data/web/php/savor_financeadmin/cli.php dataexport/salepayment/hotelagescript/start_date/$start_date/end_date/$end_date > /tmp/null &";
            system($shell);
            $now_time = time();
            $redis->set($cache_key,$now_time,3600);
            $this->success('数据正在生成中,请稍后点击下载');
        }
    }

    public function hotelagescript(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_time = "$start_date 00:00:00";
        $end_time = "$end_date 23:59:59";

        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        $all_citys = array();
        foreach ($res_area as $v){
            $all_citys[$v['id']]=$v;
        }
        $m_hotel = new \Admin\Model\HotelModel();
        $fields = 'hotel.id as hotel_id,hotel.name as hotel_name,hotel.area_id';
        $where = array('hotel.state'=>1,'hotel.flag'=>0,'ext.is_salehotel'=>1);
        $res_hotels = $m_hotel->getHotelDatas($fields,$where,'');

        $datalist = array();
        $m_sale = new \Admin\Model\SaleModel();
        $m_sale_payment_record = new \Admin\Model\SalePaymentRecordModel();
        foreach ($res_hotels as $v){
            $area_id = $v['area_id'];

            $salewhere = array('a.hotel_id'=>$v['hotel_id'],'a.ptype'=>1,'record.wo_reason_type'=>1,'record.wo_status'=>2);
            $salewhere['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time));
            $res_avg_paydaydata = $m_sale->alias('a')
                ->field('record.update_time as wo_time,a.idcode,a.id as sale_id,a.settlement_price')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->join('savor_hotel_ext ext on a.hotel_id=ext.hotel_id','left')
                ->where($salewhere)
                ->select();

            $hk_money = 0;
            $hk_day_money = 0;
            foreach ($res_avg_paydaydata as $apd){
                $hk_money +=$apd['settlement_price'];
                $res_payrecord = $m_sale_payment_record->getInfo(array('sale_id'=>$apd['sale_id']));
                $pay_day = round((strtotime($res_payrecord['add_time']) - strtotime($apd['wo_time']))/86400,2);
                $pay_money = $pay_day*$apd['settlement_price'];
                $hk_day_money+=$pay_money;
            }
            $pjhk_day = round($hk_day_money/$hk_money,2);

            $yszlwhere = array('a.hotel_id'=>$v['hotel_id'],'a.ptype'=>0,'record.wo_reason_type'=>1,'record.wo_status'=>2);
            $yszlwhere['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time));
            $res_yszldata = $m_sale->alias('a')
                ->field('record.update_time as wo_time,a.idcode,a.id as sale_id,a.settlement_price')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->join('savor_hotel_ext ext on a.hotel_id=ext.hotel_id','left')
                ->where($yszlwhere)
                ->select();
            $ar_day_money=$ar_money=0;
            $fx_ar_day_money = 0;
            foreach ($res_yszldata as $yszl){
                $ar_day = round((time() - strtotime($yszl['wo_time']))/86400,2);

                $ar_day_money_tmp = $ar_day*$yszl['settlement_price'];
                $ar_day_money+=$ar_day_money_tmp;
                $ar_money+=$yszl['settlement_price'];

                if($ar_day<=15){
                    $rate = 1;
                }elseif($ar_day>15 && $ar_day_money<=30){
                    $rate = 1.5;
                }else{
                    $rate = 2;
                }
                $fx_ar_day_money+=$ar_day_money_tmp*$rate;

            }
            $yszl = round($ar_day_money/$ar_money,2);
            $fx_yszl = round($fx_ar_day_money/$ar_money,2);

            $datalist[]=array('area_id'=>$area_id,'area_name'=>$all_citys[$area_id]['region_name'],'hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],
                'pjhk_day'=>$pjhk_day,'ar_money'=>$ar_money,'yszl'=>$yszl,'fx_yszl'=>$fx_yszl
            );
        }

        $cell = array(
            array('area_name','城市'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('pjhk_day','平均回款天数'),
            array('ar_money','应收款总额'),
            array('yszl','平均账龄'),
            array('fx_yszl','有风险的平均账龄'),
        );
        $filename = '酒楼回款账龄表';
        $path = $this->exportToExcel($cell,$datalist,$filename,2);
        $cache_key = 'cronscript:finance:hotelage'.$start_date.$end_date;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $redis->set($cache_key,$path,3600);
    }
}