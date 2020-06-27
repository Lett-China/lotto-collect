<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LottoModule\LottoUtils;

class CollectOpenCaiCommand extends Command
{
    protected $description = 'collect open_cai';

    protected $signature = 'collect:open_cai';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('collect open_cai start');
        $source = LottoUtils::openCaiAPI('', 'new', null, 5);

        if (!isset($source->rows) || $source->rows < 1) {
            $this->comment($source->str);
            return $this->error('error in data');
        }

        $mapping      = config('lotto.model.collect');
        $source->code = array_reverse($source->data);

        foreach ($source->data as $value) {
            $cache_name = $value->code . ':' . $value->expect . '===openCaiCollect';
            if (isset($mapping[$value->code]) === false) {
                continue;
            }

            $model = $mapping[$value->code];
            if (!cache()->has($cache_name) === false) {
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

            if ($result === 'update') {
                cache()->put($cache_name, 600);
            }

            $message = $model . ' ' . $value->expect . ': ' . $result;
            $this->comment($message);
        }

        return $this->info('collect open_cai success');
    }
}
