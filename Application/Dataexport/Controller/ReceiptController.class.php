<?php
namespace Dataexport\Controller;

class ReceiptController extends BaseController {
    
    private $serial_number_prefix = 'SKD';
    /*
     * 处理excel数据
     */
    public function analyseExcel(){
        exit('已执行该脚本');
        set_time_limit(9000);
        ini_set("memory_limit", "8018M");
        $m_sale = new \Admin\Model\SaleModel();
        $m_sale_payment = new \Admin\Model\SalePaymentModel();
        $m_sale_payment_record = new \Admin\Model\SalePaymentRecordModel();
        $serial_number_prefix = $this->serial_number_prefix;
        $path = '/application_data/web/php/savor_financeadmin/Public/uploads/2023-06-16/收款记录表2023061602.xlsx';
        if  ($path == '') {
            $res = array('error'=>0,'message'=>array());
            echo json_encode($res);
        }
        $type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        vendor("PHPExcel.PHPExcel.IOFactory");
        $objPHPExcel = \PHPExcel_IOFactory::load($path);
        
        
        $sheet = $objPHPExcel->getSheet(0);
        //获取行数与列数,注意列数需要转换
        $highestRowNum = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnNum = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        
        
        //取得字段，这里测试表格中的第一行为数据的字段，因此先取出用来作后面数组的键名
        $filed = array();
        for ($i = 0; $i < $highestColumnNum; $i++) {
            $cellName = \PHPExcel_Cell::stringFromColumnIndex($i) . '1';
            $cellVal = $sheet->getCell($cellName)->getValue();//取得列内容
            $filed[] = $cellVal;
        }
        
        
        //开始取出数据并存入数组
        $datas = array();
        $muyou = array();
        //$hotel_str = '';
        //$spx = '';
        $serial_number_new = [];
        for ($i = 2; $i <= $highestRowNum; $i++) {//ignore row 1
            $row = array();
            for ($j = 0; $j < $highestColumnNum; $j++) {
                $cellName = \PHPExcel_Cell::stringFromColumnIndex($j) . $i;
                $cellVal = (string)$sheet->getCell($cellName)->getValue();
                if($cellVal === 'null'){
                    $cellVal = '';
                }
                if($cellVal === '"' ||  $cellVal === "'"){
                    $cellVal = '#';
                }
                if($cellVal === 'null'){
                    $cellVal = '';
                }
                $row[$filed[$j]] = $cellVal;
            }
            //$hotel_str .= $spx. $row['id'];
            //$spx = ',';
            $row['price'] = $row['pay_money'] / $row['number'];
            $row['sysuser_id'] = 349;
            if(!empty($row['hx_time'])){
                $timestamp = strtotime($row['sk_date']);
                $row['sk_date'] = date('Y-m-d',$timestamp);
                //$map = [];
                //$map['pay_time'] = $row['sk_date'];
                //$rts = $m_sale_payment->field('skd_number')->where($map)->order('id desc')->find();
                
                if(empty($serial_number_new[date('Ymd',$timestamp)])){
                    $row['serial_number'] = $serial_number_prefix.'-'.date('Ymd',$timestamp).'-00001';
                    $serial_number_new[date('Ymd',$timestamp)] = '00001';
                }else {
                    
                    $sub_str = intval($serial_number_new[date('Ymd',$timestamp)]) +1;
                    $serial_number = str_pad($sub_str,5,"0",STR_PAD_LEFT);
                    $row['serial_number'] =$serial_number_prefix .'-'.date('Ymd',$timestamp).'-'. $serial_number;
                    $serial_number_new[date('Ymd',$timestamp)] = $serial_number;
                }
            }
            
            
            if(!empty($row['idcode'])){
                $sale_info = $m_sale->field('id,ptype')->where(array('idcode'=>$row['idcode']))->find();
                $row['sale_ids'] = $sale_info['id'];
                $row['ptype']    = $sale_info['ptype'];
                if($row['ptype']==2 || $row['ptype']==0){
                    $datas [] = $row;
                }
                if(empty($sale_info)){
                    $muyou [] = $row;
                }
                
            }
        }
        //print_r($muyou);exit;
        //$data[] = $datas['1420'];
        //print_r($datas);exit;
        /*$tmp = [];
        foreach($datas as $key=>$v){
            if(empty($v['sale_ids'])){
                $tmp[] = $v;
            }
        }
        print_r($tmp);exit;    
            
        echo "ok";exit;*/
        //$datas = array_slice($datas, 100,100);
        //print_r($datas);exit;
        $flag = 1;
        foreach($datas as $key=>$v){
            //print_r($v);exit;
            if(!empty($v['sale_ids'])){
                //第一步数据导入savor_finance_sale_payment表
                $info = [];
                $info['id'] = $flag;
                $info['hotel_id']      = $v['hotel_id'];
                $info['serial_number'] = $v['serial_number'];
                $info['tax_rate']      = 13;
                $info['pay_money']     = $v['pay_money'];
                $info['pay_time']      = $v['sk_date'];
                $info['sysuser_id']    = $v['sysuser_id'];
                //print_r($info);exit;
                $ret = $m_sale_payment->addData($info);   
                //第二步 更新 savor_finance_sale 表 ptype为1 sale_payment_id settlement_price为表格中的价格, status为2，type为1
                $condition       = [];
                $condition['id'] = $v['sale_ids'];
                $upinfo                     = [];
                $upinfo['ptype']            = 1;
                $upinfo['sale_payment_id']  = $ret;
                $upinfo['settlement_price'] = $v['price'];
                $upinfo['status']           = 2;
                $upinfo['type']             = 1;
                
                //print_r($condition);exit;
                
                
                $m_sale->updateData($condition, $upinfo);
                
                //第三部插入sale_payment_record表
                $rinfo = [];
                $rinfo['sale_id']         = $v['sale_ids'];
                $rinfo['sale_payment_id'] = $ret;
                $rinfo['pay_money']       = $v['pay_money'];
                $m_sale_payment_record->addData($rinfo);
                
                $flag ++;
            }
             
            
        }
        echo "end".date('Y-m-d H:i:s');
    }
}