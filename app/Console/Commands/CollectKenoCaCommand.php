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
        $this->info('collect keno_ca start');
        $model = new LottoKenoCa();
        $model->officialCheck();
        return $this->info('collect keno_ca success');
    }
}
