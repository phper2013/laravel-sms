<?php
namespace Laravelsms\Sms;

class UnitTest extends \TestCase
{
    private $mobile = '测试手机号码';

    public function setUp()
    {
        parent::setUp();

        $this->app = $this->createApplication();
    }

    public function manager()
    {
        return new Manager($this->app);
    }

    public function testDriver()
    {
        $manager = $this->manager();
        $smsDriver = $manager->driver('yunTongXun');
        $isObject = is_object($smsDriver);

        $this->assertTrue($isObject);
    }

    public function testGetDefaultSignName()
    {
        $signName = '辣妈羊毛党';

        $manager = $this->manager();
        $smsDriver = $manager->driver('subMail');
        $smsDriver->setSignName();

        $this->assertEquals($signName, $smsDriver->getSignName());
    }

    public function testSetSignName()
    {
        $signName = '钢铁战士';

        $manager = $this->manager();
        $smsDriver = $manager->driver('subMail');
        $smsDriver->setSignName($signName);

        $this->assertEquals($signName, $smsDriver->getSignName());
    }

    public function testSetTemplateId()
    {
        $id = 2;

        $manager = $this->manager();
        $smsDriver = $manager->driver('yunTongXun');
        $smsDriver->setTemplateId($id);

        $this->assertEquals($id, $smsDriver->getTemplateId());
    }

    public function testFactories()
    {
        $factory = new Factory($this->app);
        $factories = $factory->getFactories();

        $this->assertArrayHasKey('aLiYun', $factories);
    }

    public function testSetTemplateVarByDefault()
    {
        $templateVar = ['1' => 'verifyCode', '2' => 10];

        $manager = $this->manager();
        $smsDriver = $manager->driver('yunTongXun');
        $smsDriver->setTemplateVar($templateVar);

        var_dump($smsDriver->getTemplateVar());
    }

    public function testSetTemplateVarByHasKey()
    {
        $templateVar = ['verifyCode' => 'verifyCode', 'time' => 15];

        $manager = $this->manager();
        $smsDriver = $manager->driver('subMail');
        $smsDriver->setTemplateVar($templateVar, true);

        var_dump($smsDriver->getTemplateVar());
    }

    public function testSetTemplateVarByCustomVar()
    {
        $templateVar = ['1' => '123456', '2' => 21];

        $manager = $this->manager();
        $smsDriver = $manager->driver('aLiYun');
        $smsDriver->setTemplateVar($templateVar);

        var_dump($smsDriver->getTemplateVar());
    }

    public function testSetContentByVerifyCode()
    {
        $manager = $this->manager();
        $smsDriver = $manager->driver('luoSiMao');
        $smsDriver->setContentByVerifyCode();

        var_dump($smsDriver->getContent());
    }

    public function testSetContentByVerifyCodeAndTime()
    {
        $manager = $this->manager();
        $smsDriver = $manager->driver('yunPian');
        $smsDriver->setContentByVerifyCode(20);

        var_dump($smsDriver->getContent());
    }

    public function testSetContentByCustomVar()
    {
        $templateVar = ['verifyCode' => random_int(100000, 999999), 'minute' => 360];

        $manager = $this->manager();
        $smsDriver = $manager->driver('luoSiMao');
        $smsDriver->setContentByCustomVar($templateVar);

        var_dump($smsDriver->getContent());
    }

    public function testSetContent()
    {
        $content = '您的帐号异地登录，如要不是你本人操作，请及时修改密码';  //设置短信内容

        $manager = $this->manager();
        $smsDriver = $manager->driver('yunPian');
        $smsDriver->setContent($content);

        var_dump($smsDriver->getContent());
    }

    public function testSetContentAndSetContentByCustomVar()
    {
        $content = '{name}，您的帐号异地登录，如要不是你本人操作，请及时修改密码';  //设置短信内容
        $templateVar = ['name' => 'discovery'];

        $manager = $this->manager();
        $smsDriver = $manager->driver('luoSiMao');
        $smsDriver->setContent($content);
        $smsDriver->setContentByCustomVar($templateVar);

        var_dump($smsDriver->getContent());
    }

    /*******************************  以下请根据实际需要打开没试  ******************************************/

    /*******************************  返回拼接后的待发送数据 分割线  ***************************************/

    /**
     * 【ALiYun】
     * 官方模板格式：${verifyCode}是您请求的验证码
     * 如果使用系统内验证码，请使用verifyCode标签
     */
    public function testGetALiYunData()
    {
        var_dump('【ALiYun】');

        $templateVar = ['verifyCode' => 'verifyCode'];

        $manager = $this->manager();
        $smsDriver = $manager->driver('aLiYun');
        $smsDriver->setSignName('雷神');
        $smsDriver->setTemplateVar($templateVar, true);
        $result = $smsDriver->singlesSend($this->mobile, false);

        var_dump($result);
    }

    /**
     * 【luoSiMao】返回拼接后的待发送数据
     * 不支持营销短信、全变量短信模板
     * 官方模板格式：###是您请求的验证码【{signName}】，代码中签名在后面，发送到手机上自动变为在前面。
     * 程序中替换变量名：{verifyCode}是您请求的验证码
     * 请用标准变量名代替###，以方便程序拼接数据，变量顺序和官方模板###位置顺序保持一致
     */
    public function testGetLuoSiMaoData()
    {
        var_dump('【luoSiMao】');

        $manager = $this->manager();
        $smsDriver = $manager->driver('luoSiMao');
        $smsDriver->setContentByVerifyCode();
        $result = $smsDriver->singlesSend($this->mobile, false);

        var_dump($result);
    }

    /**
     * 【赛邮云通信xsend发送方式】返回拼接后的待发送数据
     * 官方模板格式：您的验证码是@var(verifyCode)，有效期为@var(minute)分钟，请尽快验证
     * 如果使用系统默认验证码，请使用verifyCode标签
     */
    public function testGetSubMailData()
    {
        var_dump('【赛邮云通信】');

        $templateVar = ['verifyCode' => random_int(100000, 999999), 'minute' => 60];

        $manager = $this->manager();
        $smsDriver = $manager->driver('subMail');
        $smsDriver->setSignName('辣妈测试');
        $smsDriver->setTemplateVar($templateVar, true);
        $result = $smsDriver->singlesSend($this->mobile, false);

        var_dump($result);
    }

    /**
     * 【云片网】返回拼接后的待发送数据
     * 官方模板格式：您的验证码是#code#，有效期为#time#分钟，请尽快验证
     * 程序中替换变量名：您的验证码是{verifyCode}，有效期为{time}分钟，请尽快验证
     */
    public function testGetYunPianData()
    {
        var_dump('【云片网】');

        $manager = $this->manager();
        $smsDriver = $manager->driver('yunPian');
        $smsDriver->setContentByVerifyCode(20);
        $result = $smsDriver->singlesSend($this->mobile, false);

        var_dump($result);
    }

    /**
     * 【云通讯】返回拼接后的待发送数据
     * 官方测试模板ID：1
     * 官方测试模板：【云通讯】您使用的是云通讯短信模板，您的验证码是{1}，请于{2}分钟内正确输入。
     * 如果使用系统默认验证码，请使用verifyCode标签
     */
    public function testGetYunTongXunData()
    {
        var_dump('【云通讯】');

        $templateVar = ['1' => 'verifyCode', '2' => 15];

        $manager = $this->manager();
        $smsDriver = $manager->driver('yunTongXun');
        $smsDriver->setTemplateId(1);
        $smsDriver->setTemplateVar($templateVar);
        $result = $smsDriver->singlesSend($this->mobile, false);

        var_dump($result);
    }

    /*******************************  实际发送 分割线  **********************************************/

    /**
     * 【ALiYun】
     * 官方模板格式：${verifyCode}是您请求的验证码
     * 如果使用系统内验证码，请使用verifyCode标签
     */
//    public function testALiYunAgent()
//    {
//        var_dump('【ALiYun】');
//
//        $templateVar = ['verifyCode' => 'verifyCode'];
//
//        $manager = $this->manager();
//        $smsDriver = $manager->driver('aLiYun');
//        $smsDriver->setSignName('雷神');
//        $smsDriver->setTemplateVar($templateVar, true);
//        $result = $smsDriver->singlesSend($this->mobile);
//
//        var_dump($result);
//    }

    /**
     * 【luoSiMao】
     * 不支持营销短信、全变量短信模板
     * 官方模板格式：###是您请求的验证码【{signName}】，代码中签名在后面，发送到手机上自动变为在前面。
     * 程序中替换变量名：{verifyCode}是您请求的验证码
     * 请用标准变量名代替###，以方便程序拼接数据，变量顺序和官方模板###位置顺序保持一致
     */
//    public function testLuoSiMaoAgent()
//    {
//        var_dump('【luoSiMao】');
//
//        $manager = $this->manager();
//        $smsDriver = $manager->driver('luoSiMao');
//        $smsDriver->setContentByVerifyCode();
//        $result = $smsDriver->singlesSend($this->mobile);
//
//        var_dump($result);
//    }

//    public function testLuoSiMaoAgentByCustomContent()
//    {
//        var_dump('【luoSiMao Custom Content】');
//
//        $content = '{name}，您的帐号异地登录，如要不是你本人操作，请及时修改密码';  //设置短信内容
//        $templateVar = ['name' => 'discovery'];
//
//        $manager = $this->manager();
//        $smsDriver = $manager->driver('luoSiMao');
//        $smsDriver->setContent($content);
//        $smsDriver->setContentByCustomVar($templateVar);
//        $result = $smsDriver->singlesSend($this->mobile);
//
//        var_dump($result);
//    }

    /**
     * 【赛邮云通信xsend发送方式】
     * 官方模板格式：您的验证码是@var(verifyCode)，有效期为@var(minute)分钟，请尽快验证
     * 如果使用系统默认验证码，请使用verifyCode标签
     */
//    public function testSubMailAgent()
//    {
//        var_dump('【赛邮云通信】');
//
//        $templateVar = ['verifyCode' => random_int(100000, 999999), 'minute' => 60];
//
//        $manager = $this->manager();
//        $smsDriver = $manager->driver('subMail');
//        $smsDriver->setSignName('辣妈测试');
//        $smsDriver->setTemplateVar($templateVar, true);
//        $result = $smsDriver->singlesSend($this->mobile);
//
//        var_dump($templateVar['verifyCode']);
//        var_dump($result);
//    }

    /**
     * 【云片网】
     * 官方模板格式：您的验证码是#code#，有效期为#time#分钟，请尽快验证
     * 程序中替换变量名：您的验证码是{verifyCode}，有效期为{time}分钟，请尽快验证
     */
//    public function testYunPianAgent()
//    {
//        var_dump('【云片网】');
//
//        $manager = $this->manager();
//        $smsDriver = $manager->driver('yunPian');
//        $smsDriver->setContentByVerifyCode(20);
//        $result = $smsDriver->singlesSend($this->mobile);
//
//        var_dump($result);
//    }

    /**
     * 【云通讯】
     * 官方测试模板ID：1
     * 官方测试模板：【云通讯】您使用的是云通讯短信模板，您的验证码是{1}，请于{2}分钟内正确输入。
     * 如果使用系统默认验证码，请使用verifyCode标签
     */
//    public function testYunTongXunAgent()
//    {
//        var_dump('【云通讯】');
//
//        $templateVar=['1'=>'verifyCode','2'=>15];
//
//        $manager = $this->manager();
//        $smsDriver = $manager->driver('yunTongXun');
//        $smsDriver->setTemplateId(1);
//        $smsDriver->setTemplateVar($templateVar);
//        $result = $smsDriver->singlesSend($this->mobile);
//
//        var_dump($result);
//    }
}
