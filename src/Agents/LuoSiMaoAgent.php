<?php

namespace Laravelsms\Sms\Agents;

use Laravelsms\Sms\Contracts\Sms;
use Illuminate\Support\Arr;

class LuoSiMaoAgent extends Sms
{
    protected $config = [];

    private $apiKey;

    private $host = 'http://sms-api.luosimao.com';
    private $singleSendUrl = '/v1/send.json';

    public function __construct($config)
    {
        $this->config = $config;
        $this->transformConfig();
    }

    protected function transformConfig()
    {
        $this->apiKey = Arr::pull($this->config, 'apiKey');
        $this->setSignName();
    }

    public function singlesSend($mobile, $send = true)
    {
        $url = $this->host . $this->singleSendUrl;

        $postText = $this->content . "【{$this->signName}】";

        $postData = [
            'mobile' => $mobile,
            'message' => $postText,
        ];

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
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->apiKey);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $httpResponse = $this->httpResponse($ch);
        $result = $this->transformerResponse($httpResponse);
        curl_close($ch);

        return $result;
    }

    protected function transformerResponse($httpResponse)
    {
        if (empty($httpResponse['error'])) {
            $response = json_decode($httpResponse['jsonData'], true);
            $result = ['code' => $response['error'], 'msg' => $response['msg'], 'verifyCode' => $this->verifyCode];
            unset($response);
        } else {
            $result = ['code' => time(), 'msg' => $httpResponse['error']];
        }

        return $result;
    }
}
