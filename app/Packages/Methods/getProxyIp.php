<?php

function getProxyIP($country = 'ca')
{
    start:
    $client = new \GuzzleHttp\Client(['timeout' => 60]);
    $uris   = [
        'ca'  => 'http://tiqu.linksocket.com:81/abroad?num=2&type=2&pro=0&city=0&yys=0&port=1&flow=1&ts=0&ys=0&cs=0&lb=1&sb=0&pb=4&mr=0&regions=ca&n=0',
        'bit' => 'http://tiqu.linksocket.com:81/abroad?num=2&type=2&pro=0&city=0&yys=0&port=1&flow=1&ts=0&ys=0&cs=0&lb=1&sb=0&pb=4&mr=0&regions=www&n=0&f=1',
        'tw'  => 'http://tiqu.linksocket.com:81/abroad?num=2&type=2&lb=1&sb=0&flow=1&regions=tw&n=0',
    ];

    $proxy_uri  = $uris[$country];
    $response   = $client->get($proxy_uri);
    $proxy_data = json_decode($response->getBody(), true);

    if ($proxy_data['code'] === 113) {
        $ip = request()->server()['REMOTE_ADDR'];
        dump($ip);
        $uri = 'api.ipidea.net/index/index/save_white?neek=11573&appkey=922adc79c4ab1a29741042a8a85c9bc3&white=' . $ip;
        $client->get($uri);
        return false;
    }

    if ($proxy_data['code'] !== 0) {
        dump($proxy_data['msg']);
        return false;
    }

    try {
        $proxy_ip = $proxy_data['data'][1]['ip'] . ':' . $proxy_data['data'][1]['port'];
    } catch (\Throwable $th) {
        $proxy_ip = $proxy_data['data'][0]['ip'] . ':' . $proxy_data['data'][0]['port'];
    }

    return $proxy_ip;
}
