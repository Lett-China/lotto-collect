<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\LottoUtils;
use App\Models\LottoModule\Traits\Lotto28Trait;

class LottoBingoTw extends BasicModel
{
    use Lotto28Trait;

    public $rememberCacheTag = 'lotto_bingo_tw';

    protected $configs = [
        'next_second'  => 300,
        'first_second' => 26100,
        'last_time'    => '23:55:00',
        'first_time'   => '07:05:00',
        'incrementing' => true,
    ];

    protected $lotto_name = 'tw28';

    protected $table = 'lotto_bingo_tw';

    public function lottoOpen($data)
    {
        $open_code         = trimAll($data['open_code']);
        $data['open_code'] = substr($open_code, 0, 59);

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

        $current->update($data);
        LottoUtils::lottoOpenBroadcasts($this->lotto_name, $current->id);

        return 'update';
    }
}
