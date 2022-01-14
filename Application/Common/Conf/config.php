<?php
//系统配置 
$config = array(
    //路由配置
    'URL_MODEL'				=>2,
    'URL_CASE_INSENSITIVE'  => true, //url支持大小写
    'MODULE_DENY_LIST'      => array('Common','Runtime'), // 禁止访问的模块列表
    'MODULE_ALLOW_LIST'     => array('Admin','H5','Smallapp','Dataexport','Integral'), //模块配置
    'DEFAULT_MODULE'        => 'Admin',
    //session cookie配置
    'SESSION_AUTO_START'    =>  true,    // 是否自动开启Session
    'SESSION_OPTIONS'       =>  array(), // session 配置数组 支持type name id path expire domain 等参数
    'SESSION_TYPE'          =>  '', // session hander类型 默认无需设置 除非扩展了session hander驱动
    'SESSION_PREFIX'        =>  'savor_', // session 前缀
    //数据库配置
    'DB_FIELDS_CACHE' 		=> true,
    'DATA_CACHE_TABLE'      =>'savor_datacache',
    //报错页面配置
    'TMPL_ACTION_ERROR'     => APP_PATH.'Admin/View/Public/prompt.html', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   => APP_PATH.'Admin/View/Public/prompt.html', // 默认成功跳转对应的模板文件

    //日志配置
    'LOG_RECORD'            =>  false,   // 默认不记录日志
    'LOG_TYPE'              =>  'File', // 日志记录类型 默认为文件方式
    'LOG_LEVEL'             =>  'EMERG,ALERT,CRIT,ERR',// 允许记录的日志级别
    'LOG_EXCEPTION_RECORD'  =>  false,    // 是否记录异常信息日志
    //缓存目录配置
    'MINIFY_CACHE_PATH'=>APP_PATH.'Runtime/Cache',
    'HTML_FILE_SUFFIX' => '.html',// 默认静态文件后缀
    'HOST_NAME'=>'http://'.$_SERVER['HTTP_HOST'],
    'HTTPS_HOST_NAME'=>'https://'.$_SERVER['HTTP_HOST'],
    'SITE_NAME'=> '寻味后台管理',
    'SHOW_ERROR_MSG' =>  true, //显示错误信息
    'OSS_ADDR_PATH'=>'media/resource/',
	'SECRET_KEY' => 'sw&a-lvd0onr!',//解密接口数据key
	'API_SECRET_KEY' => 'w&-ld0n!',//解密接口数据key
	'SHOW_URL_APP_KEY'=>'258257010', //新浪短链接appkey
	'BAIDU_GEO_KEY'=>'q1pQnjOG28z8xsCaoby2oqLTLaPgelyq',
    'HASH_IDS_KEY'=>'Q1xsCaoby2o',
    'HASH_IDS_KEY_ADMIN'=>'Q1xsCaoby2o',

    'SAPP_CALL_NETY_CMD'=>'call-mini-program',
    'SAPP_SALE'=>'smallappsale:',
    'SAPP_OPS'=>'smallappops:',
    'SAPP_SALE_ACTIVITYGOODS_PROGRAM'=>'smallappsale:activitygoodsprogram',
    'SAPP_SHOP_PROGRAM'=>'smallapp:shopprogram',
    'SAPP_SALE_WELCOME_RESOURCE'=>'smallappsale:welcomeresource',
    'SAPP_SALE_ACTIVITY_PROMOTE'=>'smallappsale:activitypromote:',
    'SAPP_FIND_TOP'=>'smallapp:findtop',
    'SAPP_HOTPLAY_PRONUM'=>'smallapp:hotplaypronum',
    'SAPP_FORSCREENTRACK'=>'smallapp:trackforscreen:',
    'FEAST_TIME'=>array('lunch'=>array('11:30','14:30'),'dinner'=>array('18:00','21:00')),
    'SALEFEAST_TIME'=>array('lunch'=>array('11:00','14:00'),'dinner'=>array('16:45','21:00')),
    'SAPP_CANCEL_FORSCREEN'=>'smallapp:cancelforscreen:',
    'MEAL_TIME'=>array('lunch'=>array('10:00','15:00'),'dinner'=>array('17:00','23:59')),
    'SCAN_QRCODE_TYPES'=>array(1,2,3,5,6,7,8,9,10,11,12,13,15,16,19,20,21,29,30),
//     scan_qrcode_type 1:小码2:大码(节目)3:手机小程序呼码5:大码（新节目）6:极简版7:主干版桌牌码8:小程序二维码9:极简版节目大码
//     10:极简版大码11:极简版呼玛12:大二维码（节目）13:小程序呼二维码 15:大二维码（新节目）16：极简版二维码19:极简版节目大二维码
//     20:极简版大二维码21:极简版呼二维码22购物二维码 23销售二维码 24菜品商家 25单个菜品 26海报分销售卖商品 27 商城商家 28商城商品大屏购买
//     29推广渠道投屏码 30投屏帮助视频 31活动霸王菜


);
if(APP_DEBUG === false){
    $config['TMPL_TRACE_FILE'] = APP_PATH.'Site/View/Public/404.html';   // 页面Trace的模板文件
    $config['TMPL_EXCEPTION_FILE'] = APP_PATH.'Site/View/Public/404.html';// 异常页面的模板文件
}

$config['RESOURCE_TYPE'] = array(
    '1'=>'视频',
    '2'=>'图片',
    '3'=>'其他',
    '4'=>'音频',
    '5'=>'字体',
);
$config['RESOURCE_TYPEINFO'] = array(
    'mp4'=>1,
    'mov'=>1,
    'jpg'=>2,
    'png'=>2,
    'gif'=>2,
    'jpeg'=>2,
    'bmp'=>2,
    'wma'=>4,
    'mp3'=>4,
    'ttf'=>5,
);

$config['PWDPRE'] = 'SAVOR@&^2017^2030&*^';
$config['NUMPERPAGE'] = array('50','100','200','500','800','1000','2000');
$config['MANGER_STATUS'] = array(
    '1'=>'启用',
    '2'=>'禁用'
);
$config['MANGER_LEVEL'] = array(
    '0'=>'一级栏目',
    '1'=>'二级栏目',
    '2'=>'三级栏目'
);

$config['MANGER_STATE'] = array(
    '0'=>'未审核',
    '2'=>'审核通过',
    '3'=>'审核不通过',
);
$config['MANGER_KEY'] = array(
    'colum'=>'版本管理节点',
    'cms'=>'程序节点',
    'system'=>'系统节点',
    'send' =>'内容节点',
    'version'=>'版本更新节点',
    'menu' =>'节目节点',
    'ad' =>'广告节点',
    'hotel' =>'酒楼节点',
    'report'=>'报表节点',
    'testreport'=>'测试报表节点',
    'checkaccount'=>'对账系统节点',
    'dailycontent'=>'每日知享节点',
    'newmenu'=>'新节目节点',
    'advdelivery'=>'广告投放节点',
	'option'=>'运维客户端',
    'installoffer'=>'网络设备报价',
    'smallapp'=>'小程序数据统计节点',
    'miniprogram'=>'小程序管理',
    'integral'=>'积分系统',

);
$config['MOBILE_TYPE'] = array(
    '1' => array('id'=>1, 't'=>'Iphone 4', 'w'=>'320', 'h'=>'480'),
    '2' => array('id'=>2, 't'=>'Iphone 5', 'w'=>'320', 'h'=>'568'),
    '3' => array('id'=>3, 't'=>'Iphone 6', 'w'=>'375', 'h'=>'667'),
    '4' => array('id'=>4, 't'=>'Iphone 6 Plus', 'w'=>'414', 'h'=>'736'),
    '5' => array('id'=>5, 't'=>'Ipad Mini', 'w'=>'768', 'h'=>'1024'),
    '6' => array('id'=>6, 't'=>'Ipad', 'w'=>'768', 'h'=>'1024'),
    '7' => array('id'=>7, 't'=>'Galaxy S5', 'w'=>'360', 'h'=>'640'),
    '8' => array('id'=>8, 't'=>'Nexus 5X', 'w'=>'411', 'h'=>'731'),
    '9' => array('id'=>9, 't'=>'Nexus 6P', 'w'=>'435', 'h'=>'773'),
    '10' => array('id'=>10, 't'=>'Laptop MDPI', 'w'=>'1280', 'h'=>'800'),
    '11' => array('id'=>11, 't'=>'Laptop HiDPI', 'w'=>'1440', 'h'=>'900'),
);
$config['SMS_CONFIG'] = array(
    'accountsid'=>'6a929755afeded257916ca68518ec1c3',
    'token'     =>'66edd50a46c882a7f4231186c44416d8',
    'appid'     =>'a982fdb55a2441899f2eaa64640477c0',
    'bill_templateid'=>'76285',
    'payment_templateid'=>'78145',
    'vcode_templateid'=>'107496',
    //'notice_templateid'=>'107928',
    'notice_templateid'=>'146776',
);
$config['ACTIVITY_SOURCE_ARR'] = array(
    '1'=>'App',
    '2'=>'App推送',  
    '3'=>'微信客户端',
    '4'=>'微信公众号',
);
$config['SMALL_WARN'] = array(
    '1'=>'未处理',
    '2'=>'已处理',
);

$config['SP_GR_STATE'] = array(
    '0'=>'未发布',
    '1'=>'已发布',
    '2'=>'已删除',
);

$config['WX_DYH_CONFIG'] = array(
    'appid'=>'wxb19f976865ae9404',
    'appsecret'=>'977d15e1ce3c342c123ae6f30bcfeb48',
);

$config['WX_FWH_CONFIG'] = array(
    'appid'=>'wx7036d73746ff1a14',
    'appsecret'=>'8b658fc90d7105d5cf66cb2193edb7d4',
    'key_ticket'=>'savor_wx_xiaorefu_jsticket',
    'key_token'=>'savor_wx_xiaorefu_token',
);
$config['WX_MP_CONFIG'] = array(
    'cache_key'=>'wxmp',
    'appid'=>'wxcb1e088545260931',
    'appsecret'=>'9f1ebb78d1dc7afe73dcb22a135cfcf9'
);

$config['SMALLAPP_CONFIG'] = array(
    'cache_key'=>'smallapp_token',
    'appid'=>'wxfdf0346934bb672f',
    'appsecret'=>'b9b93aef8d6609722596e35385ff05c5'
);

$config['SMALLAPP_SALE_CONFIG'] = array(
    'cache_key'=>'smallapp_sale_token',
    'appid'=>'wxfc48bdfa3fcaf358',
    'appsecret'=>'8fe57f640a23cc3ecfb3d5f8fff70144'
);

$config['XIAO_REDIAN_DING'] = array(
    'appid'=>'wxb19f976865ae9404',
    'appsecret'=>'977d15e1ce3c342c123ae6f30bcfeb48',
    'key_ticket'=>'savor_wx_xiaore_jsticket',
    'key_token'=>'savor_wx_xiaore_token',
);

$config['ZHI_XIANG_CONFIG'] = array(
    'appid'=>'wx75025eb1e60df2cf',
    'appsecret'=>'32427ebb0caae2d9e76747fed56e2071',
    'key_ticket'=>'savor_wx_zhixiang_jsticket',
    'key_token'=>'savor_wx_zhixiang_token',
    'cardapi_ticket'=>'savor_wx_zhixiang_cardapiticket',
    'token'=>'savor',
);

$config['UMENT_API_CONFIG'] = array(
     'API_URL'=>'http://msg.umeng.com/api/send',
     'opclient'=>array(
         'AppKey'=>'59acb7f0f29d98425d000cfa',
         'App_Master_Secret'=>'75h0agzaqlibje6t2rtph4uuuocjyfse',
         'ios_AppKey'=>'59b1260a734be41803000022',
         'ios_App_Master_Secret' =>'wgyklqy5uu8dacj9yartpic9xmpkezs4',
     ),
);

$config['SMALLAPP_CONFIG'] = array(
    'cache_key'=>'smallapp_token',
    'appid'=>'wxfdf0346934bb672f',
    'appsecret'=>'b9b93aef8d6609722596e35385ff05c5'
);

$config['UMENBAI_API_CONFIG'] = array(
    'API_URL'=>'http://msg.umeng.com/api/send',
    'opclient'=>array(
        'android_appkey'=>'59acb7f0f29d98425d000cfa',
        'android_master_secret'=>'75h0agzaqlibje6t2rtph4uuuocjyfse',
        'ios_appkey'=>'59b1260a734be41803000022',
        'ios_master_secret' =>'wgyklqy5uu8dacj9yartpic9xmpkezs4',
    ),
    'boxclient'=>array(
        'android_appkey'=>'58576b54677baa3b41000809',
        'android_master_secret'=>'v6fr959wpmczeayq34utymxcm7fizufu',
        //'ios_appkey'=>'59b1260a734be41803000022',
        //'ios_master_secret' =>'wgyklqy5uu8dacj9yartpic9xmpkezs4',
    ),
);
$config['WXAPPIDS'] = array(
    'wx13e41a437b8a1d2e'=>'京东爆款',
    'wxf96ad76f27597d65'=>'故宫书店',
    'wx91d27dbf599dff74'=>'京东购物',
    'wx52af38651932e8d3'=>'赖茅',
);
//推送通知的后续行为必填值
$config['AFTER_APP'] = array(
    0=>"go_app",
    1=>"go_url",
    2=>"go_activity",
    3=>"go_custom",
);
$config['REDPACKET_SENDTYPES'] = array(
    '1'=>'立即发送',
    '2'=>'单次定时',
    '3'=>'多次定时'
);

$config['USER_GRP_CONFIG'] = array(
    '0'=>'无',
    '1'=>'运维组',
);
$config['ROOM_TYPE'] = array(
    1=>'包间',
    2=>'大厅',
    3=>'等候区'
);
$config['HEART_LOSS_HOURS'] = 48;
//发送邮件配置
$config['MAIL_ADDRESS'] = 'xxx@xxx.com'; // 邮箱地址
$config['MAIL_SMTP'] = 'smtp.xxx.com'; // 邮箱SMTP服务
$config['MAIL_LOGINNAME'] = 'xx@xx.com'; // 邮箱登录帐号
$config['MAIL_PASSWORD'] = 'mailpassword'; // 邮箱密码
$config['MAIL_CHARSET'] = 'UTF-8';//编码
$config['MAIL_AUTH'] = true;//邮箱认证
$config['MAIL_HTML'] = true;//true HTML格式 false TXT格式

$config['ALIYUN_SMS_CONFIG'] = array(
    'send_invoice_addr_templateid'=>'SMS_176935152',
    'activity_goods_send_salemanager'=>'SMS_176527162',
    'merchant_login_invite_code'=>'SMS_183767419',
    'public_audit_templateid'=>'SMS_216374893',
    'public_audit_templateid'=>'SMS_216374893',
    'send_tasklottery_user_templateid'=>'SMS_227740798',
    'send_tasklottery_sponsor_templateid'=>'SMS_227745754',
    'send_tasklottery_bootnum_templateid'=>'SMS_227737155',
);

return $config;