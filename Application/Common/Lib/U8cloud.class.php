<?php
namespace Common\Lib;
class U8cloud {

    public function apiquery($api,$method,$params){
        $u8_conf = C('U8_CONFIG');
        $api_host = $u8_conf['api_host'];
        $system = $u8_conf['system'];
        $usercode = $u8_conf['usercode'];
        $password = $u8_conf['password'];

        $header_info = array(
            "Content-Type: application/json",
            "usercode: $usercode",
            "password: $password",
            "system: $system",
        );
        $GLOBALS['HEADERINFO'] = $header_info;
        $api_url = $api_host.$api;
        $res = '';
        $curl = new \Common\Lib\Curl();
        switch ($method){
            case 'get':
                $params_query = '';
                $url = $api_url.'?'.$params_query;
                $curl::get($url,$res,10);
                break;
            case 'post':
                $url = $api_url;
                $params = json_encode($params);
                $curl::post($url,$params,$res);
                break;
        }
        return array('url'=>$api_url,'result'=>$res);
    }
    //银行账户添加
    public function addBankAccountInfo($params){
        $api = '/u8cloud/api/uapbd/bankaccount/insert';
        $method = 'post';
        $res = $this->apiquery($api,$method,$params);
        return $res;
    }
}
?>