<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\LottoUtils;
use App\Models\LottoModule\Traits\Lotto28Trait;

class LottoKenoCa extends BasicModel
{
    use Lotto28Trait;

    public $rememberCacheTag = 'lotto_keno_ca';

    protected $lotto_name = 'ca28';

    protected $table = 'lotto_keno_ca';

    public function lottoAtUpdate()
    {
        $count = $this->whereNull('lotto_at')->count();
        if ($count === 0) {return true;}

        for ($i = 0; $i < $count; $i++) {
            $data = LottoKenoCa::whereNotNull('lotto_at')->where('status', 2)->orderBy('id', 'desc')->first();
            if (date('H:i:s', strtotime($data->lotto_at)) == '19:00:00') {
                return '新一期开奖时间未开始';
            }

            $next_time = strtotime('+210 second', strtotime($data->lotto_at));
            $next_at   = date('Y-m-d H:i:s', $next_time);

            $next           = LottoKenoCa::find($data->id + 1);
            $next->lotto_at = $next_at;
            $next->save();

            if (date('H:i:s', $next_time) == '19:00:00') {
                return '新一期开奖时间未开始';
            }
        }

        return true;
    }

    public function lottoCreate()
    {
        $count = $this->where('status', 1)->where('lotto_at', '>', date('Y-m-d H:i'))->count();
        if ($count >= 5) {
            return 'lotto limit';
        }

        $count = $this->whereNull('lotto_at')->count();
        if ($count >= 1) {
            return 'lotto null limit';
        }

        $last_lotto  = $this->orderBy('id', 'desc')->first();
        $last_time   = strtotime($last_lotto->lotto_at);
        $next_second = 210; // 默认下一期 增加210秒
        $next_mark   = 0;
        $next_at     = null;

        if ($last_lotto->lotto_at) {
            date_default_timezone_set('America/Vancouver');
            $last_normal = date('I') == '1' ? '19:00:00' : '20:00:00';

            date_default_timezone_set('Asia/Shanghai');

            // 判断上一期是否为最后一期
            if (date('H:i:s', $last_time) == $last_normal || $last_lotto->mark == 2) {
                $next_second = 2100;
                $next_mark   = 1;
            }

            $next_time = $last_time + $next_second;
            // 如果下一次为最后一期 标识
            date('H:i:s', $next_time) == $last_normal && $next_mark = 2;

            $next_at = date('Y-m-d H:i:s', $next_time);
            // 如果上一期为第一期 且未开奖  设置开奖时间为NULL
            if ($last_lotto->status == 1 && $last_lotto->mark == 1) {
                return 'lotto null limit';
            };
        }

        if ($next_at !== null && $next_at < date('Y-m-d H:i:s')) {
            return 'lotto_at error : ' . $next_at;
        }
        if ($next_at !== null && strtotime($next_at) - time() > 1200 && $next_mark != 1) {
            return cache()->remember('CACreateLottoAtSafe', 60, function () use ($next_at, $last_lotto) {
                $content = '【Admin】CA创建新一期 促发安全机制。ID:' . ($last_lotto->id + 1) . ' / ' . $next_at . '。 发送时间：' . date('m-d H:i:s');
                toAdmin($content);
                return $content;
            });
        }

        $data = [
            'id'       => $last_lotto->id + 1,
            'status'   => 1,
            'lotto_at' => $next_at,
            'mark'     => $next_mark,
        ];

        return $this->create($data);
    }

    public function lottoOpen($data)
    {
        $lottoAtFix = function ($cn_time) {
            if ($cn_time === null) {
                return null;
            }
            date_default_timezone_set('Asia/Shanghai');
            $timestamp = strtotime($cn_time);

            date_default_timezone_set('America/Vancouver');
            date('H:i:s', $timestamp) < '04:10:00' && $timestamp -= 86400;

            $first_stamp = strtotime(date('Y-m-d 04:00:00', $timestamp));

            $diff   = ceil(($first_stamp - $timestamp) / 210);
            $result = $first_stamp - ($diff * 210);
            $result += (date('H:i:s', $timestamp) > '04:10:00') ? 90 : 86400;

            date_default_timezone_set('Asia/Shanghai');
            return date('Y-m-d H:i:s', $result);
        };

        $current  = $this->find($data['id']);
        $lotto_at = $lottoAtFix($data['opened_at']);

        if ($current == null && $lotto_at) {
            $data['status']   = 2;
            $data['id']       = $data['id'];
            $data['lotto_at'] = $lotto_at;
            $data['mark']     = 0;
            if (in_array(date('H:i:s', strtotime($lotto_at)), ['19:00:00', '20:00:00'])) {
                $data['mark'] = 2;
            }
            $this->create($data);
            return 'create';
        }

        if ($current->status != 1) {
            return 'status:' . $current->status;
        }

        $data['status'] = 2;

        // 库中的开奖时间与计算的开奖时间不符合 ，标识状态为异常
        if ($lotto_at && $current->lotto_at !== null && $current->lotto_at != $lotto_at) {
            $this->officialCheck();
            if ($current->lotto_at > $lotto_at) {
                $data['status'] = 3;
            }
        }

        // 如果开奖时间为空或第一期，生成开奖时间
        if ($current->mark === 1 || $current->lotto_at === null) {
            if ($lotto_at <= date('Y-m-d H:i:s')) {
                $data['lotto_at'] = $lotto_at;
                $data['status']   = 2;
                $current->update($data);
            } else {
                return $this->officialCheck();
            }
            $this->officialCheck();
        }

        if ($lotto_at === null) {
            $data['logs'] = '无LottoAt';
        }

        $current->update($data);

        // //如果是第一期 更新后面的时间
        if ($current->mark === 1 || $current->lotto_at === null) {
            $this->lottoAtUpdate();
        }

        LottoUtils::lottoOpenBroadcasts($this->lotto_name, $current->id);

        return 'update';
    }

    public function officialCheck($id = null)
    {
        $client = new \GuzzleHttp\Client(['timeout' => 5]);
        // $uri = 'https://www.playnow.com/services2/keno/draw/2020-06-02/21/0';
        $uri        = 'https://www.playnow.com/services2/keno/draw/latest/10/0';
        $id && $uri = 'https://www.playnow.com/services2/keno/draw/' . $id . '/10';

        date_default_timezone_set('America/Vancouver');

        $proxy_ip = getProxyIP('ca');

        if (!$proxy_ip) {
            dump('代理IP没拿到');
            return false;
        }

        dump($proxy_ip);

        try {
            // $urlParams = [];
            // $opts      = ['proxy' => $proxy_ip];
            // $data      = \QL\QueryList::get($uri, $urlParams, $opts);

            // dd($data);

            $options = [
                'proxy'   => ['https' => $proxy_ip],
                'headers' => [
                    'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Encoding'           => 'gzip, deflate, br',
                    'Accept-Language'           => 'en-US,en;q=0.5',
                    'Cache-Control'             => 'max-age=0',
                    'Connection'                => 'keep-alive',
                    'Host'                      => 'www.playnow.com',
                    'Upgrade-Insecure-Requests' => '1',
                    'User-Agent'                => 'Mozilla/5.0 (Windows NT 10.0; rv:78.0) Gecko/20100101 Firefox/78.0',
                ],

            ];
            $response = $client->get($uri, $options);
            $data     = json_decode($response->getBody(), true);
            dump($uri);
        } catch (\Throwable $th) {
            dump('加拿大Keno官方采集失败', $uri);
            return false;
        }
        if ($data == null) {
            dump('加拿大官方 ==== data null');
            return false;
        }
        foreach ($data as $key => $value) {
            date_default_timezone_set('America/Vancouver');
            $datetime = $value['drawDate'] . ' ' . $value['drawTime'];
            $time     = strtotime($datetime);

            //每年3月的第二个星期天，加拿大时间灵成在3/4点，时间偏移1小时
            // if (date('m', $time) === '03'
            //     && ceil(date('j', $time) / 7) == 2
            //     && date('w', $time) === '0'
            //     && in_array(substr($value['drawTime'], 0, 2), ['03', '04'])
            //     && substr($value['drawTime'], -2, 2) === 'AM'
            // ) {
            //     $time += 3600;
            // }

            date_default_timezone_set('Asia/Shanghai');
            $official_at = date('Y-m-d H:i:s', $time);
            $current     = $this->find($value['drawNbr']);

            foreach ($value['drawNbrs'] as $_key => &$_value) {
                $_value = sprintf('%02d', $_value);
            }
            $open_code = implode(',', $value['drawNbrs']);

            if ($current == null) {
                $data = [
                    'id'        => $value['drawNbr'],
                    'open_code' => $open_code,
                    'lotto_at'  => $official_at,
                    'status'    => 2,
                    'logs'      => '从官方创建开奖',
                ];

                $this->create($data);
                $this->lottoCreate();
                dump($value['drawNbr'] . ':' . ' create from  official');
                continue;
            }

            $has_error = false;

            //substr($current->lotto_at, -5, 5) !== substr($official_at, -5, 5)
            if ($current->lotto_at !== $official_at && $official_at <= date('Y-m-d H:i:s')) {
                $warning_type = 'system';
                if ($current->lotto_at > $official_at) {
                    $has_error    = true;
                    $warning_type = 'error';
                }
                LottoWarning::lottoAt($warning_type, __CLASS__, $current->id, $official_at, $current->lotto_at);

                $current->lotto_at = $official_at;
                $current->save();
                $this->where('status', '1')->where('id', '>', $current->id)->delete();
                $this->lottoCreate();

                $content = '【Admin】CA促发修改时间，请及时核对 ' . $current->id . '=' . $official_at;
                toAdmin($content);

                dump($current->id . ':' . $current->lotto_at . ' fix lotto_at');
            } else {
                dump($current->id . ':' . $current->lotto_at . ' success');
            }

            if ($current->open_code === null) {
                $current->open_code            = $open_code;
                $current->status               = 2;
                $has_error && $current->status = 3;
                $current->opened_at            = date('Y-m-d H:i:s');
                $current->logs                 = '从官方开奖';
                $current->save();
                dump($current->id . ':' . $current->open_code . ' open open_code');
                continue;
            }

            if ($current->open_code !== $open_code) {
                LottoWarning::openCode('error', __CLASS__, $current->id, $open_code, $current->open_code);
                $current->open_code = $open_code;
                $current->status    = 3;
                $current->save();
                dump($current->id . ':' . $current->open_code . ' fix open_code from official');
            }
        }
        return true;
    }

    public function thirdCollect()
    {
        $uri      = 'http://api.qqcp.net/test?p=json&t=cakeno&token=715BB95E8C7F1ACD&limit=5';
        $client   = new \GuzzleHttp\Client(['timeout' => 3]);
        $response = $client->get($uri);
        $data     = json_decode($response->getBody(), true);

        try {
            foreach ($data['data'] as $key => $value) {
                $open_code = explode(',', $value['opencode']);

                if ($open_code[19] <= $open_code[18]) {
                    unset($open_code[19]);
                    $open_code[19] = $open_code[18] + 1;
                }
                $open_code = implode(',', $open_code);

                $item = [
                    'id'        => $value['expect'],
                    'open_code' => $open_code,
                    'opened_at' => null,
                ];

                $this->lottoOpen($item);
            }
        } catch (\Throwable $th) {
            dump($data);
        }

        return true;
    }

    public function thirdCollect2()
    {
        $uri = 'https://www.keno100.me/public/json_draw_history.php?city=3&dwi=0&_=1622533908940';

        $options = [
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ];
        $data = file_get_contents($uri, false, stream_context_create($options));

        // $proxy_ip = getProxyIP('tw');
        // $proxy_ip = '101.32.251.31:13488';
        // dump($proxy_ip);
        // $options = [
        //     'proxy'   => ['https' => $proxy_ip],
        //     'headers' => [
        //         'x-requested-with' => 'XMLHttpRequest',
        //     ],

        // ];
        // $client   = new \GuzzleHttp\Client(['timeout' => 5]);
        // $response = $client->get($uri, $options);
        // $data     = json_decode($response->getBody(), true);

        dd($data);

        $data = array_slice(array_reverse($data['d_list']), 0, 10);

        foreach ($data as $item) {
            $str = $item['win_numbers'];
            $arr = explode('</span>', $str);

            $code_arr = [];
            foreach ($arr as $value) {
                $temp = strip_tags($value);
                if (is_numeric($temp)) {
                    $code_arr[] = $temp;
                }
            }

            if (count($code_arr) !== 20) {
                continue;
            }

            $code = implode(',', $code_arr);
            // dump($item['draw'] . '===== ' . $code);

            $item = [
                'id'        => $item['draw'],
                'open_code' => $code,
                'opened_at' => null,
            ];

            dump($item);

            $this->lottoOpen($item);
        }

        return true;
    }

    public function thirdCollect3()
    {
        $date = date('Y-m-d H:i:s');
        $lose = LottoKenoCa::where('status', 1)
        // ->where('lotto_at', '<=', $date)
        // ->orderBy('id', 'desc')
            ->first(['id', 'lotto_at']);

        if ($lose === null) {
            return false;
        }

        $url     = 'https://www.awcpaa.com/api/Number/QueryLotteryNumberByIssueForKenoExtensions?lotteryId=1008&issues=' . $lose->id . '&v=1618571668483';
        $options = [
            'http' => [
                'method'  => 'GET',
                'timeout' => 3,
            ],
            'ssl'  => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ];
        $data = file_get_contents($url, false, stream_context_create($options));
        $data = json_decode($data, true);

        dump($lose->id, $data);

        if (!$data['Result']) {
            return false;
        }

        $data = $data['Result'][0];
        $item = [
            'id'        => $data['Issue'],
            'open_code' => $data['Number'],
            'opened_at' => date('Y-m-d H:i:s'),
        ];

        $this->lottoOpen($item);
    }
}
