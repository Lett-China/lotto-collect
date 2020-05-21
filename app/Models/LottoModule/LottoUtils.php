<?php
namespace App\Models\LottoModule;

class LottoUtils
{
    public static function Model($name = '')
    {
        $name || $name = request()->lotto_name;
        $mapping       = config('lotto.model.system');
        $class         = $mapping[$name];
        return app($class)->setLottoName($name);
    }

    public static function getCodeArea($label, $source)
    {
        $result = ['code' => [], 'total' => 0, 'mantissa' => 0];
        foreach ($label as $value) {
            $code             = (int) $source[$value - 1];
            $result['code'][] = $code;
            $result['total'] += $code;
        }
        $result['mantissa'] = (int) substr($result['total'], -1);
        return $result;
    }

    public static function openCaiAPI($code = '', $type = 'new', $extend = null, $row = 20)
    {
        $cache_name  = 'openCai:Data';
        $cache_data  = cache()->get($cache_name);
        $cache_error = 'openCai:Error';

        if (cache()->has($cache_error)) {
            dump('data from cache by error');
            return $cache_data;
        }

        $host   = 'http://wd.apiplus.net';
        $token  = 'ta232497df0350251k';
        $params = [
            'token'  => $token,
            'code'   => $code,
            'rows'   => $row,
            'format' => 'json',
        ];

        ($type === 'day') && $params['date'] = $extend;

        $mapping = ['new' => '/newly.do', 'day' => '/daily.do'];
        $uri     = $host . $mapping[$type];

        try {
            $client   = new \GuzzleHttp\Client(['timeout' => 30]);
            $response = $client->get($uri, ['query' => $params]);
            $html     = $response->getBody();
            $result   = json_decode($html);
            if (!$result) {
                dump('data from cache by collect error');
                cache()->put($cache_error, 1, 5);
                return $cache_data;
            }
            $result->str = $html;
            cache()->put($cache_name, $result);
        } catch (\Throwable $th) {
            dump($th->getMessage());
            return $cache_data;
        }

        return $result;
    }
}
