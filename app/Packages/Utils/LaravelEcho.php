<?php
namespace App\Packages\Utils;

use App\Packages\Utils\ClassTrait;

class LaravelEcho
{
    use ClassTrait;

    private $app = '';

    private $channels = '';

    private $host = '';

    private $key = '';

    private $port = '';

    private $status = '';

    private $users = '';

    public function __construct()
    {
        $this->key  = env('WS_KEY');
        $this->host = env('WS_HOST');
        $this->port = env('WS_PORT');
        $this->app  = env('WS_APP');
    }

    private function app($value)
    {
        $this->app = $value;
        return $this;
    }

    private function events($params = [])
    {
        $uri     = $this->makeUri('/events');
        $uri     = $uri . '?auth_key=' . $this->key;
        $headers = ['Authorization' => $this->key];
        $result  = LettCurl::http($uri)->timeout(1)->headers($headers)->post(json_encode($params))->toArray();
        return $result;
    }

    private function get($uri)
    {
        $headers = ['Authorization' => $this->key];
        $result  = LettCurl::http($uri)->headers($headers)->get()->toArray();
        return $result;

    }

    private function makeUri($param)
    {
        return $this->host . ':' . $this->port . '/apps/' . $this->app . $param;
    }

    private function status()
    {
        $uri = $this->makeUri('/status');
        return $this->get($uri);
    }

    private function users($channels)
    {
        $channels    = $channels ? '/channels/presence-' . $channels : '/channels';
        $this->users = '/users';
        $uri         = $this->makeUri($channels . '/users');
        return $this->get($uri);
    }
}
