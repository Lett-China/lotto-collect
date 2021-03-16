<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LottoModule\Models\LottoKenoCw;

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

        if (date('i') % 5 == 0) {
            sleep(10);
        }

        //加拿大西部28开奖
        $cw28    = app('App\Models\LottoModule\Models\LottoKenoCw');
        $is_null = $cw28->where('status', 1)->where('lotto_at', '<', date('Y-m-d H:i:s', strtotime('+90 seconds')))->count();
        if ($is_null > 0) {
            $cw28->lottoOpenOfficial();
            $cw28->thirdCollect();
            $this->comment('keno-cw open null items:' . $is_null);
        }

        try {
            //卡奖处理
            $date = date('Y-m-d H:i', strtotime('-1 minute'));
            $lose = LottoKenoCw::where('lotto_at', '<=', $date)->where('status', 1)->first(['id', 'lotto_at']);
            if ($lose === null) {
                goto end;
            }

            $this->comment('加拿大西部Keno === ' . $lose->id . ' 卡奖处理开始...');
            $model = new LottoKenoCw();
            $model->lottoOpenDrawNum($lose->id);

            $this->comment('加拿大西部Keno === ' . $lose->id . ' 卡奖处理结束...' . $result);
        } catch (\Throwable $th) {
            $this->comment('加拿大西部Keno === ' . $lose->id . ' 卡奖处理失败');
            //throw $th;
        }

        end:

        return $this->info('collect keno_cw success');
    }
}
