<?php

namespace App\Models\LottoModule\Traits;

use Illuminate\Support\Facades\Cache;
use App\Models\LottoModule\LottoFormula;
use App\Models\LottoModule\LottoWinPlace;

trait Lotto28Trait
{
    public function getWinExtElAttribute()
    {
        if ($this->open_code === null) {
            return null;
        }

        $formula            = LottoFormula::basic11($this->open_code);
        $formula['code_he'] = sprintf('%02d', $formula['code_he']);
        return $formula;
    }

    public function getWinExtStAttribute()
    {
        if ($this->open_code === null) {
            return null;
        }

        $formula            = LottoFormula::basic16($this->open_code);
        $formula['code_he'] = sprintf('%02d', $formula['code_he']);
        return $formula;
    }

    public function getWinExtendAttribute()
    {
        if ($this->open_code === null) {
            return null;
        }
        $lotto_name = $this->lotto_name;
        $formula    = LottoFormula::$lotto_name($this->open_code);
        $he         = $formula['code_he'];

        $result['code_arr'] = $formula['code_arr'];
        $result['code_str'] = $formula['code_str'];
        $result['code_he']  = sprintf('%02d', $he);

        $he >= 14 && $result['code_bos']    = '大';
        $he <= 13 && $result['code_bos']    = '小';
        $he % 2 == 1 && $result['code_sod'] = '单';
        $he % 2 == 0 && $result['code_sod'] = '双';

        $win_place = LottoWinPlace::lotto28($this->open_code, $lotto_name);

        $ts                = ['ts_leo' => '豹', 'ts_pai' => '对', 'ts_jun' => '顺', 'ts_juh' => '半', 'ts_oth' => '杂'];
        if (strpos($win_place[0], 'ts_') !== false) {
            $result['code_ts'] = $ts[$win_place[0]];
        }


        return $result;
    }

    public function getWinPlaceAttribute()
    {
        if ($this->open_code === null) {
            return null;
        }

        $lotto_name = $this->lotto_name;
        $win_place  = LottoWinPlace::lotto28($this->open_code, $lotto_name);
        return $win_place;
    }

    public function openedExtend($refresh = false)
    {
        $cache_name = 'OpenedExtendLotto28:' . __CLASS__;
        $cache_has  = cache()->has($cache_name);

        if ($cache_has === false || $refresh === true) {
            $data = $this->where('status', '!=', 1)->orderBy('id', 'desc')->remember(60)->take(5000);

            $items = $data->get();
            $count = count($items->toArray());

            $result = [];

            for ($i = 0; $i <= 27; $i++) {
                $key          = sprintf('%02d', $i);
                $result[$key] = ['miss' => $count, 'hot' => 0];
            }

            $index = 0;
            foreach ($items as $index => $item) {
                $code_he = $item->win_extend['code_he'];
                if ($index < 1000) {
                    $result[$code_he]['hot'] += 1;
                }
                if ($result[$code_he]['miss'] === $count) {
                    $result[$code_he]['miss'] = $index;
                }

                $index++;
            }

            cache()->put($cache_name, $result);
        }

        return cache()->get($cache_name);
    }
}
