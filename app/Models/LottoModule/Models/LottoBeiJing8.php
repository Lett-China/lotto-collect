<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\Traits\Lotto28Trait;

class LottoBeiJing8 extends BasicModel
{
    use Lotto28Trait;

    public $rememberCacheTag = 'lotto_beijing8';

    protected $configs = [
        'next_second'  => 300,
        'first_second' => 33000,
        'last_time'    => '23:55:00',
        'first_time'   => '09:00:00',
        'incrementing' => true,
    ];

    protected $lotto_name = 'bj28';

    protected $table = 'lotto_beijing8';

    public function lottoOpen($data)
    {
        $open_code         = $data['open_code'];
        $data['open_code'] = substr($open_code, 0, strrpos($open_code, '+'));

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

        return 'update';
    }
}
