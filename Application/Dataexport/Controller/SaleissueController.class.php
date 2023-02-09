<?php
namespace Dataexport\Controller;

    


class SaleissueController extends BaseController {
    private $sale_type_arr = array(1=>'餐厅售卖',2=>'团购售卖',3=>'其它售卖');
    private $days_range_arr = array(
        array('min'=>1,'max'=>7,'name'=>'1-7天','money'=>0), 
        array('min'=>8,'max'=>15,'name'=>'8-15天','money'=>0),
        array('min'=>16,'max'=>30,'name'=>'16-30天','money'=>0),
        array('min'=>31,'max'=>60,'name'=>'31-60天','money'=>0),
        array('min'=>61,'max'=>9999999,'name'=>'61天以上','money'=>0),
    );
    public function exportjd() {
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $type       = I('type',0,'intval');

        $where  = array('a.status'=>array('in','0,1'),'record.type'=>7,'record.wo_status'=>2);
        if(!empty($start_date) && !empty($end_date)){
            $where['a.add_time']= array(array('EGT',$start_date.' 00:00:00'),array('ELT',$end_date.' 23:59:59'));
        }
        if(!empty($type)){
            $where['a.type'] = $type;
        }
        $jd_subject_code = C('JD_SUBJECT_CODE');
        $jd_department = C('JD_CITY_DEPARTMENT');
        $m_sale = new \Admin\Model\SaleModel();
        $fileds = 'a.goods_id,a.idcode,a.settlement_price,a.hotel_id,a.maintainer_id,a.add_time,a.jd_voucher_no,ext.jd_custom_no,hotel.area_id';
        $res_data = $m_sale->getJdDataList($fileds,$where);
        $datalist = array();
        $i = 1;
        foreach ($res_data as $v){
            $data_date = date('Y/m/d',strtotime($v['add_time']));
            $jd_voucher_str = '转';
            $jd_voucher_no = $v['jd_voucher_no'];
            $jd_custom_no = $v['jd_custom_no'];
            $department = '';
            $appendix_num = 1;
            $summary = '销售应收款';
            $lb_name = '唯一识别码';
            $idcode = $v['idcode'];
            $maintainer_id = '';
            $goods_id = '';

            $rmb = 'RMB';
            $rate = 1.00;
            $gys=$ch=$lb1=$bm1=$sl=$dj='';
            foreach ($jd_subject_code as $ck=>$cv){
                $subject_code = $ck;
                $subject_name = $cv['name'];
                $num = $i++;
                $jf_money = $df_money = '';
                $money = '';
                switch ($cv['type']){
                    case 'jf':
                        $jf_money = $v['settlement_price'];
                        $money = $jf_money;
                        $maintainer_id = '';
                        $goods_id = '';
                        $department = '';
                        break;
                    case 'df':
                        $df_money = sprintf("%.2f",$v['settlement_price']/$cv['rate']);
                        $money = $df_money;
                        $jd_custom_no = '';
                        $lb_name = '';
                        $idcode = '';
                        $department = $jd_department[$v['area_id']];
                        $maintainer_id = $v['maintainer_id'];
                        $goods_id = $v['goods_id'];
                        break;
                    case 'jf-df':
                        $tmp_df_money = sprintf("%.2f",$v['settlement_price']/$cv['rate']);
                        $df_money = $v['settlement_price'] - $tmp_df_money;
                        $money = $df_money;
                        $jd_custom_no = '';
                        $maintainer_id = '';
                        $goods_id = '';
                        $lb_name = '';
                        $idcode = '';
                        $department = '';
                        break;
                }

                $datalist[]=array('data_date'=>$data_date,'jd_voucher_str'=>$jd_voucher_str,'jd_voucher_no'=>$jd_voucher_no,'appendix_num'=>$appendix_num,
                    'num'=>$num,'summary'=>$summary,'subject_code'=>$subject_code,'subject_name'=>$subject_name,'jf'=>$jf_money,
                    'df'=>$df_money,'jd_custom_no'=>$jd_custom_no,'gys'=>$gys,'maintainer_id'=>$maintainer_id,'goods_id'=>$goods_id,
                    'department'=>$department,'ch'=>$ch,'lb_name'=>$lb_name,'idcode'=>$idcode,'lb1'=>$lb1,'bm1'=>$bm1,'sl'=>$sl,
                    'dj'=>$dj,'money'=>$money,'rmb'=>$rmb,'rate'=>$rate
                );
            }
        }
        $cell = array(
            array('data_date','日期'),
            array('jd_voucher_str','凭证字'),
            array('jd_voucher_no','凭证号'),
            array('appendix_num','附件数'),
            array('num','分录序号'),
            array('summary','摘要'),
            array('subject_code','科目代码'),
            array('subject_name','科目名称'),
            array('jf','借方金额'),
            array('df','贷方金额'),
            array('jd_custom_no','客户'),
            array('gys','供应商'),
            array('maintainer_id','职员'),
            array('goods_id','项目'),
            array('department','部门'),
            array('ch','存货'),
            array('lb_name','自定义辅助核算类别'),
            array('idcode','自定义辅助核算编码'),
            array('lb1','自定义辅助核算类别1'),
            array('bm1','自定义辅助核算编码1'),
            array('sl','数量'),
            array('dj','单价'),
            array('money','原币金额'),
            array('rmb','币别'),
            array('rate','汇率'),
        );
        $filename = '系统导出金蝶系统需要数据';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }
    /**
     * @desc 数据查询  销售出库单列表
     */
    public function datalist() {
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        
        $where = [];
        $where['a.add_time'] = array(array('EGT',$start_date.' 00:00:00'),array('ELT',$end_date.' 23:59:59'));
        $orders = 'a.id desc';
        $fields = "a.add_time,a.id,case a.type when 1 then '餐厅销售' when 2 then '团购售卖' when 3 then '其他售卖' end as type,
                   a.idcode,area.region_name,a.hotel_id,hotel.name hotel_name,goods.barcode,goods.name goods_name,
                   unit.name unit_name,spe.name spe_name,a.settlement_price,a.cost_price,a.settlement_price-a.cost_price as profit ,
                   a.pay_time,a.pay_money,a.settlement_price-a.pay_money uncollected_money,a.invoice_time,a.invoice_money,sysuser.remark,user.nickName,user.name";
        $m_sale = new \Admin\Model\SaleModel();
        $data_list = $m_sale->alias('a')
        ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
        ->join('savor_finance_goods goods on a.goods_id   = goods.id','left')
        
        ->join('savor_finance_specification spe on goods.specification_id= spe.id','left')
        ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
        ->join('savor_finance_unit unit on unit.id =record.unit_id','left')
        ->join('savor_sysuser sysuser on a.maintainer_id= sysuser.id ','left')
        ->join('savor_area_info area on  area.id=hotel.area_id','left')
        ->join('savor_smallapp_user user on a.sale_openid=user.openid','left')
        ->field($fields)
        ->where($where)
        ->order($orders)
        ->select();
        foreach($data_list as $key=>$v){
            if($v['uncollected_money']==0){
                $account_days =  ceil((strtotime($v['pay_time']) - strtotime($v['add_time'])) / 86400) ;
            }else {
                $account_days =  ceil((time() - strtotime($v['add_time'])) / 86400) ;
            }
            $data_list[$key]['account'] = $account_days.'天';
            if(empty($v['name'])){
                $data_list[$key]['name'] = $v['nickname'];
            }
            $data_list[$key]['amount'] = 1;
        }
        //print_r($data_list);
        $cell = array(
            array('add_time','核销日期'),
            array('id','核销单据编号'),
            array('type','销售类型'),
            array('idcode','唯一识别码'),
            array('region_name','城市'),
            array('hotel_id','仓库编号'),
            array('hotel_name','仓库名称'),
            array('barcode','商品编码'),
            array('goods_name','商品名称'),
            array('unit_name','单位'),
            array('spe_name','规格'),
            array('amount','数量'),
            
            array('settlement_price','结算单价'),
            array('cost_price','出库成本'),
            array('profit','销售毛利'),
            array('pay_time','收款日期'),
            array('pay_money','收款金额'),
            array('uncollected_money','未收款金额'),
            array('account','账龄'),
            array('invoice_time','开票日期'),
            array('invoice_money','开票金额'),
            array('remark','业务员'),
            array('name','销售经理'),
        );
        $filename = '销售出库单列表';
        $this->exportToExcel($cell,$data_list,$filename,1);
    }
    /**
     * @desc 数据查询 销售出库单汇总表
     */
    public function datasummary(){
        
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        $orders = "a.id desc";
        $where = [];
        $where['a.add_time'] = array(array('EGT',$start_date.' 00:00:00'),array('ELT',$end_date.' 23:59:59'));
         
        $fields = "a.hotel_id,hotel.name hotel_name,area.region_name";
        $group  = "a.hotel_id";
        $m_sale = new \Admin\Model\SaleModel();
        $list =   $m_sale->alias('a')
                         ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
                         ->join('savor_area_info area on hotel.area_id= area.id','left')
                         ->field($fields)
                         ->where($where)
                         ->order($orders)
                         ->group($group)
                         ->select();
         $sale_type_arr = $this->sale_type_arr;
         $data_list = [];
         foreach($list as $key=>$v){
         
             foreach($sale_type_arr as $kk=>$vv){
                 
                 $map = [];
                 $map['a.hotel_id'] = $v['hotel_id'];
                 $map['a.add_time'] = array(array('EGT',$start_date.' 00:00:00'),array('ELT',$end_date.' 23:59:59'));
                 $map['a.type']     = $kk;
                 $ret = $m_sale->alias('a')
                               ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
                               ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                               ->field('a.hotel_id,a.goods_id,goods.name goods_name')
                               ->where($map)
                               ->group('a.goods_id')
                               ->select();
                 foreach($ret as $kkk=>$vvv){
                     $map = [];
                     $map['hotel_id'] = $v['hotel_id'];
                     $map['a.add_time'] = array(array('EGT',$start_date.' 00:00:00'),array('ELT',$end_date.' 23:59:59'));
                     $map['a.type']     = $kk;
                     $map['goods_id'] = $vvv['goods_id'];
                     $fields = 'a.cost_price,a.settlement_price';
                     $rts = $m_sale->alias('a')
                                   ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
                                   ->join('savor_area_info area on hotel.area_id= area.id','left')
                                   ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                                   ->where($map)
                                   ->field($fields)
                                   ->select();
                    if(!empty($rts)){
                        $cost_total = 0;
                        $settlement_total = 0;
                        $sale_profit = 0;
                        foreach($rts as $rk=>$rv){
                            $cost_total +=$rv['cost_price'] *1;
                            $settlement_total +=$rv['settlement_price'] *1;
                            $sale_profit = $rv['settlement_price'] - $rv['cost_price'];
                            
                        }
                        $no_rate_settlement_total = $settlement_total / 1.13;
                        $rate_settlement_total    = $settlement_total - $no_rate_settlement_total;
                        $info = [];
                        $info['type'] = $vv;
                        $info['region_name'] = $v['region_name'];
                        $info['hotel_id']    = $v['hotel_id'];
                        $info['hotel_name']  = $v['hotel_name'];
                        $info['barcode']     = $vvv['barcode'];
                        $info['goods_name']  = $vvv['goods_name'];
                        $info['total_amount']= count($rts);
                        $info['cost_total'] = $cost_total;              //出库成本
                        $info['settlement_total'] = $settlement_total;  //销售收入
                        $info['no_rate_settlement_total'] = round($no_rate_settlement_total,2);  //销售收入
                        $info['rate_settlement_total']    = round($rate_settlement_total,2);      //销售税金
                        $info['sale_profit'] = $sale_profit *$info['total_amount'] ;  //销售毛利
                        
                        $data_list[] = $info;
                    }
                 }
             }
         }
         $cell = array(
             array('type','销售类型'),
             
             array('region_name','城市'),
             array('hotel_id','仓库编号'),
             array('hotel_name','仓库名称'),
             array('goods_id','商品编码'),
             array('goods_name','商品名称'),
             array('total_amount','数量'),
             array('cost_total','出库成本'),
             array('settlement_total','销售收入'),
             array('no_rate_settlement_total','销售收入(不含税)'),
             
             array('rate_settlement_total','销售收入(税金)'),
             array('sale_profit','毛利'),
             
         );
         $filename = '销售出库单汇总表';
         $this->exportToExcel($cell,$data_list,$filename,1);
    }
    public function receivables(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        $orders = "a.id desc";
        $where = [];
        $where['a.add_time'] = array(array('EGT',$start_date.' 00:00:00'),array('ELT',$end_date.' 23:59:59'));
        
        $fields = "a.hotel_id,hotel.name hotel_name,area.region_name,user.remark";
        $group  = "a.hotel_id";
        $m_sale = new \Admin\Model\SaleModel();
        $list =   $m_sale->alias('a')
                         ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
                         ->join('savor_sysuser user on a.maintainer_id=user.id','left')
                         ->join('savor_area_info area on hotel.area_id= area.id','left')
                         ->field($fields)
                         ->where($where)
                         ->order($orders)
                         ->group($group)
                         ->select();
        foreach($list as $key=>$v){
            $map = [];
            $map['a.hotel_id'] = $v['hotel_id'];
            $map['a.add_time'] = array(array('EGT',$start_date.' 00:00:00'),array('ELT',$end_date.' 23:59:59'));
            
            $ret = $m_sale->alias('a')
                          ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
                          ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                          ->field('a.hotel_id,a.goods_id,goods.name goods_name')
                          ->where($map)
                          ->group('a.goods_id')
                          ->select();
            foreach($ret as $kkk=>$vvv){
                  $map = [];
                  $map['hotel_id'] = $v['hotel_id'];
                  $map['a.add_time'] = array(array('EGT',$start_date.' 00:00:00'),array('ELT',$end_date.' 23:59:59'));
                  
                  $map['goods_id'] = $vvv['goods_id'];
                  $fields = 'a.cost_price,a.settlement_price';
                  $rts = $m_sale->alias('a')
                                ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
                                ->join('savor_area_info area on hotel.area_id= area.id','left')
                                ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                                ->where($map)
                                ->field($fields)
                                ->select();
                  if(!empty($rts)){
                      $info = [];
                      $info['region_name'] = $v['region_name'];
                      $info['hotel_id']    = $v['hotel_id'];
                      $info['hotel_name']  = $v['hotel_name'];
                      $info['goods_id']    = $vvv['goods_id'];
                      $info['goods_name']  = $vvv['goods_name'];
                      $info['remark']      = $v['remark'];
                      $receivable_money = 0;
                      foreach($rts as $rk=>$rv){
                          $receivable_money += $rv['settlement_price'] - $rv['pay_money'];
                      }
                      $info['receivable_money'] = $receivable_money;
                      
                      $data_list[] = $info;
                  }
            }
        }//end list
        $cell = array(
            
            array('region_name','城市'),
            array('hotel_id','仓库编号'),
            array('hotel_name','仓库名称'),
            array('goods_id','商品编码'),
            array('goods_name','商品名称'),
            array('remark','业务员'),
            array('receivable_money','应收余额'),
            
        );
        $filename = '应收账款汇总表';
        $this->exportToExcel($cell,$data_list,$filename,1);
    }
    public function accountage(){
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $start_date =  !empty($start_date) ? $start_date: date('Y-m-d',strtotime('-7 days'));
        $end_date   =  !empty($end_date) ? $end_date: date('Y-m-d');
        $orders = "a.id desc";
        $where = [];
        $where['a.add_time'] = array(array('EGT',$start_date.' 00:00:00'),array('ELT',$end_date.' 23:59:59'));
        
        
        $fields = "a.hotel_id,hotel.name hotel_name,area.region_name,user.remark";
        $group  = "a.hotel_id";
        $m_sale = new \Admin\Model\SaleModel();
        $list =   $m_sale->alias('a')
                         ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
                         ->join('savor_sysuser user on a.maintainer_id=user.id','left')
                         ->join('savor_area_info area on hotel.area_id= area.id','left')
                         ->field($fields)
                         ->where($where)
                         ->order($orders)
                         ->group($group)
                         ->select();
       
       foreach($list as $key=>$v){
           
           $fields = 'a.settlement_price,a.status,a.pay_time,a.add_time';
           $map = [];
           $map['a.add_time'] = array(array('EGT',$start_date.' 00:00:00'),array('ELT',$end_date.' 23:59:59'));
           $map['a.hotel_id'] = $v['hotel_id'];
           
           $rts = $m_sale->alias('a')
                         ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
                         ->join('savor_sysuser user on a.maintainer_id=user.id','left')
                         ->join('savor_area_info area on hotel.area_id= area.id','left')
                         ->field($fields)
                         ->where($map)
                         ->order($orders)
                         ->select();
           $days_range_arr = $this->days_range_arr;
           //print_r($days_range_arr);exit;
           //print_r($rts);exit;
           foreach($rts as $kk=>$vv){
                if($vv['status']==2){
                    continue;
                }
                $diff_day = ceil((time() - strtotime($vv['add_time'])) / 86400); 
                foreach($days_range_arr as $dk=>$dv){
                    if($diff_day>=$dv['min'] && $diff_day<=$dv['max']){
                        $days_range_arr[$dk]['money'] +=$vv['settlement_price'];
                        break;
                    }
                    
                }
           }
           foreach($days_range_arr as $dk=>$dv){
               $list[$key][$days_range_arr[$dk]['name']] = $days_range_arr[$dk]['money'];
           }
          
       }
       $cell = array(
           
           array('region_name','城市'),
           array('hotel_id','仓库编号'),
           array('hotel_name','仓库名称'),
           array('remark','业务员'),
           array('1-7天','1-7天'),
           array('8-15天','8-15天'),
           array('16-30天','16-30天'),
           array('31-60天','31-60天'),
           array('61天以上','61天以上'),
           
           
       );
       $filename = '账龄分析表';
       $this->exportToExcel($cell,$list,$filename,1);
    }
}