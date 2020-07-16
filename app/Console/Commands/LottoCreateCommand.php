<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LottoCreateCommand extends Command
{
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
        foreach ($mapping as $model) {
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
