<?php

namespace Laravelsms\Sms\Agents;

use Illuminate\Support\Arr;
use Laravelsms\Sms\Contracts\Sms;

class YunTongXunAgent extends Sms
{
    protected $config = [];

    private $accountSid;
    private $appId;

    private $host = 'https://app.cloopen.com:8883';
    private $singleSendUrl = '/2013-12-26/Accounts/{accountSid}/SMS/TemplateSMS?sig={SigParameter}';

    private $sigParameter;
    private $header;

    public function __construct($config)
    {
        $this->config = $config;
        $this->transformConfig();
    }

    protected function transformConfig()
    {
        $postDateTime = date("YmdHis");

        $this->setTemplateId(Arr::pull($this->config, 'templateId'));

        $credentials = Arr::pull($this->config, 'credentials');
        $this->accountSid = Arr::pull($credentials, 'accountSid');
        $accountToken = Arr::pull($credentials, 'accountToken');
        $this->appId = Arr::pull($credentials, 'appId');
        $this->sigParameter = strtoupper(md5($this->accountSid . $accountToken . $postDateTime));

        $authorization = base64_encode($this->accountSid . ":" . $postDateTime);
        $this->header = array(
            'Content-Type:application/json;charset=utf-8',
            'Accept:application/json',
            'Authorization:' . $authorization
        );
    }

    public function singlesSend($mobile, $send = true)
    {
        $url = $this->host . $this->singleSendUrl;
        $url = str_replace('{accountSid}', $this->accountSid, $url);
        $url = str_replace('{SigParameter}', $this->sigParameter, $url);

        $data = '';
        while (list(, $value) = each($this->templateVar)) {
            $data .= $value . ",";
        }
        $data = rtrim($data, ',');

        $postData = "{'to':'$mobile','templateId':'$this->templateId','appId':'$this->appId','datas':[" . $data . "]}";

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
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
        $httpResponse = $this->httpResponse($ch);
        $result = $this->transformerResponse($httpResponse);
        curl_close($ch);

        return $result;
    }

    protected function transformerResponse($httpResponse)
    {
        if (empty($httpResponse['error'])) {
            $response = json_decode($httpResponse['jsonData'], true);
            if ($response['statusCode'] == '000000') {
                $result = ['code' => 0, 'msg' => '发送成功', 'verifyCode' => $this->verifyCode];
            } else {
                $result = ['code' => $response['statusCode'], 'msg' => 'YunTongXun:' . $response['statusCode']];
            }
            unset($response);
        } else {
            $result = ['code' => time(), 'msg' => $httpResponse['error']];
        }

        return $result;
    }
}
