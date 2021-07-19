<?php
namespace App\Models\LottoModule\Models;

use QL\QueryList;
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

    public function getWinExtendAttribute()
    {
        if (null === $this->open_code) {
            return null;
        }

        $formula = LottoFormula::bit28($this->open_code);
        $he      = $formula['code_he'];

        $result             = [];
        $result['code_arr'] = $formula['code_arr'];
        $result['code_str'] = $formula['code_str'];
        $result['code_he']  = sprintf('%02d', $he);

        $he >= 14 && $result['code_bos']    = '大';
        $he <= 13 && $result['code_bos']    = '小';
        $he % 2 == 1 && $result['code_sod'] = '单';
        $he % 2 == 0 && $result['code_sod'] = '双';

        return $result;
    }

    public function lottoCollectData()
    {
        $main = function ($backup = false) {
            $uri = 'https://www.blockchain.com/btc/unconfirmed-transactions';
            if ($backup === true) {
                $uri = 'https://blockchain.com/bch/unconfirmed-transactions';
            }
            // $proxy_ip = getProxyIP('bit');
            // $opts     = ['proxy' => $proxy_ip];
            $table = QueryList::get($uri, [], [])->find('.beTSoK');

            $list = $table->find('.hXyplo')->map(function ($row) {
                return $row->find('a')->texts()->all();
            });

            $data = [];
            foreach ($list->toArray() as $key => $value) {
                if (!preg_match('/[a-z\d+]{64}/', $value[0])) {
                    continue;
                }

                $data[] = $value[0];
            }

            $result = ['uri' => $uri, 'data' => $data];
            return $result;
        };

        $collectBC = function () {
            //https://blockchair.com/bitcoin/blocks?

            //获取区块ID
            $uri      = 'https://api.blockchair.com/bitcoin/blocks?limit=15&offset=0';
            $client   = new \GuzzleHttp\Client(['timeout' => 10]);
            $response = $client->get($uri);
            $data     = json_decode($response->getBody(), true);
            $block_id = $data['data'][0]['id'];

            $uri      = 'https://api.blockchair.com/bitcoin/transactions?q=block_id(' . $block_id . ')&limit=100&s=id(asc)';
            $client   = new \GuzzleHttp\Client(['timeout' => 10]);
            $response = $client->get($uri);
            $data     = json_decode($response->getBody(), true);

            $result = ['uri' => $uri, 'data' => []];

            foreach ($data['data'] as $key => $value) {
                $result['data'][] = $value['hash'];
            }

            return $result;
        };

        $cache_name = 'bitCollectData';
        try {
            $collect = $main();
            if (count($collect['data']) > 0) {
                cache()->put($cache_name, $collect, 6000);
            } else {
                $collect = $collectBC();
            }
        } catch (\Throwable $th) {
            $collect = cache()->get($cache_name);
            if ($collect === null) {
                $collect = $collectBC();
            }
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

        $formula     = LottoFormula::bit28($open_code);
        $lotto_index = $this->lotto_name . ':' . $item->id;

        //如果随机到0 27 且没有控制 且有下注重新开始。
        if (in_array($formula['code_he'], [0, 27]) && in_array($item->control, ['he_00', 'he_27']) === false) {
            $count = ControlBet::remember(10)->where('lotto_index', $lotto_index)->count();
            if ($count > 0) {
                goto start;
            }
        }

        //根据下注额控制
        $control_val = $control->formulaBet($lotto_index, $open_code, $this->lotto_name);
        if ($count <= 50) {
            if (($item->control === 'bet' && $control_val > 0) || $control_val >= 200) {
                $count += 1;
                goto start;
            }
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

        LottoUtils::lottoOpenBroadcasts($this->lotto_name, $item->id);
    }
}
