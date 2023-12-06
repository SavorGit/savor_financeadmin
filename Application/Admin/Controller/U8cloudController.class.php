<?php
namespace Admin\Controller;
use Think\Controller;
/**
 * @desc 用友接口
 *
 */
class U8cloudController extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function accsubjquery(){
        $u8 = new \Common\Lib\U8cloud();
        $api = '/u8cloud/api/uapbd/accsubj/query';
        $method = 'post';
        $params = array('pk_subjscheme'=>"12");
        $res = $u8->apiquery($api,$method,$params);
        print_r($res);
        exit;
    }


}