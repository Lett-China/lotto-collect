<?php
namespace App\Models\LottoModule\Traits;

trait CommonTrait
{
    public function lottoCreate()
    {
        $count = $this->where('status', 1)->where('lotto_at', '>', date('Y-m-d H:i'))->count();
        if ($count >= 5) {
            return 'lotto limit';
        }

        $last_lotto  = $this->orderBy('id', 'desc')->first();
        $last_time   = strtotime($last_lotto->lotto_at);
        $next_second = $this->configs['next_second'];
        $next_mark   = 0;
        $next_at     = null;
        $next_id     = $last_lotto->id + 1;

        if (date('H:i:s', $last_time) == $this->configs['last_time'] || $last_lotto->mark == 2) {
            $next_second = $this->configs['first_second'];
            $next_mark   = 1;

            if (!isset($this->configs['incrementing']) || $this->configs['incrementing'] !== true) {
                $next_id = date('Ymd', strtotime(substr($last_lotto->id, 0, 8)) + 86400) . '001';
            }
        }

        $next_time = $last_time + $next_second;
        $next_at   = date('Y-m-d H:i:s', $next_time);

        if ($next_at !== null & $next_at < date('Y-m-d H:i:s')) {
            return 'lotto_at error : ' . $next_at;
        }

        date('H:i:s', $next_time) == $this->configs['last_time'] && $next_mark = 2;

        $data = [
            'id'       => $next_id,
            'status'   => 1,
            'lotto_at' => $next_at,
            'mark'     => $next_mark,
        ];

        return $this->create($data);
    }
}
