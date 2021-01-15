<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LottoModule\LottoUtils;

class CollectXingCaiCommand extends Command
{
    protected $description = 'collect xing_cai';

    protected $lotto_mapping = [];

    protected $model_mapping = [];

    protected $signature = 'collect:xing_cai';

    public function __construct()
    {
        $this->model_mapping = config('lotto.collect_api.xing_cai');
        $this->lotto_mapping = config('lotto.model.system');
        parent::__construct();
    }

    public function handle()
    {
        $this->info('collect xing_cai start');
        $source = LottoUtils::XingCaiApi();
        if (!isset($source->rows) || $source->rows < 1) {
            $this->comment($source->str);
            return $this->error('error in data');
        }

        dump($source);

        $date = date('Y-m-d H:i', strtotime('-3 minute'));

        foreach ($source->data as $value) {
            $cache_name = $value->enname . ':' . $value->expect . '===XingCaiCollect';

            if (isset($this->model_mapping[$value->enname]) === false) {
                continue;
            }

            $lotto_name = $this->model_mapping[$value->enname];
            $model      = $this->lotto_mapping[$lotto_name];

            if (cache()->has($cache_name) === false) {
                if ($lotto_name === 'xjssc') {
                    $value->expect = substr($value->expect, 0, 8) . '0' . substr($value->expect, 8);
                }

                $data = [
                    'id'        => $value->expect,
                    'open_code' => $value->opencode,
                    'opened_at' => $value->opentime,
                ];

                try {
                    $result = app($model)->lottoOpen($data);
                } catch (\Throwable $th) {
                    $result = $th->getMessage();
                }
            } else {
                $result = 'cache';
            }

            if ($result === 'update' || $result === 'status:2') {
                cache()->put($cache_name, 86400);
            }

            $message = $model . ' ' . $value->expect . ': ' . $result;
            $this->comment($message);

            //卡奖处理
            $lose = app($model)->where('lotto_at', '<=', $date)->where('status', 1)->first(['id', 'lotto_at']);
            if ($lose === null) {continue;}
            if ($lotto_name === 'xjssc') {
                $lose->id = substr($lose->id, 0, 8) . substr($lose->id, 9);
            }

            $this->comment($value->enname . ' === ' . $lose->id . ' 卡奖处理开始...');
            try {
                $source = LottoUtils::XingCaiApiIssue($value->enname, $lose->id);
                $source = $source->data[0];
                if ($lotto_name === 'xjssc') {
                    $source->expect = substr($source->expect, 0, 8) . '0' . substr($source->expect, 8);
                }

                $data = [
                    'id'        => $source->expect,
                    'open_code' => $source->opencode,
                    'opened_at' => $source->opentime,
                ];

                $result = app($model)->lottoOpen($data);

                $this->comment($value->enname . ' === ' . $lose->id . ' 卡奖处理结束...' . $result);
            } catch (\Throwable $th) {
                $this->comment($value->enname . ' === ' . $lose->id . ' 卡奖采集数据失败');
                //throw $th;
            }
        }

        return $this->info('collect xing_cai success');
    }
}
