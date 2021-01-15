<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CollectBingoTwCommand extends Command
{
    protected $description = 'collect bingo_tw';

    protected $signature = 'collect:bingo_tw';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('collect bingo_tw start');

        $tw28    = app('App\Models\LottoModule\Models\LottoBingoTw');
        $is_null = $tw28->where('status', 1)->where('lotto_at', '<', date('Y-m-d H:i:s'))->count();
        if ($is_null > 0) {
            $tw28->lottoOpenOfficial();
            $this->comment('bingo_tw open null items:' . $is_null);
        }

        return $this->info('collect bingo_tw success');
    }
}
