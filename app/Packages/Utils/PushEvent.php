<?php

namespace App\Packages\Utils;

class PushEvent
{
    use ClassTrait;

    private $channel = '';

    private $data = '';

    private $name = '';

    public function __construct()
    {
        $config     = config('broadcasting.laravel_echo_server');
        $this->key  = $config['key'];
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->app  = $config['app'];
    }

    private function data($data)
    {
        $this->data = $data;
        return $this->send();
    }

    private function name($name)
    {
        $this->name = $name;
        return $this;
    }

    private function send()
    {
        $params = [
            'channel' => $this->channel,
            'name'    => 'App\Events\\' . ucwords($this->name),
            'data'    => $this->data,
        ];
        return LaravelEcho::events($params);
    }

    private function toPresence($channel)
    {
        $this->channel = 'presence-' . $channel;
        return $this;
    }

    private function toUser($id)
    {
        $this->channel = 'private-user.' . $id;
        return $this;
    }

    private function users($channel)
    {
        return LaravelEcho::users($channel);
    }
}
