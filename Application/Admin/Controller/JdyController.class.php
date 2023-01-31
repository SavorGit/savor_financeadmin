<?php
namespace Admin\Controller;
use Think\Controller;
/**
 * @desc 金蝶云接口
 *
 */
class JdyController extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function notify(){
        $content = file_get_contents('php://input');
        $log_content = date("Y-m-d H:i:s").'[resp_result]'.$content.'[client_ip]'.get_client_ip()."\n";
        $log_file_name = APP_PATH.'Runtime/Logs/'.'jdy_'.date("Ymd").".log";
        @file_put_contents($log_file_name, $log_content, FILE_APPEND);

        if(!empty($content)){
            $res_data = json_decode($content,true);
            if($res_data['bizType']=='app_authorize'){
                $app_authorize = $res_data['data'][0];
                $app_authorize['bizType'] = $res_data['bizType'];
                $app_authorize['timestamp'] = $res_data['timestamp'];
                $redis = new \Common\Lib\SavorRedis();
                $redis->select(12);
                $cache_key = 'jdy_app_authorize_'.$app_authorize['outerInstanceId'];
                $redis->set($cache_key,json_encode($app_authorize));
            }
        }
        echo 'success';
    }

    public function apptoken(){
        $jdy_conf = C('JDY_CONF');
        $outer_instance_id = $jdy_conf['outer_instance_id'];

        $cache_key = 'jdy_app_authorize_'.$outer_instance_id;
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(12);
        $res_config = $redis->get($cache_key);
        $jdy_config = json_decode($res_config,true);

        $appKey = $jdy_config['appKey'];
        $appSecret = $jdy_config['appSecret'];
        $app_signature = hash_hmac("sha256", $appKey, $appSecret, false);
        $app_signature_str = base64_encode($app_signature);

        $method = 'GET';
        $api = '/jdyconnector/app_management/kingdee_auth_token';
        $params = array('app_key'=>$appKey,'app_signature'=>$app_signature_str);
        $result = $this->jdy_query($method,$api,$params);
        print_r($result);
        if(!empty($result['result'])){
            $res_data = json_decode($result['result'],true);
            if($res_data['errcode']==0 && !empty($res_data['data'])){
                $cache_token_key = 'jdy_app_token';
                $redis->set($cache_token_key,json_encode($res_data['data']));
            }else{
                $this->pushappauth();
            }
        }
    }

    public function pushappauth(){
        $jdy_conf = C('JDY_CONF');
        $outer_instance_id = $jdy_conf['outer_instance_id'];
        $method = 'POST';
        $api = '/jdyconnector/app_management/push_app_authorize';
        $params = array('outerInstanceId'=>$outer_instance_id);
        $result = $this->jdy_query($method,$api,$params);
        print_r($result);
    }

    public function goods(){
        $jdy_conf = C('JDY_CONF');
        $cache_key = 'jdy_app_authorize_'.$jdy_conf['outer_instance_id'];
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(12);
        $res_config = $redis->get($cache_key);
        $app_auth = json_decode($res_config,true);
        $domain = $app_auth['domain'];
        $res_token = $redis->get('jdy_app_token');
        $token_info = json_decode($res_token,true);
        $app_token = $token_info['app-token'];

        $method = 'GET';
        $api = '/jdy/v2/bd/material';
        $page_size = 100;
        $goods_parent_id = 1458678600654962688;
        $params = array('page'=>1,'page_size'=>$page_size,'parent'=>$goods_parent_id);
        $result = $this->jdy_query($method,$api,$params,$app_token,$domain);
        $res_data = json_decode($result['result'],true);
        foreach ($res_data['data']['rows'] as $v){
            $all_goods[]=array('material_id'=>$v['id'],'material_name'=>$v['name'],'goods_id'=>$v['number']);
        }
        echo json_encode($all_goods);
    }

    public function saleorder(){
        $jdy_conf = C('JDY_CONF');
        $cache_key = 'jdy_app_authorize_'.$jdy_conf['outer_instance_id'];
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(12);
        $res_config = $redis->get($cache_key);
        $app_auth = json_decode($res_config,true);
        $domain = $app_auth['domain'];
        $res_token = $redis->get('jdy_app_token');
        $token_info = json_decode($res_token,true);
        $app_token = $token_info['app-token'];

        $stime = strtotime('2023-01-29 00:00:00');
        $create_start_time = $stime.'000';
        $etime = strtotime('2023-01-29 23:59:59');
        $create_end_time	= $etime.'000';

        $method = 'GET';
        $api = '/jdy/v2/scm/sal_out_bound';
        $params = array('create_start_time'=>$create_start_time,'create_end_time'=>$create_end_time,'settle_status'=>'A','page'=>1,'page_size'=>50);
        $result = $this->jdy_query($method,$api,$params,$app_token,$domain);
        print_r($result['result']);
    }

    public function orderinfo(){
        $jdy_conf = C('JDY_CONF');
        $cache_key = 'jdy_app_authorize_'.$jdy_conf['outer_instance_id'];
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(12);
        $res_config = $redis->get($cache_key);
        $app_auth = json_decode($res_config,true);
        $domain = $app_auth['domain'];
        $res_token = $redis->get('jdy_app_token');
        $token_info = json_decode($res_token,true);
        $app_token = $token_info['app-token'];

        $method = 'GET';
        $api = '/jdy/v2/scm/sal_out_bound_detail';
        $params = array('id'=>1608850196882570240);
        $result = $this->jdy_query($method,$api,$params,$app_token,$domain);
        print_r($result['result']);
    }

    public function emplist(){
        $jdy_conf = C('JDY_CONF');
        $cache_key = 'jdy_app_authorize_'.$jdy_conf['outer_instance_id'];
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(12);
        $res_config = $redis->get($cache_key);
        $app_auth = json_decode($res_config,true);
        $domain = $app_auth['domain'];
        $res_token = $redis->get('jdy_app_token');
        $token_info = json_decode($res_token,true);
        $app_token = $token_info['app-token'];

        $method = 'GET';
        $api = '/jdy/v2/bd/emp';
        $params = array('page'=>1,'page_size'=>100);
        $result = $this->jdy_query($method,$api,$params,$app_token,$domain);
        print_r($result['result']);
    }

    public function empadd(){
        $jdy_conf = C('JDY_CONF');
        $cache_key = 'jdy_app_authorize_'.$jdy_conf['outer_instance_id'];
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(12);
        $res_config = $redis->get($cache_key);
        $app_auth = json_decode($res_config,true);
        $domain = $app_auth['domain'];
        $res_token = $redis->get('jdy_app_token');
        $token_info = json_decode($res_token,true);
        $app_token = $token_info['app-token'];

        $method = 'POST';
        $api = '/jdy/v2/bd/emp';
        $params = array(
            "birthday"=>"2019-12-05",
            "number"=>"TS001",
            "gender"=>1,
            "name"=>"王测试",
            "mobile"=>"13112345678",
            "hire_date"=>"2019-12-05",
        );
        $result = $this->jdy_query($method,$api,$params,$app_token,$domain);
        print_r($result);
    }



    private function jdy_query($method,$api,$params,$app_token='',$domain=''){
        $jdy_conf = C('JDY_CONF');
        $api_host = $jdy_conf['api_host'];
        $client_id = $jdy_conf['client_id'];
        $client_secret = $jdy_conf['client_secret'];

        $params_1 = strtoupper($method);
        $params_2 = urlencode($api);
        $params_3 = "";
        $params_query = "";
        if(!empty($params)){
            ksort($params);
            foreach ($params as $k=>$v){
                $pq = urlencode($v);
                $params_query.="$k=$pq".'&';
                $pp = urlencode($pq);
                $params_3.="$k=$pp".'&';
            }
            $params_3 = rtrim($params_3,'&');
            $params_query = rtrim($params_query,'&');
        }
        if($method=='POST'){
            if($api!='/jdyconnector/app_management/push_app_authorize'){
                $params_3 = "";
            }
        }
        $nowtime = time();
        $now_timestamp = getMillisecond();
        $params_4 = "x-api-nonce:{$nowtime}";
        $params_5 = "x-api-timestamp:{$now_timestamp}";
        $sign_str = "$params_1\n$params_2\n$params_3\n$params_4\n$params_5\n";
        $sig = hash_hmac("sha256", $sign_str, $client_secret, false);
        $api_signature = base64_encode($sig);

        $header_info = array(
            "Content-Type: application/json;charset=utf-8",
            "X-Api-ClientID: $client_id",
            "X-Api-Auth-Version: 2.0",
            "X-Api-TimeStamp: $now_timestamp",
            "X-Api-Nonce: $nowtime",
            "X-Api-SignHeaders: X-Api-TimeStamp,X-Api-Nonce",
            "X-Api-Signature: $api_signature"
        );
        if(!empty($app_token)){
            $header_info[]="app-token: $app_token";
        }
        if(!empty($domain)){
            $header_info[]="X-GW-Router-Addr: $domain";
        }
        $GLOBALS['HEADERINFO'] = $header_info;
        $api_url = $api_host.$api;
        $res = '';
        $curl = new \Common\Lib\Curl();
        switch ($method){
            case 'GET':
                $url = $api_url.'?'.$params_query;
                $curl::get($url,$res,10);
                break;
            case 'POST':
                if($api=='/jdyconnector/app_management/push_app_authorize'){
                    $url = $api_url.'?'.$params_query;
                    $params = '';
                }else{
                    $url = $api_url;
                    $params = json_encode($params);
                }
                $curl::post($url,$params,$res);
                break;
        }
        return array('url'=>$url,'result'=>$res);
    }


}