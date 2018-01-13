<?php

namespace Laravelsms\Sms\Agents;

use Laravelsms\Sms\Contracts\Sms;
use Illuminate\Support\Arr;

class QQYunAgent extends Sms
{
    private $config = [];

    private $appId;
    private $appKey;
    private $strRand;

    private $host = 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms';

    public function __construct($config)
    {
        $this->config = $config;
        $this->transformConfig();
    }

    protected function transformConfig()
    {
        $credentials = Arr::pull($this->config, 'credentials');
        $this->appId = Arr::pull($credentials, 'appId');
        $this->appKey = Arr::pull($credentials, 'appKey');
        $this->setSignName();
        $this->setTemplateId(Arr::pull($this->config, 'templateId'));
        $this->strRand = $this->makeRandom();
    }

    private function computeSignature($parameters)
    {
        $canonicalizedQueryString = '';
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .= '&' . $key . '=' . $value;
        }

        return hash("sha256", "appkey=" . $this->appKey . $canonicalizedQueryString);
    }

    private function formatMobile($phoneNumber)
    {
        if (is_array($phoneNumber)) {
            $to = array(
                'nationcode' => $phoneNumber[0],
                'mobile' => $phoneNumber[1]
            );
        } else {
            $to = array(
                'nationcode' => '86',
                'mobile' => $phoneNumber
            );
        }

        return $to;
    }

    public function singlesSend($mobile, $send = true)
    {

        $mobile = $this->formatMobile($mobile);

        $url = "{$this->host}?sdkappid={$this->appId}&random={$this->strRand}";

        $requestParams = array(
            'tel' => $mobile,
            'sign' => $this->signName,
            'tpl_id' => (int)($this->templateId),
            'params' => $this->templateVar
        );

        if ($send) {
            return $this->curl($url, $requestParams);
        }

        return $requestParams;
    }

    /**
     * @param $url
     * @param array $requestParams
     * @return array $result
     * @return int $result[].code 返回0则成功，返回其它则错误
     * @return string $result[].msg 返回消息
     * @return mixed $result[].verifyCode 验证码
     */
    protected function curl($url, $requestParams)
    {
        $timestamp = time();

        $publicParams = array(
            'time' => $timestamp,
            'extend' => '',
            'ext' => ''
        );

        $parameters = array(
            'random' => $this->strRand,
            'time' => $timestamp,
            'mobile' => $requestParams['tel']['mobile']
        );

        $signature = $this->computeSignature($parameters);
        $publicParams = array_merge(['sig' => $signature], $publicParams);
        $publicParams = array_merge($requestParams, $publicParams);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($publicParams));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $httpResponse = $this->httpResponse($ch);
        $result = $this->transformerResponse($httpResponse);
        curl_close($ch);

        return $result;
    }

    protected function transformerResponse($httpResponse)
    {
        if (empty($httpResponse['error'])) {
            $response = array_except(
                json_decode($httpResponse['jsonData'], true),
                ['ext', 'sid', 'fee']
            );
            if ($response['result'] === 0) {
                $result = ['code' => 0, 'msg' => '发送成功', 'verifyCode' => $this->verifyCode];
            } else {
                $errmsg = 'result; ' . $response['result'] . ', errmsg: ' . $response['errmsg'];
                $result = ['code' => time(), 'msg' => $errmsg];
            }
            unset($response);
        } else {
            $result = ['code' => time(), 'msg' => $httpResponse['error']];
        }

        return $result;
    }
}
