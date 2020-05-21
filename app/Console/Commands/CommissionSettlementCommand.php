<?php

namespace App\Console\Commands;

use App\Models\Commission;
use App\Models\UserReference;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CommissionSettlementCommand extends Command
{
    protected $description = 'commission settlement';

    protected $signature = 'commission:settlement {--uid=}';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('commission settlement start');

        $references = UserReference::GroupBy('ref_id')->get(['id', 'ref_id']);

        foreach ($references as $key => $value) {
            $date = date('Y-m-d');
            DB::beginTransaction();
            $temp = Commission::settlement($value->ref_id, $date);
            DB::commit();
            $this->comment($value->ref_id . ': ' . $date . ' ' . $temp);
        }

        return $this->info('commission settlement success');
    }
}
