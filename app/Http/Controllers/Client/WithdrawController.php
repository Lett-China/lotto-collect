<?php

namespace App\Http\Controllers\Client;

use App\Models\BankCard;
use Illuminate\Http\Request;
use App\Packages\Utils\PushEvent;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ModelTrait\ThisUserTrait;
use App\Models\LottoModule\Models\BetLog;
use App\Models\UserWallet\BalanceRecharge;
use App\Models\UserWallet\BalanceWithdraw;

class WithdrawController extends Controller
{
    use ThisUserTrait;

    public function create(Request $request)
    {
        $this->user->CheckIsTrial();
        $this->user->real_name || real()->exception('您未补充真实姓名，暂不可申请提现');
        $this->user->safe_word || real()->exception('您未设置安全密码 请先设置安全密码后再提现');
        $rule = [
            'amount'    => 'required|currency',
            'card_id'   => 'required|int',
            'safe_word' => 'required|int',
        ];
        $data    = $request->all();
        $message = [
            'amount.required'    => '请输入提现金额',
            'card_id.required'   => '请选择需要到账的银行卡',
            'safe_word.required' => '请输入安全密码',
        ];
        real()->validator($data, $rule, $message);

        $request->amount < 100 && real()->exception('单笔提现最低金额为100元');
        $request->amount % 100 != 0 && real()->exception('提现金额需为100的倍数 请重新申请');

        $card = BankCard::find($request->card_id);
        $card || real()->exception('bank_card.info.error.retry');
        $card->user_id !== $this->user->id && real()->exception('bank_card.info.error.retry');

        $this->user->safeWordCheck($request->safe_word) || real()->exception('safe_word.check.error');

        $recharge = BalanceRecharge::where('user_id', $this->user->id)->whereIn('status', [1, 2]);
        $bet      = BetLog::where('user_id', $this->user->id);

        $amount = $recharge->sum('amount');
        $award  = $recharge->sum('award');

        $recharge_all = $amount + $award;
        $bet_all      = $bet->sum('total');
        if ($bet_all < $recharge_all) {
            $diff = $recharge_all - $bet_all;
            real()->exception('您的打码量不足一倍 您还需' . $diff . '打码量');
        }

        if ($recharge_all > 0) {
            $recharge_last = $recharge->orderBy('id', 'desc')->first();
            $bet_last      = $bet->where('created_at', '>=', $recharge_last->created_at)->sum('total');
            if ($bet_last < $recharge_last->amount) {
                $diff = $recharge_last->amount - $bet_last;
                real()->exception('您的打码量不足一倍 您还需' . $diff . '打码量');
            }
        }

        DB::beginTransaction();
        $data = [
            'user_id'   => $this->user->id,
            'amount'    => $request->amount,
            'bank_card' => $card->card,
            'bank_code' => $card->code,
            'status'    => 1,
        ];
        $item = BalanceWithdraw::create($data);
        $item || real()->exception('data.create.failed.retry');
        $this->user->wallet->balance('balance.withdraw', $item->id)->minus($request->amount);

        $message = [
            'message' => '提现订单 - ' . $this->user->nickname,
            'desc'    => '提现金额：' . $item->amount . '元 / ' . $item->bank_name . '<br>提现时间：' . $item->created_at,
            'audio'   => 'withdraw',
        ];
        PushEvent::name('notify')->toUser(10000000)->data($message);

        DB::commit();
        $result = $item->toArray();
        return real($result)->success('提现申请成功 预计3-10分钟内处理');
    }

    // public function get(Request $request)
    // {
    //     $rule = ['id' => 'required|int'];
    //     $data = $request->all();
    //     real()->validator($data, $rule);

    //     $item = BalanceWithdraw::find($request->id);
    //     $item || real()->exception('data.notexist');
    //     $result = $item->toArray();
    //     return real($result)->success();
    // }

    public function index(Request $request)
    {
        $items = BalanceWithdraw::where('user_id', $this->user->id)
            ->where('created_at', '>=', date('Y-m-d', strtotime('-7 day')))
            ->orderBy('id', 'desc')->get();
        $result = ['items' => $items->toArray()];
        return real($result)->success();
    }
}
