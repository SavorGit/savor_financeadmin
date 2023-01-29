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
            $params = json_decode($content,true);
            $app_authorize = $params['data'][0];
            $app_authorize['bizType'] = $params['bizType'];
            $app_authorize['timestamp'] = $params['timestamp'];
            $redis = new \Common\Lib\SavorRedis();
            $redis->select(12);
            $cache_key = 'jdy_app_authorize_'.$app_authorize['outerInstanceId'];
            $redis->set($cache_key,json_encode($app_authorize));
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

    private function jdy_query($method,$api,$params){
        $jdy_conf = C('JDY_CONF');
        $api_host = $jdy_conf['api_host'];
        $client_id = $jdy_conf['client_id'];
        $client_secret = $jdy_conf['client_secret'];

        $params_1 = strtoupper($method);
        $params_2 = urlencode($api);
        $params_3 = "";
        $params_query = "";
        if(!empty($params)){
            foreach ($params as $k=>$v){
                $pq = urlencode($v);
                $params_query.="$k=$pq".'&';
                $pp = urlencode($pq);
                $params_3.="$k=$pp".'&';
            }
            $params_3 = rtrim($params_3,'&');
            $params_query = rtrim($params_query,'&');
        }

        $nowtime = time();
        $now_timestamp = getMillisecond();
        $params_4 = "x-api-nonce:{$nowtime}";
        $params_5 = "x-api-timestamp:{$now_timestamp}";
        $sign_str = "$params_1\n$params_2\n$params_3\n$params_4\n$params_5\n";
        $sig = hash_hmac("sha256", $sign_str, $client_secret, false);
        $api_signature = base64_encode($sig);

        $GLOBALS['HEADERINFO'] = array(
            "Content-Type: application/json;charset=utf-8",
            "X-Api-ClientID: $client_id",
            "X-Api-Auth-Version: 2.0",
            "X-Api-TimeStamp: $now_timestamp",
            "X-Api-Nonce: $nowtime",
            "X-Api-SignHeaders: X-Api-TimeStamp,X-Api-Nonce",
            "X-Api-Signature: $api_signature"
        );
        $url = $api_host.$api.'?'.$params_query;
        $res = '';
        $curl = new \Common\Lib\Curl();
        switch ($method){
            case 'GET':
                $curl::get($url,$res,10);
                break;
            case 'POST':
                $curl::post($url,'',$res);
                break;
        }
        return array('url'=>$url,'result'=>$res);
    }


}