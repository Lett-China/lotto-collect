<?php
function toAdmin($content)
{
    $url    = 'https://api.smsbao.com/sms';
    $params = [
        'u' => 'gd8888',
        'p' => md5('aa101088'),
        'm' => '15520722572',
        'c' => $content,
    ];

    $client   = new \GuzzleHttp\Client(['timeout' => 10.0]);
    $response = $client->get($url, ['query' => $params]);
    $result   = json_decode($response->getBody(), true);

    return $result === 0 ? true : false;

    $url    = 'https://api.smsbao.com/wsms';
    $params = [
        'u' => 'gd8888',
        'p' => md5('aa101088'),
        'm' => '+971585085888',
        'c' => $content,
    ];

    $client   = new \GuzzleHttp\Client(['timeout' => 10.0]);
    $response = $client->get($url, ['query' => $params]);
    $result   = json_decode($response->getBody(), true);

    return $result === 0 ? true : false;
}
