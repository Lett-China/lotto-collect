<?php
namespace App\Models\LottoModule;

use App\Models\User;
use App\Packages\Utils\PushEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\LottoModule\Models\BetLog;
use App\Models\LottoModule\Models\LottoConfig;

class LottoBonus
{
    public static function batch($lotto_index)
    {
        $data    = BetLog::where('lotto_index', $lotto_index)->get(['id', 'lotto_index']);
        $success = [];
        $error   = [];
        foreach ($data as $value) {
            try {
                $temp                = self::execute($value->id);
                $success[$value->id] = $temp;
            } catch (\Throwable $th) {
                DB::rollBack();
                $error[$value->id] = $th->getMessage();
                dump($error);
            }
        }

        $error && Log::error($error);
    }

    public static function execute($id)
    {
        DB::beginTransaction();
        $bet_log = BetLog::lockForUpdate()->find($id);
        $bet_log || real()->exception('bet.log.is.null');
        $bet_log->status !== 1 && real()->exception('bonus.status.error');

        $lotto  = LottoUtils::model($bet_log->lotto_name)->with('betStats')->find($bet_log->lotto_id);
        $config = LottoConfig::find($bet_log->lotto_name);

        $bet_time  = strtotime($bet_log->created_at);
        $stop_time = strtotime($lotto->lotto_at) - $config->stop_ahead;

        if ($bet_time > $stop_time) {
            real()->exception('bonus.bet_time.error');
        }

        $win_code   = $lotto->win_code;
        $win_extend = $lotto->win_extend;
        //获取开奖计算结果
        $func        = $config->win_function;
        $win_place   = LottoWinPlace::$func($win_code);
        $details     = $bet_log->details;
        $total_bonus = 0;
        foreach ($details as $value) {
            if (!in_array($value->place, $win_place)) {
                $value->bonus = '0.00';
                $value->save() || real()->exception('bonus.log.save.failed');
                continue;
            }

            $odds = $value->odds;

            //28系列
            if ($func == 'lotto28') {
                //28系列外围盘 开13 14 赔率为1
                if (in_array($win_extend['code_he'], ['13', '14']) && strstr($value->place, 'ww_')) {
                    $odds          = '1.000';
                    $value->extend = '外围玩法逢13/14返本';
                }

                //28 期号尾数逢2/8 额外加奖
                $last_id  = substr($bet_log->lotto_id, -1);
                $bonus_28 = config('act.bonus_28');
                $date     = date('Y-m-d');
                if (in_array($last_id, ['2', '8']) && $lotto->opened_at > $bonus_28['start'] && $lotto->opened_at < $bonus_28['end']) {
                    if (in_array($last_id, $win_extend['code_arr'])) {
                        $odds          = bcadd($odds, $bonus_28['odds'], 3);
                        $value->extend = '期号逢2/8额外加奖';
                    }
                }
            }

            //快三系列三军赔率算法
            if ($func == 'kuai3' && strstr($value->place, 'sj_')) {
                $sj_multiple = array_count_values($win_extend['code_arr']);
                $sj_code     = explode('_', $value->place)[1];
                $variable_1  = $sj_multiple[$sj_code];
                $odds        = ($value->odds - 1) * $variable_1 + 1;
            }

            //如果结算赔率跟原始赔率不一致
            $odds == $value->odds || $value->odds_settle = $odds;

            $value->bonus = bcmul($value->amount, $odds, 2);
            $total_bonus += $value->bonus;
            $value->save() || real()->exception('bonus.log.save.failed');
        }

        $bet_log->open_code    = $win_code;
        $bet_log->bonus        = $total_bonus;
        $bet_log->status       = 2;
        $bet_log->confirmed_at = date('Y-m-d H:i:s');
        $bet_log->save() || real()->exception('bonus.save.failed');

        if ($total_bonus > 0) {
            // 添加统计数据
            $user = User::find($bet_log->user_id);

            if ($user->trial !== true) {
                if (null === $lotto->betStats) {
                    $params = [
                        'lotto_index' => $bet_log->lotto_index,
                        'win_total'   => $bet_log->total,
                        'win_people'  => 1,
                    ];
                    $lotto->betStats = $lotto->betStats()->create($params);
                } else {
                    $lotto->betStats->increment('win_total', $bet_log->bonus);
                    $lotto->betStats->increment('win_people', 1);
                }
            }

            // 加钱

            $extend = $bet_log->toArray();
            $temp   = 'lotto.bonus.' . $bet_log->lotto_name;
            $user->wallet->balance($temp, $bet_log->id)->extend($extend)->plus($total_bonus);

            //通知
            $message = [
                'message' => '恭喜您在<' . $config->title . '>中奖了！',
                'bet_log' => $bet_log->id,
                'wallet'  => $user->wallet,
            ];
            PushEvent::name('notify')->toUser($user->id)->data($message);
        }
        DB::commit();
        return true;
    }
}
