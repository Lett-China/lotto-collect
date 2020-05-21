<?php
namespace App\Packages\Utils;

use App\Packages\Utils\ClassTrait;

class LettCurl
{
    use ClassTrait;

    public $response = null;

    private $headers = [];

    private $host = null;

    private $isSSL = false;

    private $params = null;

    private $timeout = 30;

    public function content()
    {
        return $this->response;
    }

    public function response()
    {
        return $this->response;
    }

    public function timeout($timeout = 30)
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function toArray()
    {
        return json_decode($this->response, true);
    }

    public function toObject()
    {
        return json_decode($this->response);
    }

    private function curl($isPost = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

        if ($this->isSSL === true) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        if ($isPost === true) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
            curl_setopt($ch, CURLOPT_URL, $this->host);
        }

        if ($isPost === false) {
            if ($this->params) {
                if (is_array($this->params)) {
                    $this->params = http_build_query($this->params);
                }
                curl_setopt($ch, CURLOPT_URL, $this->host . '?' . $this->params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $this->host);
            }
        }

        $response                        = curl_exec($ch);
        $response === false && $response = null;

        // $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);

        $this->response = $response;

        return $this;
    }

    private function get($params = null)
    {
        $this->params = $params;
        return $this->curl(false);
    }

    private function headers($params)
    {
        $parse = [];
        foreach ($params as $key => $value) {
            $parse[] = $key . ': ' . $value;
        }
        $this->headers = $parse;
        return $this;
    }

    private function http($host)
    {
        $this->host = $host;
        return $this;
    }

    private function https($host)
    {
        $this->host  = $host;
        $this->IsSSL = true;
        return $this;
    }

    private function params($params = null)
    {
        $this->params = $params;
        return $this;
    }

    private function post($params = null)
    {
        $this->params = $params;
        return $this->curl(true);
    }

    private function ssl($value = false)
    {
        $this->isSSL = $value;
        return $this;
    }
}
