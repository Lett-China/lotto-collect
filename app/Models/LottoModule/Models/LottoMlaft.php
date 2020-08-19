<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\LottoUtils;
use App\Models\LottoModule\Traits\RacingTrait;

class LottoMlaft extends BasicModel
{
    use RacingTrait;

    public $rememberCacheTag = 'lotto_mlaft';

    protected $configs = [
        'next_second'  => 300,
        'first_second' => 32700,
        'last_time'    => '04:04:00',
        'first_time'   => '13:04:00',
        'incrementing' => false,
    ];

    protected $lotto_name = 'mlaft';

    protected $table = 'lotto_mlaft';

    public function lottoOpen($data)
    {
        $lottoAtFix = function ($id) {
            $day_time = strtotime(substr($id, 0, 8) . ' ' . $this->configs['first_time']);

            $current = $day_time + (substr($id, -3) * $this->configs['next_second']);
            return date('Y-m-d H:i:s', $current);
        };

        $lotto_at = $lottoAtFix($data['id']);

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
}
