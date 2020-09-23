<?php

return [
    //token 失效时间 0代表永不失效
    'token_expire' => 0,
    //短信配置
    'sms' => [
        //短信厂商 目前只支持ali
        'type' => 'ali',
        'ali' => [
            //是否开启功能
            'isopen' => false,
            'accessKeyId' => '',
            'accessSecret' => '',
            //签名名称
            'signName' => 'forceboot',
            //短信模版
            'templateCode' => 'SMS_189711506',
            //验证码发送时间间隔
            'expire' => 60,
            //ip日限制发送数量
            'ipLimit' => 5
        ],
    ],
    //存储配置
    'store' => [
        //支持ali 阿里OSS 留空则本地
        'type' => '',
        'ali' => [
            'accessKeyId' => 'LTAI4FtPub8FABt7smKkCarG',
            'accessSecret' => 'WYT6N12zEy2tj3jkqNJlo7k7WYNAN0',
            'endPoint' => 'oss-cn-shenzhen.aliyuncs.com', //EndPoint（地域节点）
            'bucket' => 'appsns' //存储桶名称
        ]
    ],
    //第三方登录配置
    'thirdLogin' => [
        'qq' => [
            'app_id' => '101820370',
            'app_secret' => '01521257a36b4bcd39c4560b5a5c4325',
            'scope' => 'get_user_info',
            'callback' => ''
        ],
        'weibo' => [
            'app_id' => '78734****',
            'app_secret' => 'd8a00617469018d61c**********',
            'scope' => 'all',
            'callback' => ''
        ]
    ]
];
