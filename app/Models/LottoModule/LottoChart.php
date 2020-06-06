<?php
namespace App\Models\LottoModule;

class LottoChart
{
    public function chart($chart)
    {
        $mapping = [
            'keno-28' => 'chartKeno28',
            'keno-16' => 'chartKeno16',
        ];

        $fun = $mapping[$chart];
        return $this->$fun();
    }

    public function chartKeno28()
    {
        $items = $this->items;
        $limit = request()->limit ?: 100;

        $_stand     = [1, 3, 6, 10, 15, 21, 28, 36, 45, 55, 63, 69, 73, 75, 75, 73, 69, 63, 55, 45, 36, 28, 21, 15, 10, 6, 3, 1];
        $code_place = [];
        $stand      = [];
        for ($i = 0; $i <= 27; $i++) {
            $code         = sprintf('%02d', $i);
            $stand[$code] = $_stand[$i];
            $code_place[] = $code;
        }

        $stand['single'] = 500;
        $stand['double'] = 500;
        $stand['middle'] = 560;
        $stand['side']   = 440;
        $stand['big']    = 500;
        $stand['small']  = 500;

        $pro_stand = [];
        $pro_real  = [];
        foreach ($stand as $key => $value) {
            $pro_stand[$key] = intval($stand[$key] / 1000 * $limit);
            $pro_real[$key]  = 0;
        }

        foreach ($items as $item) {
            $he = $item->win_extend['code_he'];
            $pro_real[$he] += 1;

            $chart              = ['win_he' => $he];
            $chart['single']    = $he % 2 == 1;
            $chart['double']    = $he % 2 == 0;
            $chart['big']       = $he >= 14;
            $chart['small']     = $he <= 13;
            $chart['middle']    = $he >= 10 && $he <= 17 ? true : false;
            $chart['side']      = !$chart['middle'];
            $chart['mta_big']   = substr($he, -1) >= 5 ? true : false;
            $chart['mta_small'] = !$chart['mta_big'];
            $chart['mod_3']     = $he % 3;
            $chart['mod_4']     = $he % 4;
            $chart['mod_5']     = $he % 5;
            $item['lotto_at']   = date('m-d H:i:s', strtotime($item['lotto_at']));
            $item->chart        = $chart;
            $item->makeHidden('win_extend');
        }

        $result = [
            'items'      => $items->toArray(),
            'code_place' => $code_place,
            'pro_stand'  => $pro_stand,
            'pro_real'   => $pro_real,

        ];

        return $result;

        return $items->toArray();
    }

    public function lotto($lotto_name)
    {
        $limit = request()->limit ?: 100;
        $model = LottoUtils::model($lotto_name);
        $items = $model->where('status', '2');
        $items->take($limit)->orderBy('id', 'desc');
        $items = $items->get();
        $items->makeHidden(['lotto_name', 'bet_count_down', 'status', 'updated_at', 'opened_at', 'logs', 'mark']);
        $this->items = $items;
        return $this;
    }
}
