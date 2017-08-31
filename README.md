# laravel sms

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]


基于laravel5开发的轻量化手机短信服务包，特点：简单，灵活


   > 支持：阿里云短信、云片网络、容联·云通讯、赛邮·云通讯、Luosimao

   > 适合场景：手机验证、订单消息、通知提醒等



## 安装

Via Composer

``` php
composer require laravelsms/sms
```

composer.json


``` php
"laravelsms/sms": "dev-master"
```

## 配置

``` php
//服务提供者
'providers' => [
        // ...
        Laravelsms\Sms\SmsServiceProvider::class,
    ]
    
//别名
'aliases' => [
    //...
    'Sms' => Laravelsms\Sms\Facades\Sms::class,    
]

//创建配置文件
php artisan vendor:publish --tag=phpsms
```


## 配置项(.env)

   > 以下为本程序所支持的短信代理平台的配置参考：


``` php

//默认短信平台
SMS_DEFAULT=aLiYun

//备用短信平台（非自动化，仅作为变量提供，需自行程序处理，适合当主平台出现状况后使用。）
SMS_FALLBACK=subMail

//默认签名（重要：签名要用{}括起来）
SMS_SIGNNAME={辣妈羊毛党}

YUNPIAN_APIKEY=your-appkey
YUNPIAN_TEMPLATECONTENT=模板内容   //参考：您的验证码是{verifyCode}，有效期为{time}分钟，请尽快验证

LUOSIMAO_APIKEY=your-appkey
LUOSIMAO_TEMPLATECONTENT=模板内容  //参考：{verifyCode}是您请求的验证码

SUBMAIL_APPID=your-appid
SUBMAIL_APIKEY=your-appkey
SUBMAIL_TEMPLATEID=模板ID          //（project Id）参考：3OZtl

YUNTONGXUN_ACCOUNTSID=your-account-sid
YUNTONGXUN_ACCOUNTTOKEN=your-account-token
YUNTONGXUN_APPID=your-appid
YUNTONGXUN_TEMPLATEID=模板ID      //参考：1~N

ALIYUN_APPKEY=your-appkey
ALIYUN_APPSECRET=your-appsecret
ALIYUN_TEMPLATEID=模板ID         //参考：SMS_57930028

```
## 使用示例

### 1、使用不同的短信平台

``` php
//调用默认短信平台
$smsDriver = Sms::driver();

//调用备用短信平台
$smsDriver = Sms::driver('fallback');

$smsDriver = Sms::driver('aLiYun');

$smsDriver = Sms::driver('yunPian');

$smsDriver = Sms::driver('yunTongXun');

$smsDriver = Sms::driver('subMail');

$smsDriver = Sms::driver('luoSiMao');
```
### 2、程序自带标签变量说明

``` php
{verifyCode} 模板数据验证码变量
{time} 模板数据有效时间变量
['yzm' => 'verifyCode'] 此中verifyCode表示使用程序自动生成的验证码
```

### 3、用户定义模板说明(重要)

   > 官方模板变量格式(###、#变量名#、@var(变量名)等等)，放在程序内统一格式为：{变量名}，变量名和位置应与官方模板变量名和位置保持一致


### 4、基本发送方式

``` php
$mobile = '***********';  //手机号
```

> 使用模板方式发送,无需设置content(如:容联·云通讯、赛邮·云通讯、阿里云短信)

``` php
$templateVar = ['yzm' => 'verifyCode'];          //verifyCode表示使用程序自动生成的验证码
$smsDriver->setTemplateVar($templateVar, true);  //替换模板变量，true表示返回键值对数组，false表示返回值数组
$result = $smsDriver->singlesSend($mobile);    //发送短信,返回结果
```

> 使用内容方式发送,无需设置模板id和模板var(如:云片网络、luosimao)
``` php
$smsDriver->setContentByVerifyCode();
//假设模板内容为：“{verifyCode}是您请求的验证码”，程序转化为：761888是您请求的验证码
$result = $smsDriver->singlesSend($mobile);

Or

$smsDriver->setContentByVerifyCode(20);
//假设模板内容为：“您的验证码是{verifyCode}，有效期为{time}分钟”，程序转化为：您的验证码是761888，有效期为20分钟
$result = $smsDriver->singlesSend($mobile);

Or

$content = '尊敬的用户，您的域名已到期，请及时续费';  //设置短信内容
$smsDriver->setContent($content);
$result = $smsDriver->singlesSend($mobile);
```

### 5、组合发送方式

> 设置签名
``` php
$smsDriver->setSignName('雷神');
```

> 设置内容
``` php
$smsDriver->setContent($content);
```

> 替换内容中验证码变量(程序生成验证码方式)
``` php
$smsDriver->setContentByVerifyCode();
```

> 替换内容中验证码变量(程序生成验证码方式)及有效时间
``` php
$smsDriver->setContentByVerifyCode(20);
```

> 替换内容中自定义变量（array $templateVar）
``` php
$smsDriver->setContentByCustomVar($templateVar);
```

> 设置内容并替换内容的变量（array $templateVar）
``` php
$content = '{name},您的帐号异地登录，如要不是你本人操作，请及时修改密码';  //设置短信内容
$templateVar = ['name' => 'discovery'];  //替换模板内容变量
$smsDriver->setContent($content);
$smsDriver->setContentByCustomVar($templateVar);
//程序转化为：discovery,您的帐号异地登录，如要不是你本人操作，请及时修改密码
```

> 设置模板ID
``` php
$smsDriver->setTemplateId(1);
```

> 替换模板变量，返回值数组（array $templateVar）
``` php
$smsDriver->setTemplateVar($templateVar);

//参考
 array(2) {
   [0]=>
   string(8) "'931101'"
   [1]=>
   string(4) "'10'"
 }
```


> 替换模板变量，true表示返回键值对数组（array $templateVar）
``` php
$smsDriver->setTemplateVar($templateVar, true);

//参考
.array(2) {
  ["verifyCode"]=>
  string(6) "859432"
  ["time"]=>
  string(2) "15"
}
```

> 发送
``` php
$smsDriver->singlesSend($mobile);
```

> 返回拼接后的发送数据
``` php
$smsDriver->singlesSend($mobile, false);
```

### 6、更多组合详细请参见单元测试文件：PhpSmsUnitTest.php

### 7、判断短信发送成功还是失败

   >return array $result  返回数组结果
   
   >return int $result[].code 返回0则成功，返回其它则错误
   
   >return string $result[].msg 返回消息 = "发送成功" Or 短信代理平台提示消息
   
   >return int $result[].verifyCode 验证码 = 程序生成验证码，不使用时返回NULL
   
  
 ``` php
 array(3) {
   ["verifyCode"]=>
   int(977178)
   ["code"]=>
   int(0)
   ["msg"]=>
   string(12) "发送成功"
 }
 ```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

拷贝单元测试文件PhpSmsUnitTest.php到根目录tests文件中

``` bash
$ vendor/bin/phpunit tests/PhpSmsUnitTest.php
```

## Security

> If you discover any security related issues, please email xzadv@126.com instead of using the issue tracker.

> 如果你发现任何相关的问题，请把问题以邮件的形式发送至xzadv@126.com。


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/laravelsms/sms.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://scrutinizer-ci.com/g/phper2013/laravel-sms/badges/build.png?b=master
[ico-scrutinizer]: https://scrutinizer-ci.com/g/phper2013/laravel-sms/badges/quality-score.png?b=master
[ico-code-quality]: https://scrutinizer-ci.com/g/phper2013/laravel-sms/badges/coverage.png?b=master
[ico-downloads]: https://img.shields.io/packagist/dt/laravelsms/sms.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/laravelsms/sms
[link-travis]: https://scrutinizer-ci.com/g/phper2013/laravel-sms/build-status/master
[link-scrutinizer]: https://scrutinizer-ci.com/g/laravelsms/sms/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/phper2013/laravel-sms/badges/coverage.png?b=master
[link-downloads]: https://packagist.org/packages/laravelsms/sms
[link-author]: https://github.com/phper2013
[link-contributors]: ../../contributors
