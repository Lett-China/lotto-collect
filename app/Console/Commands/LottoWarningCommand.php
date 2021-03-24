<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LottoWarningCommand extends Command
{
    protected $description = 'lotto warning';

    protected $signature = 'lotto:warning';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('lotto warning start');

        cache()->remember('lottoWarningHandled', 60, function () {
            $this->info('进入处理');
            $lotto   = ['ca28', 'cw28', 'tw28', 'bit28', 'de28', 'bit28'];
            $mapping = config('lotto.model.system');
            $items   = [];

            $date = date('Y-m-d H:i:s', strtotime('-150 seconds'));
            foreach ($lotto as $name) {
                $model = $mapping[$name];
                $data  = app($model)->where('lotto_at', '<=', $date)->where('status', 1);

                //跳过ca28 第一期
                if ($name === 'ca28') {
                    $item = $data->first();
                    if ($item !== null && $item->mark == 1) {
                        continue;
                    }
                }

                if ($data->exists() === true) {
                    array_push($items, $name);
                }
            }

            if (count($items) > 0) {
                $content = '【Admin】' . implode('、', $items) . '有可能卡了，请关注' . date('m-d H:i:s');
                dump($content);
                toAdmin($content);
            }
        });

        return $this->info('lotto warning success');
    }
}
