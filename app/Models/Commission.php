<?php

namespace App\Models;

use App\Models\CommissionLog;
use App\Models\UserReference;
// use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use App\Models\LottoModule\Models\BetLog;
use App\Models\UserWallet\BalanceRecharge;

class Commission extends Model
{
    protected $connection = 'main_sql';

    protected $fillable = ['user_id', 'total', 'bet', 'recharge', 'date'];

    public function logs()
    {
        return $this->hasMany(CommissionLog::class, 'parent_id', 'id')->with('user:id,nickname');
    }

    public static function settlement($current, $date)
    {
        $cache_name = 'CommissionSettlement' . ':' . $current . ':' . $date;
        if (Cache::has($cache_name)) {
            return true;
        }

        $was = self::where('user_id', $current)->where('date', $date)->first();

        if ($was) {
            Cache::put($cache_name, time(), 86400);
            return true;
        }

        $child = UserReference::with('children')->where('ref_str', 'regexp', $current)->get();

        //组装下级用户 共5级
        $position = [];
        foreach ($child as $value) {
            $temp                      = array_flip($value->ref_str);
            $position[$value->user_id] = [
                'position'     => $temp[$current] + 1,
                'bet_amount'   => 0,
                'bonus_amount' => 0,
                'recharge'     => 0,
            ];
        }

        $child_ids = array_keys($position);

        //查询下级用户的投注记录
        $yesterday = date('Y-m-d', strtotime($date . ' -1 day'));
        $bets      = BetLog::whereIn('user_id', $child_ids)->where('confirmed_at', '>=', $yesterday)->where('bonus', '>', 0)->where('confirmed_at', '<', $date)->get();
        $recharges = BalanceRecharge::whereIn('user_id', $child_ids)->where('confirmed_at', '>=', $yesterday)->where('status', 2)->where('confirmed_at', '<', $date)->get();
        if ($bets->toArray() == []) {
            Cache::put($cache_name, time(), 86400);
            return true;
        }

        //计算下级用户用户的中奖订单的总额和中奖总额
        foreach ($bets as $value) {
            foreach ($value->details as $_value) {
                if ($_value->bonus <= 0) {
                    continue;
                }
                $position[$value->user_id]['bet_amount'] += $_value->amount;
                $position[$value->user_id]['bonus_amount'] += $_value->bonus;
            }
        }

        //计算下级用户的充值信息
        foreach ($recharges as $value) {
            $position[$value->user_id]['recharge'] += $value->amount;
        }

        $comm_total    = 0;
        $comm_recharge = 0;
        $comm_bet      = 0;
        $logs          = [];
        foreach ($position as $key => $value) {
            //计算下注反水
            if ($value['bet_amount'] > 0) {
                $bet_source = $value['bonus_amount'] - $value['bet_amount'];
                $proportion = config('commission')[$value['position']];
                $bet_amount = bcmul($bet_source, $proportion, 2);

                if ($bet_amount == 0) {
                    continue;
                }

                $comm_total += $bet_amount;
                $comm_bet += $bet_amount;
                $logs[] = [
                    'user_id'    => $key,
                    'amount'     => $bet_amount,
                    'proportion' => $proportion,
                    'type'       => 'bet',
                    'source'     => $bet_source,
                    'level'      => $value['position'],
                ];
            }

            //计算充值反水
            if ($value['recharge'] > 0) {
                continue;
                $recharge_source = $value['recharge'];
                $proportion      = config('commission')[$value['position']];
                $recharge_amount = bcmul($recharge_source, $proportion, 2);

                if ($recharge_amount == 0) {
                    continue;
                }

                $comm_total += $recharge_amount;
                $comm_recharge += $recharge_amount;
                $logs[] = [
                    'user_id'    => $key,
                    'amount'     => $recharge_amount,
                    'proportion' => $proportion,
                    'type'       => 'recharge',
                    'source'     => $recharge_source,
                    'level'      => $value['position'],
                ];
            }
        }

        // dd($logs);
        $data = [
            'user_id'  => $current,
            'total'    => $comm_total,
            'bet'      => $comm_bet,
            'recharge' => $comm_recharge,
            'date'     => $date,
        ];

        $create = self::create($data);
        foreach ($logs as $value) {
            $value['parent_id'] = $create->id;
            $temp               = CommissionLog::create($value);
        }

        $user = User::find($current);
        $a    = $user->wallet->balance('level.commission', $create->id)->plus($comm_total);

        Cache::put($cache_name, time(), 86400);
        return true;
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
