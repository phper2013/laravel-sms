<?php

namespace Laravelsms\Sms\Contracts;

abstract class Sms
{
    protected $signName;
    protected $templateId;
    protected $content;
    protected $templateVar = [];
    protected $verifyCode;

    /**
     * @return string
     */
    private function getAgentName()
    {
        $formattedClassName = explode('\\', get_called_class());
        if (count($formattedClassName) > 0) {
            $agentFileName = end($formattedClassName);
            $agents = config("sms.agents");
            foreach ($agents as $key => $value) {
                if (strcmp($value['executableFile'], $agentFileName) == 0)
                    return $key;
            }
        }
        throw new \InvalidArgumentException("Unauthorized access.");
    }

    /**
     * @return string
     */
    protected function getTemplateContentByConfig()
    {
        $name = $this->getAgentName();
        return config("sms.agents.{$name}.templateContent");
    }

    /**
     * @param integer $time
     */
    public function setContentByVerifyCode($time = null)
    {
        $this->verifyCode = $this->makeRandom();
        if (empty($this->content)) {
            $this->content = $this->getTemplateContentByConfig();
        }
        $this->content = str_replace('{verifyCode}', $this->verifyCode, $this->content);
        if (!empty($time)) {
            $this->content = str_replace('{time}', $time, $this->content);
        }
    }

    /**
     * @param array $templateVar
     */
    public function setContentByCustomVar($templateVar = [])
    {
        if (empty($this->content)) {
            $this->content = $this->getTemplateContentByConfig();
        }

        $count = count($templateVar);
        if (is_array($templateVar) && $count > 0) {
            foreach ($templateVar as $key => $value) {
                $this->content = str_replace("{" . $key . "}", $value, $this->content);
            }
        } else {
            $this->content = '';
        }
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = is_string(trim($content)) ? $content : '';
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param array $templateVar
     * @param bool $hasKey
     */
    public function setTemplateVar($templateVar = [], $hasKey = false)
    {
        $count = count($templateVar);

        if (is_array($templateVar) && $count > 0) {
            foreach ($templateVar as $key => $value) {
                if ($hasKey) {
                    if ($value == 'verifyCode') {
                        $this->verifyCode = $this->makeRandom();
                        $this->templateVar[$key] = "$this->verifyCode";
                    } else {
                        $this->templateVar[$key] = "$value";
                    }
                } else {
                    if ($value == 'verifyCode') {
                        $this->verifyCode = $this->makeRandom();
                        $this->templateVar[] = "'" . $this->verifyCode . "'";
                    } else {
                        $this->templateVar[] = "'" . $value . "'";
                    }
                }
            }
        } else {
            $this->templateVar = [];
        }
    }

    /**
     * @return string
     */
    public function getTemplateVar()
    {
        return $this->templateVar;
    }

    /**
     * @param string $signName
     */
    public function setSignName($signName = null)
    {
        $this->signName = trim($signName) ?: trim(config('sms.signName'), '{}');
    }

    /**
     * @return string
     */
    public function getSignName()
    {
        return $this->signName;
    }

    /**
     * @param mixed $id
     */
    public function setTemplateId($id = null)
    {
        $this->templateId = $id ?: 1;
    }

    /**
     * @return string
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * @return void
     */
    abstract protected function transformConfig();

    /**
     * @param string $mobile
     * @param bool $send
     * @return mixed
     */
    abstract protected function singlesSend($mobile, $send = true);

    /**
     * @param string $url
     * @param array $params
     * @return array
     */
    abstract protected function curl($url, $params);

    /**
     * @param $ch
     * @return array
     */
    abstract protected function transformerResponse($ch);

    /**
     * @param $ch
     * @return array
     */
    protected function httpResponse($ch)
    {
        $retry = 0;
        do {
            $jsonData = curl_exec($ch);
            $retry++;
        } while (curl_errno($ch) && $retry < 3);

        if (curl_errno($ch)) {
            $response = ['error' => 1, 'msg' => curl_error($ch)];
        } else {
            $response = ['error' => 0, 'jsonData' => $jsonData];
        }

        return $response;
    }

    /**
     * @return int
     */
    protected function makeRandom()
    {
        return random_int(100000, 999999);
    }
}
