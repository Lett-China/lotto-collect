<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\LottoFormula;
use App\Models\LottoModule\Models\OpenControl;
use App\Models\LottoModule\Traits\Lotto28Trait;

class LottoBit28 extends BasicModel
{
    use Lotto28Trait;

    public $rememberCacheTag = 'lotto_bit_28';

    protected $configs = [
        'next_second'  => 60,
        'first_second' => 60,
        'last_time'    => '23:59:00',
        'first_time'   => '00:00:00',
        'incrementing' => false,
    ];

    protected $lotto_name = 'bit28';

    protected $table = 'lotto_bit_28';

    public function getWinCodeAttribute()
    {
        if (!$this->open_code) {
            return null;
        }

        $formula = LottoFormula::bit28($this->open_code);
        return (string) $formula['win_code'];
    }

    public function getWinExtendAttribute()
    {
        $result = [];
        $code   = $this->win_code;

        if (null === $code) {
            return null;
        }

        $formula = LottoFormula::bit28($this->open_code);
        $he      = $formula['code_he'];

        $result['code_arr'] = $formula['code_arr'];
        $result['code_he']  = sprintf('%02d', $he);

        $he >= 14 && $result['code_bos']    = '大';
        $he <= 13 && $result['code_bos']    = '小';
        $he % 2 == 1 && $result['code_sod'] = '单';
        $he % 2 == 0 && $result['code_sod'] = '双';

        return $result;
    }

    public function lottoCollectData()
    {
        $backup = function () {
            $client     = new \GuzzleHttp\Client(['timeout' => 10]);
            $proxy_uri  = 'http://tiqu.linksocket.com:81/abroad?num=1&type=2&pro=0&city=0&yys=0&port=1&flow=1&ts=0&ys=0&cs=0&lb=1&sb=0&pb=4&mr=0&regions=www&n=0&f=1';
            $response   = $client->get($proxy_uri);
            $proxy_data = json_decode($response->getBody(), true);
            $proxy_ip   = $proxy_data['data'][0]['ip'] . ':' . $proxy_data['data'][0]['port'];

            $uri      = 'https://etherscan.io/txsPending';
            $option   = ['proxy' => ['https' => $proxy_ip]];
            $response = $client->get($uri, $option);
            $body     = $response->getBody();

            $temp = [];
            preg_match_all('/[a-z\d+]{64}/', $body, $temp);
            $data = array_unique($temp[0]);

            $result = ['body' => $body, 'uri' => $uri, 'data' => $data];
            return $result;
        };

        $main = function () {
            $client   = new \GuzzleHttp\Client(['timeout' => 5]);
            $uri      = 'https://www.blockchain.com/btc/unconfirmed-transactions';
            $response = $client->get($uri);
            $body     = $response->getBody();

            $temp = [];
            preg_match_all('/[a-z\d+]{64}/', $body, $temp);
            $data = array_unique($temp[0]);

            $result = ['body' => $body, 'uri' => $uri, 'data' => $data];
            return $result;
        };

        try {
            $collect = $main();
        } catch (\Throwable $th) {
            $collect = $backup();
        }

        if (count($collect['data']) == 0) {
            $collect = $backup();
        }

        $uri  = $collect['uri'];
        $data = $collect['data'];

        $result = ['uri' => $uri, 'data' => $data];

        return $result;
    }

    public function lottoCreate()
    {
        $count = $this->where('status', 1)->where('lotto_at', '>', date('Y-m-d H:i'))->count();
        if ($count >= 5) {
            return 'lotto limit';
        }

        $last_lotto  = $this->orderBy('id', 'desc')->first();
        $last_time   = strtotime($last_lotto->lotto_at);
        $next_second = $this->configs['next_second'];
        $next_mark   = 0;
        $next_at     = null;
        $next_id     = $last_lotto->id + 1;

        if (date('H:i:s', $last_time) == $this->configs['last_time'] || $last_lotto->mark == 2) {
            $next_second = $this->configs['first_second'];
            $next_mark   = 1;

            if (!isset($this->configs['incrementing']) || $this->configs['incrementing'] !== true) {
                $next_id = date('Ymd', strtotime(substr($last_lotto->id, 0, 8)) + 86400) . '0001';
            }
        }

        $next_time = $last_time + $next_second;

        if ($next_time < time()) {
            $date_day  = date('Y-m-d');
            $time_day  = strtotime($date_day);
            $diff      = (time() - $time_day) / 60;
            $date_id   = sprintf('%04d', intval($diff + 1));
            $next_id   = date('Ymd') . $date_id;
            $next_time = intval($diff) * 60 + $time_day;
        }
        $next_at = date('Y-m-d H:i:s', $next_time);

        date('H:i:s', $next_time) == $this->configs['last_time'] && $next_mark = 2;

        $data = [
            'id'       => $next_id,
            'status'   => 1,
            'lotto_at' => $next_at,
            'mark'     => $next_mark,
        ];

        return $this->create($data);
    }

    public function lottoOpen()
    {
        $items = $this->where('lotto_at', '<=', date('Y-m-d H:i:s'))
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->get();
        $items->makeVisible(['control']);
        foreach ($items as $item) {
            $this->lottoOpenItem($item);
        }

        return 'update';
    }

    public function lottoOpenItem($item)
    {
        $source      = $this->lottoCollectData();
        $collect_uri = $source['uri'];
        $source      = $source['data'];

        $count   = 0;
        $control = new OpenControl();
        start:

        $rand      = array_rand($source, 3);
        $open_code = '';
        foreach ($rand as $value) {
            $open_code .= $source[$value];
        }

        $formula = LottoFormula::bit28($open_code);

        //如果随机到0 27 且没有控制 重新开始。
        if (in_array($formula['code_he'], [0, 27]) && in_array($item->control, ['he_00', 'he_27']) === false) {
            goto start;
        }

        //如果有设置和值 控制
        if (stripos($item->control, 'he_') !== false && $count <= 500) {
            $temp   = explode('_', $item->control);
            $win_he = (int) $temp[1];

            if ($win_he != $formula['code_he']) {
                $count += 1;
                goto start;
            }
        }

        save:
        $item->open_code = $open_code;
        $item->opened_at = date('Y-m-d H:i:s');
        $item->status    = 2;
        $item->logs      = $collect_uri;
        $temp            = $item->save();
    }
}
