<?php
namespace App\Models\LottoModule;

class LottoChart
{
    public function chart($chart)
    {
        $mapping = [
            'keno-28' => 'chartKeno28',
            'keno-16' => 'chartKeno16',
            'keno-11' => 'chartKeno11',
        ];

        $fun = $mapping[$chart];
        return $this->$fun();
    }

    public function chartKeno11()
    {
        {
            $items = $this->items;

            $limit = request()->limit ?: 100;

            $_stand     = [22, 55, 83, 111, 138, 166, 138, 111, 83, 55, 27];
            $code_place = [];
            $stand      = [];
            for ($i = 2; $i <= 12; $i++) {
                $code         = sprintf('%02d', $i);
                $stand[$code] = $_stand[$i - 2];
                $code_place[] = $code;
            }

            $stand['single'] = 500;
            $stand['double'] = 500;
            $stand['middle'] = 666;
            $stand['side']   = 333;
            $stand['big']    = 583;
            $stand['small']  = 416;

            $pro_stand = [];
            $pro_real  = [];
            foreach ($stand as $key => $value) {
                $pro_stand[$key] = intval($stand[$key] / 1000 * $limit);
                $pro_real[$key]  = 0;
            }

            foreach ($items as $item) {
                $he = $item->win_ext_el['code_he'];
                $pro_real[$he] += 1;

                $chart              = ['win_he' => $he];
                $chart['single']    = $he % 2 == 1;
                $chart['double']    = $he % 2 == 0;
                $chart['big']       = $he >= 7;
                $chart['small']     = $he <= 6;
                $chart['middle']    = $he >= 5 && $he <= 9 ? true : false;
                $chart['side']      = !$chart['middle'];
                $chart['mta_big']   = substr($he, -1) >= 5 ? true : false;
                $chart['mta_small'] = !$chart['mta_big'];
                $chart['mod_3']     = $he % 3;
                $chart['mod_4']     = $he % 4;
                $chart['mod_5']     = $he % 5;
                $item['lotto_at']   = date('m-d H:i:s', strtotime($item['lotto_at']));

                $chart['single'] && $pro_real['single']++;
                $chart['double'] && $pro_real['double']++;
                $chart['big'] && $pro_real['big']++;
                $chart['small'] && $pro_real['small']++;
                $chart['side'] && $pro_real['side']++;
                $chart['middle'] && $pro_real['middle']++;

                $item->chart = $chart;
                $item->makeHidden('win_extend');
            }

            $result = [
                'items'      => $items->toArray(),
                'code_place' => $code_place,
                'pro_stand'  => $pro_stand,
                'pro_real'   => $pro_real,

            ];

            return $result;
        }
    }

    public function chartKeno16()
    {
        $items = $this->items;

        $limit = request()->limit ?: 100;

        $_stand     = [4, 13, 27, 46, 69, 97, 115, 125, 125, 115, 97, 69, 46, 27, 13, 4];
        $code_place = [];
        $stand      = [];
        for ($i = 3; $i <= 18; $i++) {
            $code         = sprintf('%02d', $i);
            $stand[$code] = $_stand[$i - 3];
            $code_place[] = $code;
        }

        $stand['single'] = 500;
        $stand['double'] = 500;
        $stand['middle'] = 675;
        $stand['side']   = 324;
        $stand['big']    = 500;
        $stand['small']  = 500;

        $pro_stand = [];
        $pro_real  = [];
        foreach ($stand as $key => $value) {
            $pro_stand[$key] = intval($stand[$key] / 1000 * $limit);
            $pro_real[$key]  = 0;
        }

        foreach ($items as $item) {
            $he = $item->win_ext_st['code_he'];

            $pro_real[$he] += 1;

            $chart              = ['win_he' => $he];
            $chart['single']    = $he % 2 == 1;
            $chart['double']    = $he % 2 == 0;
            $chart['big']       = $he >= 11;
            $chart['small']     = $he <= 10;
            $chart['middle']    = $he >= 8 && $he <= 13 ? true : false;
            $chart['side']      = !$chart['middle'];
            $chart['mta_big']   = substr($he, -1) >= 5 ? true : false;
            $chart['mta_small'] = !$chart['mta_big'];
            $chart['mod_3']     = $he % 3;
            $chart['mod_4']     = $he % 4;
            $chart['mod_5']     = $he % 5;
            $item['lotto_at']   = date('m-d H:i:s', strtotime($item['lotto_at']));
            $chart['single'] && $pro_real['single']++;
            $chart['double'] && $pro_real['double']++;
            $chart['big'] && $pro_real['big']++;
            $chart['small'] && $pro_real['small']++;
            $chart['side'] && $pro_real['side']++;
            $chart['middle'] && $pro_real['middle']++;
            $item->chart = $chart;
            $item->makeHidden('win_extend');
        }

        $result = [
            'items'      => $items->toArray(),
            'code_place' => $code_place,
            'pro_stand'  => $pro_stand,
            'pro_real'   => $pro_real,

        ];

        return $result;
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

            $chart['single'] && $pro_real['single']++;
            $chart['double'] && $pro_real['double']++;
            $chart['big'] && $pro_real['big']++;
            $chart['small'] && $pro_real['small']++;
            $chart['side'] && $pro_real['side']++;
            $chart['middle'] && $pro_real['middle']++;
            $item->chart = $chart;
            $item->makeHidden('win_extend');
        }

        $result = [
            'items'      => $items->toArray(),
            'code_place' => $code_place,
            'pro_stand'  => $pro_stand,
            'pro_real'   => $pro_real,

        ];

        return $result;
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
