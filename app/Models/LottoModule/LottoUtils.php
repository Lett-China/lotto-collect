<?php
namespace App\Models\LottoModule;

use Illuminate\Support\Facades\DB;

class LottoUtils
{
    public static function Model($name = '')
    {
        $name || $name = request()->lotto_name;
        $mapping       = config('lotto.model.system');
        $class         = $mapping[$name];
        return app($class);
    }

    public static function XingCaiApi()
    {
        $cache_name  = 'XingCaiApi:Data';
        $cache_data  = cache()->get($cache_name);
        $cache_error = 'XingCaiApi:Error';

        if (cache()->has($cache_error)) {
            dump('data from cache by error');
            return $cache_data;
        }

        $uri = 'http://a.apilottery.com/api/7f9aa602dab0ed2866af8f4a5a8126b6/all/json';

        try {
            $client   = new \GuzzleHttp\Client(['timeout' => 30]);
            $response = $client->get($uri, []);
            $html     = $response->getBody();
            $result   = json_decode($html);
            if (!$result) {
                dump('data from cache by collect error');
                cache()->put($cache_error, 1, 3);
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

    public static function XingCaiApiIssue($tag, $issue)
    {
        $uri = 'http://a.apilottery.com/api/7f9aa602dab0ed2866af8f4a5a8126b6/' . $tag . '/json?issue=' . $issue;

        try {
            $client      = new \GuzzleHttp\Client(['timeout' => 30]);
            $response    = $client->get($uri, []);
            $html        = $response->getBody();
            $result      = json_decode($html);
            $result->str = $html;
        } catch (\Throwable $th) {
            dump($th->getMessage());
            return null;
        }
        return $result;
    }

    public static function lottoOpenBroadcasts($name, $id)
    {
        dump('开奖推送通知开始' . $name . '====' . $id);
        $params = [
            'name' => $name,
            'id'   => $id,
        ];

        try {
            $uri      = DB::table('open_broadcasts')->get();
            $client   = new \GuzzleHttp\Client(['timeout' => 10]);
            $promises = [];
            foreach ($uri as $value) {
                $promises[] = $client->getAsync($value->uri, ['query' => $params]);
            }
            $results = \GuzzleHttp\Promise\unwrap($promises);
        } catch (\Throwable $th) {
            return dump('开奖推送通知失败');
        }

        dump('开奖推送通知完成');
    }
}
