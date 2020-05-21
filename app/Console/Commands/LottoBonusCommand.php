<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LottoModule\LottoBonus;
use App\Models\LottoModule\Models\BetLog;

class LottoBonusCommand extends Command
{
    protected $description = 'lotto create';

    protected $signature = 'lotto:bonus {--bet_log=}';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('lotto bonus start');

        if ($this->option('bet_log')) {
            $id = $this->option('bet_log');
            LottoBonus::execute($id);
            return $this->info('lotto create success');
        }

        // 获取所有未派奖的lotto_index
        $logs   = BetLog::where('status', 1)->groupBy('lotto_index')->get('lotto_index');
        $lottos = [];
        foreach ($logs as $value) {
            $lottos[$value->lotto_name][] = $value->lotto_id;
        }

        //获取所有未派奖对应的lotto（仅查已开奖）
        $mapping = config('lotto.model.system');
        foreach ($lottos as $key => $value) {
            $model = $mapping[$key];
            $data  = app($model)->whereIn('id', $value)->where('status', 2)->get();
            foreach ($data as $_lotto) {
                $lotto_index = $key . ':' . $_lotto->id;
                dump($lotto_index);
                LottoBonus::batch($lotto_index);
            }
        }

        return $this->info('lotto bonus success');
    }
}
