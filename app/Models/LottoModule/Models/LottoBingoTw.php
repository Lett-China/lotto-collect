<?php
namespace App\Models\LottoModule\Models;

use QL\QueryList;
use App\Models\LottoModule\LottoUtils;
use App\Models\LottoModule\Traits\Lotto28Trait;

class LottoBingoTw extends BasicModel
{
    use Lotto28Trait;

    public $rememberCacheTag = 'lotto_bingo_tw';

    protected $configs = [
        'next_second'  => 300,
        'first_second' => 25800,
        'last_time'    => '23:55:00',
        'first_time'   => '07:05:00',
        'incrementing' => true,
    ];

    protected $lotto_name = 'tw28';

    protected $table = 'lotto_bingo_tw';

    public function lottoOpen($data)
    {
        $open_code         = trimAll($data['open_code']);
        $data['open_code'] = substr($open_code, 0, 59);

        $lottoAtFix = function ($time) {
            $time = strtotime($time);
            $diff = floor($time / 300);
            return date('Y-m-d H:i:s', $diff * 300);
        };

        $lotto_at = $lottoAtFix($data['opened_at']);

        $current = $this->remember(1)->find($data['id']);

        $data['status'] = 2;

        if ($current == null) {
            $data['lotto_at'] = $lotto_at;
            $data['status']   = 2;
            $this->create($data);
            return 'create';
        }

        if ($current->status != 1) {
            return 'status:' . $current->status;
        }

        if ($current->lotto_at === null) {
            $data['lotto_at'] = $lotto_at;
        }

        $current->update($data);
        LottoUtils::lottoOpenBroadcasts($this->lotto_name, $current->id);

        return 'update';
    }

    public function lottoOpenItem($data)
    {
        $opened_at = date('Y-m-d H:i:s');
        $current   = $this->find($data['id']);

        if ($current == null) {
            $data['status']    = 2;
            $data['opened_at'] = $opened_at;
            $this->create($data);
            return 'create';
        }

        if ($current->status != 1) {
            return 'status:' . $current->status;
        }

        $current->opened_at = $opened_at;
        $current->status    = 2;
        $current->open_code = $data['open_code'];
        $current->logs      = '从官方开奖';

        // 提前开奖标示为异常
        if ($current->lotto_at !== null && $current->lotto_at > $opened_at) {
            $current->extend = ['lotto_at' => $lotto_at];
            $current->status = 3;
        }

        $current->save();
        // LottoUtils::lottoOpenBroadcasts($this->lotto_name, $current->id);
        return 'update';
    }

    public function lottoOpenOfficial()
    {
        $client = new \GuzzleHttp\Client(['timeout' => 60]);
        $url    = 'https://www.taiwanlottery.com.tw/lotto/BingoBingo/drawing.aspx';

        dump($url);
        // $proxy_ip  = getProxyIP('tw');
        // $urlParams = [];
        // $opts      = ['proxy' => $proxy_ip];
        // // $opts      = [];
        // $table = QueryList::get($url, $urlParams, $opts)->find('.tableFull');
        // $rows  = $table->find('tr:gt(0)')->map(function ($row) {
        //     return $row->find('td')->texts()->all();
        // });

        $html   = file_get_contents($url);
        $temp   = strstr($html, '<table class=tableFull>');
        $tp     = strpos($temp, '</table>');
        $strlen = strlen($temp);
        $html   = substr($temp, -$strlen, $tp) . '</table>';

        $table = \QL\QueryList::html($html)->find('.tableFull');
        $rows  = $table->find('tr:gt(0)')->map(function ($row) {
            return $row->find('td')->texts()->all();
        });

        $items = $rows->all();
        $items = array_splice($items, 0, 10);

        foreach ($items as $value) {
            if ($value[0] < 11000000) {
                continue;
            }

            $code = explode(' ', $value[1]);
            $code = array_filter($code);
            $code = implode(',', $code);

            $data = ['id' => $value[0], 'open_code' => $code];

            dump($data);

            $this->lottoOpenItem($data);
        }

        return true;
    }

    public function thirdCollect()
    {
        $uri      = 'https://api.518api.com/api?p=json&t=twbg&token=B5F0877278AE9F48&limit=20';
        $client   = new \GuzzleHttp\Client(['timeout' => 3]);
        $response = $client->get($uri);
        $data     = json_decode($response->getBody(), true);

        dump($data);

        try {
            foreach ($data['data'] as $key => $value) {
                $item = [
                    'id'        => $value['expect'],
                    'open_code' => $value['opencode'],
                    'opened_at' => $value['opentime'],
                ];

                $this->lottoOpen($item);
            }
        } catch (\Throwable $th) {
            dump($data);
        }

        return true;
    }
}
