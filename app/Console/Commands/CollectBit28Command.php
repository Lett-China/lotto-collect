<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CollectBit28Command extends Command
{
    protected $description = 'collect bit28';

    protected $signature = 'collect:bit28';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('collect bit28 start');
        $result = app('App\Models\LottoModule\Models\LottoBit28')->lottoOpen();
        $this->comment($result);
        return $this->info('collect bit28 success');
    }
}
