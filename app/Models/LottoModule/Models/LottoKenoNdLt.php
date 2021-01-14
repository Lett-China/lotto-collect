<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\LottoUtils;
use App\Models\LottoModule\LottoFormula;
use App\Models\LottoModule\Traits\Lotto28Trait;

class LottoKenoNdLt extends BasicModel
{
    use Lotto28Trait;

    public $rememberCacheTag = 'lotto_keno_ndl_lt';

    protected $lotto_name = 'nd28';

    protected $table = 'lotto_keno_ndl_lt';

    public function lottoCreate()
    {
        $count = $this->where('status', 1)->where('lotto_at', '>', date('Y-m-d H:i'))->count();
        if ($count >= 5) {
            return 'lotto limit';
        }

        $last_lotto  = $this->orderBy('id', 'desc')->first();
        $last_time   = strtotime($last_lotto->lotto_at);
        $next_second = 60;
        $next_mark   = 0;
        $next_at     = null;

        $next_time = $last_time + $next_second;
        $new_id    = $last_lotto->id + 1;
        if ($next_time < time()) {
            $time_a    = time();
            $time_b    = strtotime('2020-07-01');
            $diff      = intval(($time_a - $time_b) / 60) + 1;
            $new_id    = 100000 + $diff;
            $next_time = $diff * 60 + $time_b;
        }

        $next_at = date('Y-m-d H:i:s', $next_time);

        if ($next_at !== null & $next_at < date('Y-m-d H:i:s')) {
            return 'lotto_at error : ' . $next_at;
        }

        $data = [
            'id'       => $new_id,
            'status'   => 1,
            'lotto_at' => $next_at,
            'mark'     => $next_mark,
        ];

        return $this->create($data);
    }

    public function lottoOpen()
    {
        $items = $this->where('lotto_at', '<=', date('Y-m-d H:i:s'))
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->get();
        $items->makeVisible(['control']);
        foreach ($items as $item) {
            $this->lottoOpenItem($item);
        }

        return 'update';
    }

    public function lottoOpenItem($item)
    {
        $count   = 0;
        $control = new OpenControl();
        start:

        $open_code = $control->openCode();
        $formula   = LottoFormula::basic28($open_code);

        $lotto_index = $this->lotto_name . ':' . $item->id;

        //如果随机到0 27 且没有控制 且有下注重新开始。
        if (in_array($formula['code_he'], [0, 27]) && in_array($item->control, ['he_00', 'he_27']) === false) {
            $count = ControlBet::remember(10)->where('lotto_index', $lotto_index)->count();
            if ($count > 0) {
                goto start;
            }
        }

        //根据下注额控制
        $control_val = $control->formulaBet($lotto_index, $open_code, $this->lotto_name);
        dump($control_val);
        if ($count <= 50) {
            if (($item->control === 'bet' && $control_val > 0) || $control_val >= 200) {
                $count += 1;
                goto start;
            }
        }
        //如果有设置和值 控制
        if (stripos($item->control, 'he_') !== false) {
            $temp   = explode('_', $item->control);
            $win_he = (int) $temp[1];
            $source = $control->query()->where('win_he', $win_he)->where('status', 1)->first();

            if ($source === null) {
                goto save;
            }

            $open_code         = $source->open_code;
            $source->status    = 2;
            $source->used_at   = date('Y-m-d H:i:s');
            $source->used_info = [
                'lotto_id'    => $item->id,
                'lotto_model' => __CLASS__,
            ];
            $source->save();
        }

        save:
        $item->open_code = $open_code;
        $item->opened_at = date('Y-m-d H:i:s');
        $item->status    = 2;
        $item->save();

        LottoUtils::lottoOpenBroadcasts($this->lotto_name, $item->id);
    }
}
