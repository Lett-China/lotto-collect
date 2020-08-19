<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\Traits\RacingTrait;

class LottoPK10 extends BasicModel
{
    use RacingTrait;

    public $rememberCacheTag = 'lotto_pk10';

    public $timestamps = false;

    protected $configs = [
        'next_second'  => 1200,
        'first_second' => 34800,
        'last_time'    => '23:50:00',
        'first_time'   => '09:30:00',
        'incrementing' => true,
    ];

    protected $lotto_name = 'pk10';

    protected $table = 'lotto_pk10';

    public function lottoOpen($data)
    {
        $lottoAtFix = function ($time) {
            $next_second = $this->configs['next_second'];
            $first_time  = $this->configs['first_time'];
            $temp_a      = strtotime(date('H:i:s', strtotime($time)));
            $temp_b      = strtotime($first_time);
            $diff        = intval(($temp_a - $temp_b) / $next_second) * $next_second;
            $result      = date(substr($time, 0, 10) . ' H:i:s', $temp_b + $diff);
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
