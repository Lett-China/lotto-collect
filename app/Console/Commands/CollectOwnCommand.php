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

        $cache_name = 'CollectOwnCommandInCache';
        if (cache()->has($cache_name)) {
            return $this->info('collect own has cache');
        }

        $mapping = config('lotto.model.system');
        $self    = config('lotto.base')['collect_own'];

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

        cache()->put($cache_name, true, 20);
        return $this->info('collect own success');
    }
}
