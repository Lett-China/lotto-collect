<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LottoModule\LottoUtils;

class LottoCollectCommand extends Command
{
    protected $description = 'lotto collect';

    protected $signature = 'lotto:collect';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('lotto collect start');
        $source = LottoUtils::openCaiAPI('', 'new', null, 5);

        if (!isset($source->rows) || $source->rows < 1) {
            $this->comment($source->str);
            return $this->error('error in data');
        }

        $mapping = config('lotto.model.collect');

        $source->code = array_reverse($source->data);

        //加拿大西部28开奖
        $cw28    = app('App\Models\LottoModule\Models\LottoKenoCw');
        $is_null = $cw28->where('status', 1)->where('lotto_at', '<', date('Y-m-d H:i:s'))->count();
        if ($is_null > 0) {
            $cw28->lottoOpen();
            $this->comment('keno-cw open null items:' . $is_null);
        }

        //促发自有盘
        $self = ['hero28', 'de28', 'bit28'];
        foreach ($self as $value) {
            $source->data[] = (object) [
                'code'     => $value,
                'expect'   => 'system',
                'opencode' => 'rand',
                'opentime' => date('Y-m-d H:i:s'),
            ];
        }

        foreach ($source->data as $value) {
            if (isset($mapping[$value->code]) === false) {
                continue;
            }

            $model = $mapping[$value->code];
            $data  = [
                'id'        => $value->expect,
                'open_code' => $value->opencode,
                'opened_at' => $value->opentime,
            ];

            try {
                $result = app($model)->lottoOpen($data);
            } catch (\Throwable $th) {
                $result = $th->getMessage();
            }

            $message = $model . ' ' . $data['id'] . ': ' . $result;
            $this->comment($message);
        }

        return $this->info('lotto collect success');
    }
}
