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

            $date = date('Y-m-d H:i', strtotime('-2 minute'));
            foreach ($lotto as $name) {
                $model = $mapping[$name];
                $data  = app($model)->where('lotto_at', '<=', $date)->where('status', 1)->exists();

                if ($data === true) {
                    array_push($items, $name);
                }
            }

            if (count($items) > 0) {
                $content = '【Admin】' . implode('、', $items) . '触发BUG，请及时处理';
                toAdmin($content);
            }
        });

        return $this->info('lotto warning success');
    }
}
