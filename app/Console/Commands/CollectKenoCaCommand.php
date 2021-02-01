<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LottoModule\Models\LottoKenoCa;

class CollectKenoCaCommand extends Command
{
    protected $description = 'collect keno_ca';

    protected $signature = 'collect:keno_ca';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('collect keno_ca start===');

        $date  = date('Y-m-d H:i', strtotime('+30 seconds'));
        $count = LottoKenoCa::where('lotto_at', '<=', $date)->where('status', 1)->count();
        $this->comment('keno_ca has ' . $count);
        if ($count !== 0) {
            $model = new LottoKenoCa();
            $model->officialCheck();
        }

        try {
            $cache_name = 'collectKenoCaHasNoOpen';
            if (cache()->has($cache_name) === true) {
                goto end;
            }
            //卡奖处理
            $date = date('Y-m-d H:i', strtotime('-1 minute'));
            $lose = LottoKenoCa::where('lotto_at', '<=', $date)->where('status', 1)->first(['id', 'lotto_at']);
            if ($lose === null) {
                cache()->put($cache_name, true, 60);
                goto end;
            }

            $this->comment('加拿大Keno === ' . $lose->id . ' 卡奖处理开始...');
            $model = new LottoKenoCa();
            $model->officialCheck($lose->id);

            $this->comment('加拿大Keno === ' . $lose->id . ' 卡奖处理结束...');
        } catch (\Throwable $th) {
            $this->comment('加拿大Keno === ' . $lose->id . ' 卡奖处理失败');
            throw $th;
        }

        end:
        return $this->info('collect keno_ca success');
    }
}
