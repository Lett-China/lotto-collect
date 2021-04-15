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

        //每N分钟直接采集官方
        $cache_name = 'CollectKenoCaCommandOfficialCheck';
        if (cache()->has($cache_name) === false) {
            $model = new LottoKenoCa();
            dump('===直接采集官方===');
            $model->officialCheck();
            cache()->put($cache_name, true, 90);
        }

        $date  = date('Y-m-d H:i:s', strtotime('+30 seconds'));
        $count = LottoKenoCa::where('lotto_at', '<=', $date)->where('status', 1)->count();
        $this->comment('keno_ca has ' . $count);

        if ($count !== 0) {
            $model = new LottoKenoCa();
            $model->officialCheck();
            // $model->thirdCollect();
            $model->thirdCollect2();
        }

        try {
            $cache_name = 'collectKenoCaHasNoOpen';
            if (cache()->has($cache_name) === true) {
                goto end;
            }
            //卡奖处理
            $date = date('Y-m-d H:i');
            $lose = LottoKenoCa::where('lotto_at', '<=', $date)->where('status', 1)->first(['id', 'lotto_at']);
            if ($lose === null) {
                cache()->put($cache_name, true, 30);
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
