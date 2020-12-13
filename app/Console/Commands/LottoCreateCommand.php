<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LottoCreateCommand extends Command
{
    protected $continue = ['jsk3', 'shk3', 'hebk3', 'cqssc', 'tjssc', 'bj28', 'pk10', 'pc28', 'mlaft', 'bjk3', 'hero28'];

    protected $description = 'lotto create';

    protected $signature = 'lotto:create';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('lotto create start');
        $mapping = config('lotto.model.system');

        foreach ($mapping as $key => $model) {
            if (in_array($key, $this->continue)) {
                $this->comment($key . '跳出创建');
                continue;
            }

            try {
                $result = app($model)->lottoCreate();
            } catch (\Throwable $th) {
                $result = $th->getMessage();
            }
            $message = $model . ': ' . $result;
            $this->comment($message);
        }
        return $this->info('lotto create success');
    }
}
