<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 第三方短信服务商
    |--------------------------------------------------------------------------
    |
    | 支持：阿里云短信、云片网络、容联·云通讯、赛邮·云通讯、Luosimao
    |
    | 消息返回：JSON格式消息
    |
    */
    'default' => env('SMS_DEFAULT'),

    'fallback' => env('SMS_FALLBACK'),

    'signName' => env('SMS_SIGNNAME'),

    'agents' => [

        'aLiYun' => [
            'credentials' => [
                'appKey' => env('ALIYUN_APPKEY'),
                'appSecret' => env('ALIYUN_APPSECRET')
            ],
            'templateId' => env('ALIYUN_TEMPLATEID'),
            'executableFile' => 'ALiYunAgent',
        ],

        'yunPian' => [
            'apiKey' => env('YUNPIAN_APIKEY'),
            'templateContent' => env('YUNPIAN_TEMPLATECONTENT'),
            'executableFile' => 'YunPianAgent',
        ],

        'yunTongXun' => [
            'credentials' => [
                'accountSid' => env('YUNTONGXUN_ACCOUNTSID'),
                'accountToken' => env('YUNTONGXUN_ACCOUNTTOKEN'),
                'appId' => env('YUNTONGXUN_APPID'),
            ],
            'templateId' => env('YUNTONGXUN_TEMPLATEID'),
            'executableFile' => 'YunTongXunAgent',
        ],

        'subMail' => [
            'credentials' => [
                'appid' => env('SUBMAIL_APPID'),
                'apiKey' => env('SUBMAIL_APIKEY'),
            ],
            'templateId' => env('SUBMAIL_TEMPLATEID'),
            'executableFile' => 'SubMailAgent',
        ],

        'luoSiMao' => [
            'apiKey' => env('LUOSIMAO_APIKEY'),
            'templateContent' => env('LUOSIMAO_TEMPLATECONTENT'),
            'executableFile' => 'LuoSiMaoAgent',
        ],

    ],

];
