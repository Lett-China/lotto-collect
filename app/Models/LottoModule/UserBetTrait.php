<?php
namespace App\Models\LottoModule;

use App\Packages\Utils\PushEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\LottoModule\Models\BetLog;
use App\Models\LottoModule\Models\BetRebate;
use App\Models\LottoModule\Models\LottoConfig;

trait UserBetTrait
{
    protected $lotto = null;

    public function bet($name, $id)
    {
        $this->lotto = LottoUtils::model($name)->with('betStats')->find($id);
        $this->lotto || real()->exception('lotto.notexist');

        $down = $this->lotto->bet_count_down - 2;
        $down <= 0 && real()->exception('当前期号已截止下注 请重新下注');

        $config = LottoConfig::find($name);
        $config->disable_status && real()->exception('该游戏已暂停下注<br>详情请关注公告');

        return $this;
    }

    public function betLog()
    {
        return $this->hasMany(BetLog::class, 'user_id', 'id');
    }

    //每日反水
    public function betRebate($date)
    {
        $config = config('act.bet_rebate');

        if ($config['ratio'] === null) {
            return null;
        }

        $cache_name = 'betCashBack' . ':' . $this->id . ':' . $date;
        // if (Cache::has($cache_name)) {
        //     return true;
        // }

        $was = BetRebate::where('user_id', $this->id)->where('date', $date)->first();

        if ($was) {
            Cache::put($cache_name, time(), 86400);
            return true;
        }

        $data = DB::table('bet_logs')->where('user_id', $this->id);
        $data->where('confirmed_at', '>=', $date);
        $data->where('confirmed_at', '<=', $date . ' 23:59:59');

        $bet   = $data->first([DB::raw('SUM(total) as bet'), DB::raw('SUM(bonus) as bonus')]);
        $stats = [
            'profit' => sprintf('%01.2f', $bet->bonus - $bet->bet),
            'bet'    => sprintf('%01.2f', $bet->bet),
            'bonus'  => sprintf('%01.2f', $bet->bonus),
        ];

        if ($config['limit'] > abs($stats['profit']) || $stats['profit'] > 0) {
            // dump($stats['profit']);
            return 'limit ' . $stats['profit'];
        }

        $ratio = config('act.bet_rebate.ratio');
        $award = abs(bcmul($stats['profit'], $config['ratio'], 2));

        if ($award <= 0) {
            return true;
        }

        $temp = [
            'date'    => $date,
            'user_id' => $this->id,
            'profit'  => $stats['profit'],
            'bet'     => $stats['bet'],
            'bonus'   => $stats['bonus'],
            'award'   => $award,
            'ratio'   => $config['ratio'],
        ];

        $create = BetRebate::create($temp);

        $this->wallet->balance('bet.lose.rebate', $create->id)->remark(date('Y年m月d日', strtotime($date)) . '亏顺返利')->plus($award);
        return true;
    }

    public function place($params, $total_check = null, $amount = null)
    {
        $lotto_name  = $this->lotto->lotto_name;
        $lotto_index = $this->lotto->lotto_index;

        $config = LottoConfig::find($lotto_name);

        $amount > $config->bet_quota->max && real()->exception('单注下注金额最大' . $config->bet_quota->max . '元<br>请修改后重新下注');
        $amount < $config->bet_quota->min && real()->exception('单注下注金额最低' . $config->bet_quota->min . '元<br>请修改后重新下注');

        DB::beginTransaction();

        $total = 0;
        foreach ($params as $value) {
            $total = bcadd($total, $value['amount'], 2);
        }

        if ($total == 0) {
            real()->exception('bet.log.create.failed');
        }
        // 插入投注记录
        $log_params = [
            'user_id'     => $this->id,
            'trial'       => $this->trial,
            'lotto_index' => $lotto_index,
            'total'       => $total,
            'amount'      => $amount,
        ];

        $bet_log = BetLog::create($log_params);
        $bet_log || real()->exception('bet.log.create.failed');
        $this->betLog = $bet_log;

        $places = $config->place_setting;

        foreach ($params as $value) {
            $place = $value['place'];
            try {
                $value['name'] = $places->$place->name;
                $value['odds'] = $places->$place->odds;
            } catch (\Throwable $th) {
                real()->exception('系统配置错误 请刷新后重试');
            }
            $bet_log->details()->create($value);
        }

        if ($total_check && $total != $total_check) {
            return real()->exception('总额校验失败，请重新提交');
        }

        // 扣除用户零钱
        $source = 'lotto.bet.' . $lotto_name;
        $extend = $bet_log->toArray();
        $this->wallet->balance($source, $bet_log->id)->extend($extend)->minus($total);

        // 插入统计信息
        $statistic = $this->lotto->betStats;

        if ($this->trial !== true) {
            if (null === $statistic->id) {
                $params = [
                    'lotto_index' => $lotto_index,
                    'bet_total'   => $total,
                    'bet_people'  => 1,
                ];
                $statistic = $this->lotto->betStats()->create($params);
            } else {
                $statistic->increment('bet_total', $total);
                $statistic->increment('bet_people', 1);
            }
        }

        DB::commit();

        $message = BetLog::with('user:id,nickname')
            ->with('details:name,log_id,name,place,amount')
            ->find($bet_log->id)
            ->makeHidden(['id', 'status', 'bonus', 'updated_at', 'confirmed_at', 'open_code', 'extend']);
        $message->message_type = 'bet';

        $result = $message->toArray();
        unset($result['user']['id']);
        $push = PushEvent::name('chatBet')->toPresence('lotto.' . $lotto_name)->data($result);

        return true;
    }
}
