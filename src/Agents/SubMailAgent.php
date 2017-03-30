<?php

namespace Laravelsms\Sms\Agents;

use Laravelsms\Sms\Contracts\Sms;
use Illuminate\Support\Arr;

class SubMailAgent extends Sms
{
    protected $config = [];

    private $appId;
    private $apiKey;

    private $host = 'https://api.mysubmail.com';
    private $singleSendUrl = '/message/xsend.json';
    private $timestampUrl = '/service/timestamp.json';

    public function __construct($config)
    {
        $this->config = $config;
        $this->transformConfig();
    }

    protected function transformConfig()
    {
        $credentials = Arr::pull($this->config, 'credentials');
        $this->appId = Arr::pull($credentials, 'appid');
        $this->apiKey = Arr::pull($credentials, 'apiKey');
        $this->setTemplateId(Arr::pull($this->config, 'templateId'));
        $this->setSignName();
    }

    public function singlesSend($mobile, $send = true)
    {
        $url = $this->host . $this->singleSendUrl;

        $timestamp = $this->getTimestamp();

        $postData = [
            'appid' => $this->appId,
            'to' => $mobile,
            'project' => $this->templateId,
            'vars' => json_encode($this->templateVar),
            'timestamp' => $timestamp,
            'sign_type' => 'sha1',
        ];
        $signature = $this->computeSignature($postData);

        $postData = array_merge(['signature' => $signature], $postData);

        if ($send) {
            return $this->curl($url, $postData);
        }

        return $postData;
    }

    /**
     * @param $url
     * @param array $postData
     * @return array $result
     * @return int $result[].code 返回0则成功，返回其它则错误
     * @return string $result[].msg 返回消息
     * @return mixed $result[].verifyCode 验证码
     */
    protected function curl($url, $postData)
    {
        $headers = array('X-HTTP-Method-Override: post');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $httpResponse = $this->httpResponse($ch);
        $result = $this->transformerResponse($httpResponse);
        curl_close($ch);

        return $result;
    }

    protected function transformerResponse($httpResponse)
    {
        if (empty($httpResponse['error'])) {
            $output = trim($httpResponse['jsonData'], "\xEF\xBB\xBF");
            $response = json_decode($output, true);
            if ($response['status'] == 'success') {
                $result = ['code' => 0, 'msg' => '发送成功', 'verifyCode' => $this->verifyCode];
            } else {
                $result = ['code' => $response['code'], 'msg' => $response['msg']];
            }
            unset($response);
        } else {
            $result = ['code' => time(), 'msg' => $httpResponse['error']];
        }

        return $result;
    }

    private function computeSignature($parameters)
    {
        ksort($parameters);
        reset($parameters);

        $canonicalizedQueryString = "";
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .= $key . "=" . $value . "&";
        }
        $canonicalizedQueryString = rtrim($canonicalizedQueryString, '&');

        $signature = sha1($this->appId . $this->apiKey . $canonicalizedQueryString . $this->appId . $this->apiKey);

        return $signature;
    }

    private function getTimestamp()
    {
        $url = $this->host . $this->timestampUrl;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $output = curl_exec($ch);
        $timestamp = json_decode($output, true);

        return $timestamp['timestamp'];
    }
}
