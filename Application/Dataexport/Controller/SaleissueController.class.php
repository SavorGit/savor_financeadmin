<?php
namespace Dataexport\Controller;

class SaleissueController extends BaseController {
    
    public function exportjd() {
        $start_date = I('start_date','');
        $end_date   = I('end_date','');
        $type       = I('type',0,'intval');

        $where  = array('a.status'=>1,'record.type'=>7,'record.wo_status'=>2);
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
            $maintainer_id = $v['maintainer_id'];
            $goods_id = $v['goods_id'];
            $department = '';
            $appendix_num = 1;
            $summary = '销售应收款';
            $lb_name = '唯一识别码';
            $idcode = $v['idcode'];

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


}