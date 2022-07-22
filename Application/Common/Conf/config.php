<?php
//系统配置 
$config = array(
    //路由配置
    'URL_MODEL'				=>2,
    'URL_CASE_INSENSITIVE'  => true, //url支持大小写
    'MODULE_DENY_LIST'      => array('Common','Runtime'), // 禁止访问的模块列表
    'MODULE_ALLOW_LIST'     => array('Admin','Dataexport'), //模块配置
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
    'SITE_NAME'=> '热点财务后台管理',
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

);
if(APP_DEBUG === false){
    $config['TMPL_TRACE_FILE'] = APP_PATH.'Site/View/Public/404.html';   // 页面Trace的模板文件
    $config['TMPL_EXCEPTION_FILE'] = APP_PATH.'Site/View/Public/404.html';// 异常页面的模板文件
}

$config['RESOURCE_TYPE'] = array(
    '1'=>'视频',
    '2'=>'图片',
//    '3'=>'其他',
//    '4'=>'音频',
//    '5'=>'字体',
    '6'=>'文档',
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
    'xls'=>6,'xlsx'=>6,'csv'=>6,
    'pptx'=>6,'ppt'=>6,
    'doc'=>6,'wps'=>6,'docx'=>6,
    'pdf'=>6,'rtf'=>6,'txt'=>6,
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
$config['UNIT_TYPE'] = array(
    '1'=>'基本单位',
    '2'=>'其他'
);
$config['UNIT_CONVERT_TYPE'] = array(
    '1'=>'1基本单位',
    '2'=>'2基本单位',
    '4'=>'4基本单位',
    '6'=>'6基本单位'
);
$config['MANGER_STATE'] = array(
    '0'=>'未审核',
    '2'=>'审核通过',
    '3'=>'审核不通过',
);
$config['STOCK_IN_TYPES'] = array('11'=>'采购入库','12'=>'调拨入库','13'=>'餐厅退回');
$config['STOCK_OUT_TYPES'] = array('21'=>'内部调拨','22'=>'餐厅配货','23'=>'赠送餐厅','24'=>'销售','25'=>'其他消耗');
$config['STOCK_USE_TYPE'] = array('1'=>'餐厅售卖','2'=>'品鉴酒','3'=>'活动');
$config['STOCK_REASON'] = array(
    '1'=>array('id'=>1,'name'=>'售卖'),
    '2'=>array('id'=>2,'name'=>'品鉴酒'),
    '3'=>array('id'=>3,'name'=>'活动')
);
$config['STOCK_RECORD_TYPE']=array('1'=>'入库','2'=>'出库','3'=>'拆箱','4'=>'领取','5'=>'验收','6'=>'报损','7'=>'核销');
$config['STOCK_WRITEOFF_STATUS']=array('1'=>'待审核','2'=>'审核通过','3'=>'审核不通过','4'=>'待补充资料');
$config['MANGER_KEY'] = array(
    'system'=>'系统管理',
    'baseset'=>'基础设置',
    'supplier'=>'供应商管理',
    'goods'=>'商品管理',
    'stock'=>'库存管理',
    'hotelcontract'=>'酒楼合同管理',
    'proxysale'=>'商品代销合同管理',
    'advcontract'=>'广告销售合同管理',
    'purchasecontract'=>'采购合同管理',
    'administrationcontract'=>'行政合同管理',
    'inventorypurchase'=>'采购管理'
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

$config['SMALL_WARN'] = array(
    '1'=>'未处理',
    '2'=>'已处理',
);

$config['SP_GR_STATE'] = array(
    '0'=>'未发布',
    '1'=>'已发布',
    '2'=>'已删除',
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
$config['ALL_COMPANY'] = array(
    '1'=>'拉萨经济技术开发区热点信息技术有限公司',
    '2'=>'拉萨经济技术开发区热点信息技术有限公司北京分公司',
    '3'=>'拉萨经济技术开发区热点信息技术有限公司上海分公司',
    '4'=>'拉萨经济技术开发区热点信息技术有限公司广州分公司',
    '5'=>'拉萨经济技术开发区热点信息技术有限公司深圳分公司',
    '6'=>'北京热点投屏科技发展有限公司'
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
$config['FINACE_CONTRACT'] = array(
	'company_property' => array( 
								array('id'=>1,'name'=>'一般纳税人'),
								array('id'=>'2','name'=>'小规模纳税人')),
	'invoice_type'     => array( 
								array('id'=>1,'name'=>'专票'),
								array('id'=>'2','name'=>'普票')),
	'contract_ctype'   => array(
								'proxysale'=>array(
									array('id'=>21,'name'=>'酒水'),
									array('id'=>22,'name'=>'食品'),
									array('id'=>23,'name'=>'其他'),
								),
								'adsale'=>array(
									array('id'=>31,'name'=>'酒水'),
									array('id'=>32,'name'=>'食品'),
									array('id'=>33,'name'=>'其他'),
								),
								'purchase'=>array(
									array('id'=>41,'name'=>'食品'),
									array('id'=>42,'name'=>'酒水'),
									array('id'=>43,'name'=>'机顶盒'),
									array('id'=>44,'name'=>'电'),
									array('id'=>45,'name'=>'网络设备'),
									array('id'=>46,'name'=>'其它'),
								),
                                'administration'=>array(
                                    '51'=>array('id'=>51,'name'=>'房租'),
                                    '52'=>array('id'=>52,'name'=>'车位'),
                                    '53'=>array('id'=>53,'name'=>'宽带'),
                                    '54'=>array('id'=>54,'name'=>'招聘'),
                                    '55'=>array('id'=>55,'name'=>'其它'),
                                ),
									
	),
    'contract_status'=> array(
        '1'=>array('id'=>1,'name'=>'待生效'),
        '2'=>array('id'=>2,'name'=>'进行中'),
        '3'=>array('id'=>3,'name'=>'已结束'),
        '4'=>array('id'=>4,'name'=>'已终止'),
    ),
	'settlement_type'  => array( 
						'purchase'=>array(
										array('id'=>1,'name'=>'一次性付款'),
										array('id'=>2,'name'=>'分期付款'),
									),
						'advsale'=>array(
										array('id'=>1,'name'=>'现金'),
										array('id'=>2,'name'=>'易货'),
										array('id'=>3,'name'=>'现金+易货'),
									),
									
	
	),
    'check_cycle'=>array(
        'proxysale'=>array(
            array('id'=>1,'name'=>'批'),
            array('id'=>2,'name'=>'月'),
        ),
    ),
    'closed_circle'=>array(
        'proxysale'=>array(
            array('id'=>1,'name'=>'月'),
            array('id'=>2,'name'=>'季'),
        )
    ),
);

$config['STOCK_MANAGER']=array(
    'o9GS-4reX0MCJbXvGamZghvmPk6U'=>'郑伟',
    'o9GS-4oZfWgjT0lySkJskdlflNrw'=>'黄勇',
    'o9GS-4iGZE9olTzXTMjon8xDyRpo'=>'黄勇',
    'o9GS-4oGSdRGYiNZZ4oKQ9PBm_TI'=>'李丛',
    'o9GS-4g6xM3jhCWUUPnvK5a4sysI'=>'张英涛',
    'o9GS-4t61F_qSPmwEaAtd9v6f6DY'=>'刘斌',
    'o9GS-4icfJEZSX8_qDs6pB_nD30o'=>'李昭',
    'o9GS-4iinyutBsN73FJFjdZC3rWg'=>'赵翠燕',
    'o9GS-4ix2RgA41QyHjMqAljsvbvY'=>'黎晓欣',
    'o9GS-4kpg8khL72nVZKDsgn0ioDM'=>'陈灵玉',
    'o9GS-4mouXnk_WhBAL-Zhsg0YbOE'=>'余穗筠',
    'o9GS-4mTCZvkRCDRnkg77QqohMI4'=>'胡子凤',
);
$config['CONTRACT_COMPANY']= array(
	array('id'=>1,'name'=>'拉萨经济技术开发区热点信息技术有限公司'),
	array('id'=>2,'name'=>'拉萨经济技术开发区热点信息技术有限公司北京分公司'),
	array('id'=>3,'name'=>'拉萨经济技术开发区热点信息技术有限公司上海分公司'),
	array('id'=>4,'name'=>'拉萨经济技术开发区热点信息技术有限公司广州分公司'),
	array('id'=>5,'name'=>'拉萨经济技术开发区热点信息技术有限公司深圳分公司'),
	array('id'=>6,'name'=>'北京热点投屏科技发展有限公司'),
);



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