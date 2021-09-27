<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LottoModule\Models\LottoMsHongKong;

class CollectMsHongKongCommand extends Command
{
    protected $description = 'collect mark six hongkong';

    protected $signature = 'collect:mshk';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('collect mshk start===');

        $date  = date('Y-m-d H:i:s', strtotime('+30 seconds'));
        $count = LottoMsHongKong::where('lotto_at', '<=', $date)->where('status', 1)->count();
        $this->comment('mshk has ' . $count);

        $model = new LottoMsHongKong();
        if ($count !== 0) {
            $model->thirdCollect();
        }
        $model->lottoCreate();

        return $this->info('collect mshk success');
    }
}
