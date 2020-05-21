<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BetRebateCommand extends Command
{
    protected $description = 'bet cash back';

    protected $signature = 'bet:rebate {--uid=}';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $date = date('Y-m-d', strtotime('yesterday'));
        $this->info('bet cash back start : ' . $date);
        $bet = DB::table('bet_logs')->where('confirmed_at', '>', $date)->where('confirmed_at', '<=', $date . ' 23:59:59')->groupBy('user_id')->get(['user_id']);
        foreach ($bet as $value) {
            DB::beginTransaction();
            $user = User::find($value->user_id);
            $temp = $user->betRebate($date);
            $this->comment($value->user_id . ': ' . $temp);
            DB::commit();
        }

        return $this->info('bet cash back success : ' . $date);
    }
}
