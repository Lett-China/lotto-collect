<?php

namespace App\Models\LottoModule\Traits;

use App\Models\LottoModule\Models\LottoWarning;
trait ShiShiCaiTrait
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

        $result           = [];
        $result['source'] = $this->win_code;
        $he               = 0;
        foreach ($arr as $value) {
            $he += $value;
        }
        $result['code_he'] = sprintf('%02d', $he);

        return $result;
    }

    public function lottoOpen($data)
    {
        $lottoAtFix = function ($time) {
            $temp_a = strtotime(date('H:i:s', strtotime($time)));
            $temp_b = strtotime($this->configs['first_time']);
            $diff   = intval(($temp_a + 60 - $temp_b) / 1200) * 1200;
            if ($temp_a < $temp_b) {
                $diff -= 1200;
            }
            $result = date(substr($time, 0, 10) . ' H:i:s', $temp_b + $diff);
            return $result;
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

        return 'update';
    }
}
