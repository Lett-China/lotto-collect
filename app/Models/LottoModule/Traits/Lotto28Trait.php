<?php
namespace App\Models\LottoModule\Traits;

use Illuminate\Support\Facades\Cache;
use App\Models\LottoModule\LottoFormula;

trait Lotto28Trait
{
    public function getWinCodeAttribute()
    {
        if (!$this->open_code) {
            return null;
        }

        try {
            $lotto_name = $this->lotto_name;
            $formula    = LottoFormula::$lotto_name($this->open_code);
            return (string) $formula['win_code'];
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function getWinExtendAttribute()
    {
        $result = [];
        $code   = $this->win_code;

        if (null === $code) {
            return null;
        }

        $lotto_name = $this->lotto_name;
        if (!$lotto_name) {
            return null;
        }

        $formula = LottoFormula::$lotto_name($this->open_code);
        $he      = $formula['code_he'];

        $result['code_arr'] = $formula['code_arr'];
        $result['code_he']  = sprintf('%02d', $he);

        $he >= 14 && $result['code_bos']    = '大';
        $he <= 13 && $result['code_bos']    = '小';
        $he % 2 == 1 && $result['code_sod'] = '单';
        $he % 2 == 0 && $result['code_sod'] = '双';

        return $result;
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
