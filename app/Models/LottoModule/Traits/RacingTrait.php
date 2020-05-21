<?php

namespace App\Models\LottoModule\Traits;

trait RacingTrait
{
    public function getWinCodeAttribute()
    {
        return $this->open_code;
    }

    public function getWinExtendAttribute()
    {
        if (!$this->win_code) {
            return null;
        }
        $arr = explode(',', $this->win_code);

        $result = [];

        $result['source']   = $this->win_code;
        $result['code_gyh'] = sprintf('%02d', ($arr[0] + $arr[1]));
        return $result;
    }

    public function openedExtend()
    {
        return [];
        $items  = $this->where('status', '!=', 1)->orderBy('id', 'desc')->remember(60)->take(100)->get();
        $result = [];

        for ($i = 0; $i <= 10; $i++) {
            $key          = sprintf('%02d', $i);
            $result[$key] = ['miss' => 100, 'hot' => 0];
        }

        foreach ($items as $index => $item) {
            $code_he = $item->win_extend['code_he'];
            $result[$code_he]['hot'] += 1;
            if ($result[$code_he]['miss'] === 100) {
                $result[$code_he]['miss'] = $index;
            }
        }

        return $result;
    }
}
