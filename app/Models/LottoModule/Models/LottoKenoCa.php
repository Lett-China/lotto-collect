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

            // dd(date('Y-m-d H:i:s w', time() + 86400));
            // 判断上一期是否为最后一期
            if (date('H:i:s', $last_time - 86400) == '04:00:00' || $last_lotto->mark == 2) {
                $next_second = 2100;
                $next_mark   = 1;
            }

            $next_time = $last_time + $next_second;
            // 如果下一次为最后一期 标识
            date('H:i:s', $next_time - 86400) == '04:00:00' && $next_mark = 2;

            date_default_timezone_set('Asia/Shanghai');
            $next_at = date('Y-m-d H:i:s', $next_time);
            // 如果上一期为第一期 且未开奖  设置开奖时间为NULL
            if ($last_lotto->status == 1 && $last_lotto->mark == 1) {
                return 'lotto null limit';
            };
        }

        if ($next_at !== null & $next_at < date('Y-m-d H:i:s')) {
            return 'lotto_at error : ' . $next_at;
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

        if ($current == null) {
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
        if ($current->lotto_at !== null && $current->lotto_at != $lotto_at) {
            $this->officialCheck();
            if ($current->lotto_at > $lotto_at) {
                $data['status'] = 3;
            }
        }

        // 如果开奖时间为空或第一期，生成开奖时间
        if ($current->mark === 1 || $current->lotto_at === null) {
            $this->officialCheck();
            $data['lotto_at'] = $lotto_at;
            $data['status']   = 2;
        }

        $current->update($data);

        // //如果是第一期 更新后面的时间
        if ($current->mark === 1 || $current->lotto_at === null) {
            $this->lottoAtUpdate();
        }

        LottoUtils::lottoOpenBroadcasts($this->lotto_name, $current->id);

        return 'update';
    }

    public function officialCheck()
    {
        $client = new \GuzzleHttp\Client(['timeout' => 60]);

        $uri = 'https://www.playnow.com/services2/keno/draw/2020-06-02/21/0';
        $uri = 'https://www.playnow.com/services2/keno/draw/2576727/21';
        $uri = 'https://www.playnow.com/services2/keno/draw/latest/10/0?time=' . time();

        date_default_timezone_set('America/Vancouver');

        $proxy_uri  = 'http://tiqu.linksocket.com:81/abroad?num=2&type=2&pro=0&city=0&yys=0&port=1&flow=1&ts=0&ys=0&cs=0&lb=1&sb=0&pb=4&mr=0&regions=ca&n=0';
        $response   = $client->get($proxy_uri);
        $proxy_data = json_decode($response->getBody(), true);

        if ($proxy_data['code'] !== 0) {
            dump($proxy_data['msg']);
            return false;
        }
        $proxy_ip = $proxy_data['data'][1]['ip'] . ':' . $proxy_data['data'][1]['port'];

        $options  = ['proxy' => ['https' => $proxy_ip]];
        $response = $client->get($uri, $options);
        $data     = json_decode($response->getBody(), true);
        dump($uri);
        if ($data == null) {
            dump('data null', $response->getBody());
            return false;
        }
        dump($data);
        foreach ($data as $key => $value) {
            date_default_timezone_set('America/Vancouver');
            $datetime = $value['drawDate'] . ' ' . $value['drawTime'];
            $time     = strtotime($datetime);
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
            if ($current->lotto_at !== $official_at) {
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
                dump($current->id . ':' . $current->lotto_at . ' fix lotto_at');
            } else {
                dump($current->id . ':' . $current->lotto_at . ' success');
            }

            if ($current->open_code === null) {
                $current->open_code            = $open_code;
                $current->status               = 2;
                $has_error && $current->status = 3;
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
}
