<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LottoModule\LottoUtils;

class CollectDataBeCommand extends Command
{
    protected $description = 'collect data_be';

    protected $lotto_mapping = [];

    protected $model_mapping = [];

    protected $signature = 'collect:data_be';

    public function __construct()
    {
        $this->model_mapping = config('lotto.collect_api.data_be');
        $this->lotto_mapping = config('lotto.model.system');
        parent::__construct();
    }

    public function handle()
    {
        $this->info('collect data_be start');
        $source = LottoUtils::dataBeApi(3);

        if (!isset($source->rows) || $source->rows < 1) {
            $this->comment($source->str);
            return $this->error('error in data');
        }

        $source->code = array_reverse($source->data);

        foreach ($source->data as $value) {
            $cache_name = $value->code . ':' . $value->expect . '===dataBeCollect';

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

        return $this->info('collect data_be success');
    }
}
