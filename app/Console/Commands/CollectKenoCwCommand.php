<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CollectKenoCwCommand extends Command
{
    protected $description = 'collect keno_cw';

    protected $signature = 'collect:keno_cw';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('collect keno_cw start');

        //加拿大西部28开奖
        $cw28    = app('App\Models\LottoModule\Models\LottoKenoCw');
        $is_null = $cw28->where('status', 1)->where('lotto_at', '<', date('Y-m-d H:i:s'))->count();
        if ($is_null > 0) {
            $cw28->lottoOpen();
            $this->comment('keno-cw open null items:' . $is_null);
        }

        return $this->info('collect keno_cw success');
    }
}
