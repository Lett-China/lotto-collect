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
        dd($source);
        if (!isset($source->rows) || $source->rows < 1) {
            $this->comment($source->str);
            return $this->error('error in data');
        }

        $source->code = array_reverse($source->data);

        foreach ($source->data as $value) {
            $cache_name = $value->code . ':' . $value->expect . '===XingCaiCollect';

            if (isset($this->model_mapping[$value->code]) === false) {
                continue;
            }

            $lotto_name = $this->model_mapping[$value->code];
            $model      = $this->lotto_mapping[$lotto_name];
            if (cache()->has($cache_name) === false) {
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
        }

        return $this->info('collect xing_cai success');
    }
}
