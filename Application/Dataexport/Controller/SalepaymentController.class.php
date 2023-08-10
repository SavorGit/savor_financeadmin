<?php
namespace Dataexport\Controller;

class SalepaymentController extends BaseController {

    public function datalist(){
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        $where = array();
        if(!empty($start_time) && !empty($end_time)){
            $now_start_time = date('Y-m-d',strtotime($start_time));
            $now_end_time = date('Y-m-d',strtotime($end_time));
            $where['a.pay_time'] = array(array('egt',$now_start_time),array('elt',$now_end_time));
        }

        $m_salepayment = new \Admin\Model\SalePaymentModel();
        $res_list = $m_salepayment->getList('a.*,hotel.name as hotel_name',$where,'a.id desc');
        $m_paymentrecord = new \Admin\Model\SalePaymentRecordModel();
        $data_list = array();
        foreach ($res_list as $v){
            $res_idcodes = $m_paymentrecord->getList('a.*,sale.idcode',array('a.sale_payment_id'=>$v['id']),'a.id desc');
            foreach ($res_idcodes as $iv){
                $idcode = $iv['idcode'];
                if(is_numeric($idcode)){
                    $idcode = "'$idcode";
                }
                $data_list[]=array('id'=>$v['id'],'serial_number'=>$v['serial_number'],'hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],
                    'tax_rate'=>$v['tax_rate'],'pay_time'=>$v['pay_time'],'idcode'=>$idcode,'idcode_pay_money'=>$iv['pay_money']
                );
            }
        }
        $cell = array(
            array('id','ID'),
            array('serial_number','收款单标识码'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('tax_rate','税率(%)'),
            array('idcode_pay_money','收款金额'),
            array('pay_time','收款时间'),
            array('idcode','唯一识别码'),
        );
        $filename = '收款列表';
        $this->exportToExcel($cell,$data_list,$filename,1);
    }

    public function residentage(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_time = "$start_date 00:00:00";
        $end_time = "$end_date 23:59:59";
        $all_citys = array('1'=>'北京','9'=>'上海','236'=>'广州','246'=>'深圳','248'=>'佛山');

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

            $datalist[]=array('area_id'=>$area_id,'area_name'=>$all_citys[$area_id],'residenter_id'=>$v['residenter_id'],'residenter_name'=>$v['residenter_name'],
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
        $this->exportToExcel($cell,$datalist,$filename,1);
    }

    public function hotelage(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_time = "$start_date 00:00:00";
        $end_time = "$end_date 23:59:59";

        $all_citys = array('1'=>'北京','9'=>'上海','236'=>'广州','246'=>'深圳','248'=>'佛山');
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

            $datalist[]=array('area_id'=>$area_id,'area_name'=>$all_citys[$area_id],'hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],
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
        $this->exportToExcel($cell,$datalist,$filename,1);
    }
}