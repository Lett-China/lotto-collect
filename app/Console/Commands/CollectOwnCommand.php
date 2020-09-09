<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CollectOwnCommand extends Command
{
    protected $description = 'collect own';

    protected $signature = 'collect:own';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('collect own start');
        $mapping = config('lotto.model.system');
        //促发自有盘
        $self = ['de28', 'india28'];

        foreach ($self as $value) {
            if (isset($mapping[$value]) === false) {
                continue;
            }

            $model = $mapping[$value];

            try {
                $result = app($model)->lottoOpen();
            } catch (\Throwable $th) {
                $result = $th->getMessage();
            }

            $message = $model . ' ' . $value . ': ' . $result;
            $this->comment($message);
        }

        return $this->info('collect own success');
    }
}
