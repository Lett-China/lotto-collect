<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class AppStat extends Model
{
    protected $casts = [
        'user_active'   => 'int',
        'user_register' => 'int',
        'rech_count'    => 'int',
        'rech_total'    => 'decimal:3',
        'rech_award'    => 'decimal:3',
        'draw_count'    => 'int',
        'draw_total'    => 'decimal:3',
        'draw_award'    => 'decimal:3',
        'bet_count'     => 'int',
        'bet_total'     => 'decimal:3',
        'bet_bonus'     => 'decimal:3',
    ];

    // public $timestamps = false;
    protected $connection = 'main_sql';

    protected $fillable = [
        'date',
        'user_active',
        'user_register',
        'rech_count',
        'rech_total',
        'rech_award',
        'draw_count',
        'draw_total',
        'draw_award',
        'bet_count',
        'bet_total',
        'bet_bonus',
    ];

    public static function createData($date = null, $force = false)
    {
        if ($date === null) {
            $date = date('Y-m-d', strtotime('yesterday'));
        }
        $last = $date . ' 23:59:59';

        $data = self::where('date', $date)->first();
        if ($data && $force === false) {
            return true;
        }

        //统计注册用户
        $user_register = DB::table('users')
            ->where('created_at', '>=', $date)
            ->where('created_at', '<=', $last)->count();

        //统计活跃用户
        $user_active = DB::table('users')
            ->where('requested_at', '>=', $date)
            ->where('requested_at', '<=', date('Y-m-d H:i:s', strtotime($last) + 3600))
            ->count();

        //统计提现
        $withdraw = DB::table('balance_withdraws')
            ->where('confirmed_at', '>=', $date)
            ->where('confirmed_at', '<=', $last)
            ->first([
                DB::raw('sum(amount) as total'),
                DB::raw('sum(award) as award'),
                DB::raw('count(id) as count'),
            ]);

        //统计充值
        $recharge = DB::table('balance_recharges')
            ->where('confirmed_at', '>=', $date)
            ->where('confirmed_at', '<=', $last)
            ->first([
                DB::raw('sum(amount) as total'),
                DB::raw('sum(award) as award'),
                DB::raw('count(id) as count'),
            ]);

        //统计下注
        $bet = DB::table('bet_logs')
            ->where('confirmed_at', '>=', $date)
            ->where('confirmed_at', '<=', $last)
            ->first([
                DB::raw('sum(total) as total'),
                DB::raw('sum(bonus) as bonus'),
                DB::raw('count(id) as count'),
            ]);

        $temp = [
            'date'          => $date,
            'user_active'   => $user_active ?: 0,
            'user_register' => $user_register ?: 0,
            'rech_count'    => $recharge->count ?: 0,
            'rech_total'    => $recharge->total ?: 0,
            'rech_award'    => $recharge->award ?: 0,
            'draw_count'    => $withdraw->count ?: 0,
            'draw_total'    => $withdraw->total ?: 0,
            'draw_award'    => $withdraw->award ?: 0,
            'bet_count'     => $bet->count ?: 0,
            'bet_total'     => $bet->total ?: 0,
            'bet_bonus'     => $bet->bonus ?: 0,
        ];

        if ($force === true) {
            unset($temp['user_active']);
            unset($temp['user_register']);
            $data->update($temp);
        } else {
            self::create($temp);
        }

        return true;
    }
}
