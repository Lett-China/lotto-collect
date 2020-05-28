<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LottoModule\Models\LottoBeiJing8;

class CollectBeiJing8Command extends Command
{
    protected $description = 'collect beijing8';

    protected $signature = 'collect:bj8';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('collect beijing8 start');

        $cache_name = 'BeiJing8Collect77';
        if (cache()->has($cache_name)) {
            $this->comment('has cache');
        } else {
            $model = new LottoBeiJing8();
            $model->collect77();
            cache()->put($cache_name, 1, 20);
        }

        return $this->info('collect beijing8 success');
    }
}
