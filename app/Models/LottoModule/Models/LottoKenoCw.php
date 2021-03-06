<?php

namespace App\Models\LottoModule\Models;

use QL\QueryList;
use App\Models\LottoModule\LottoUtils;
use App\Models\LottoModule\Traits\Lotto28Trait;

class LottoKenoCw extends BasicModel
{
    use Lotto28Trait;

    public $rememberCacheTag = 'lotto_keno_cw';

    protected $lotto_name = 'cw28';

    protected $table = 'lotto_keno_cw';

    public function lottoAtUpdate()
    {
        $data = $this->whereNull('lotto_at')->where('status', 2);
        if ($data->count() === 0) {
            return true;
        }

        $items = $data->orderBy('id', 'desc')->get();

        foreach ($items as $item) {
            $next = $this->find($item->id + 1);
            if ($next === null || $next->lotto_at === null) {
                continue;
            }

            $fix_time = 300;

            $next_time = strtotime($next->lotto_at);
            date_default_timezone_set('America/Vancouver');

            if (date('H:i:s', $next_time - 86400) == '03:30:00') {
                $fix_time = 7200;
            }

            date_default_timezone_set('Asia/Shanghai');

            $last_time = strtotime($next->lotto_at) - $fix_time;
            $current   = date('Y-m-d H:i:s', $last_time);

            $item->lotto_at = $current;
            $item->save();
        }

        return true;
    }

    public function lottoCreate()
    {
        $count = $this->where('status', 1)->where('lotto_at', '>', date('Y-m-d H:i'))->count();
        if ($count >= 5) {
            return 'lotto limit';
        }

        $last_lotto  = $this->orderBy('id', 'desc')->first();
        $last_time   = strtotime($last_lotto->lotto_at);
        $next_second = 300;
        $next_mark   = 0;
        $next_at     = null;

        if ($last_lotto->lotto_at) {
            date_default_timezone_set('America/Vancouver');
            $last_normal = date('I') == '1' ? '16:30:00' : '17:30:00';

            date_default_timezone_set('Asia/Shanghai');
            // 判断上一期是否为最后一期
            if (date('H:i:s', $last_time) == $last_normal || $last_lotto->mark == 2) {
                $next_second = 7200;
                $next_mark   = 1;
            }

            $next_time = $last_time + $next_second;
            // 如果下一次为最后一期 标识
            date('H:i:s', $next_time) == $last_normal && $next_mark = 2;

            $next_at = date('Y-m-d H:i:s', $next_time);
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

        // 库中的开奖时间与计算的开奖时间不符合 ，标识状态为异常
        if ($current->lotto_at !== null && $current->lotto_at != $lotto_at) {
            $warning_type = 'warning';
            if ($current->lotto_at > $lotto_at) {
                $data['status'] = 3;
                $warning_type   = 'error';
            }
            LottoWarning::lottoAt($warning_type, __CLASS__, $current->id, $lotto_at, $current->lotto_at);
        }

        $current->update($data);

        LottoUtils::lottoOpenBroadcasts($this->lotto_name, $current->id);

        return 'update';
    }

    public function lottoOpenDrawNum($id)
    {
        $url = 'http://www.wclc.com/winning-numbers/keno.htm?drawNum=' . $id;
        // $proxy_ip  = getProxyIP('ca');
        // $urlParams = [];
        // $opts      = ['proxy' => $proxy_ip];
        // $table     = QueryList::get($url, $urlParams, $opts)->find('.kenoTable');

        $html  = file_get_contents($url);
        $table = QueryList::html($html)->find('.kenoTable');
        $rows  = $table->find('tr:gt(0)')->map(function ($row) {
            return $row->find('td')->texts()->all();
        });

        $items = array_reverse($rows->all());
        foreach ($items as $item) {
            $code = array_splice($item, 1);
            $data = [
                'id'        => $item[0],
                'open_code' => implode(',', $code),
            ];
            $this->lottoOpenItem($data);
        }

        $this->lottoAtUpdate();

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
        LottoUtils::lottoOpenBroadcasts($this->lotto_name, $current->id);
        return 'update';
    }

    public function lottoOpenOfficial($date = null, $limit = 50)
    {
        $date  = $date ?: date('m/d/Y');
        $items = $this->collectData($date);

        if (count($items) === 0) {
            $date  = date('m/d/Y', strtotime('yesterday'));
            $items = $this->collectData($date);
        }

        $items = array_splice($items, 0, $limit);

        foreach ($items as $item) {
            $code = array_splice($item, 1);
            $data = [
                'id'        => $item[0],
                'open_code' => implode(',', $code),
            ];

            $this->lottoOpenItem($data);
        }

        $this->lottoAtUpdate();

        return 'update';
    }

    public function thirdCollect()
    {
        $uri      = 'https://api.518api.com/api?p=json&t=jndxbkl8&token=B5F0877278AE9F48&limit=5';
        $client   = new \GuzzleHttp\Client(['timeout' => 3]);
        $response = $client->get($uri);
        $data     = json_decode($response->getBody(), true);

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

    private function collectData($date)
    {
        // $url = 'https://www.wclc.com/winning-numbers/keno.htm?channel=print&selDate=' . $date;
        // $proxy_ip  = getProxyIP('ca');
        // $opts      = ['proxy' => $proxy_ip];
        // $table = QueryList::get($url, [], $opts)->find('.kenoTable');

        try {
            $url  = 'https://www.wclc.com/winning-numbers/keno.htm?channel=print&selDate=' . $date;
            $html = file_get_contents($url);
        } catch (\Throwable $th) {
            $url  = 'http://www.wclc.com/winning-numbers/keno.htm';
            $html = file_get_contents($url);
        }

        $table = QueryList::html($html)->find('.kenoTable');

        $rows = $table->find('tr:gt(0)')->map(function ($row) {
            return $row->find('td')->texts()->all();
        });

        $items = array_reverse($rows->all());
        return $items;
    }
}
