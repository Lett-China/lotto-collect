<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Packages\Utils\PushEvent;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ModelTrait\ThisUserTrait;
use App\Models\UserWallet\BalanceRecharge;

class RechargeController extends Controller
{
    use ThisUserTrait;

    public function channel()
    {
        $this->user->CheckIsTrial();
        $channel = config('recharge.channel');
        $channel = [$channel['service']];
        $result  = ['channel' => $channel];
        return real($result)->success();
    }

    public function create(Request $request)
    {
        $this->user->CheckIsTrial();
        DB::beginTransaction();
        $rule = [
            'amount'  => 'required|currency|currency|min:1|max:50000',
            'channel' => 'required',
            // 'name'    => 'min:1|max:2',
        ];

        $message = [
            'amount.required'  => '请输入充值金额',
            'channel.required' => '请选择充值渠道',
            'name.required'    => '请输入您的转账户名',
        ];

        if ($request->amount > 500000 || $request->amount < 10) {
            return real()->error('单次充值最多50万，最低10元');
        }

        $data = $request->all();
        real()->validator($data, $rule, $message);
        $data = [
            'user_id' => $this->user->id,
            'amount'  => (int) ($request->amount * 100) / 100,
            'channel' => $request->channel,
            'name'    => $request->name,
            'status'  => 1,
        ];
        $item = BalanceRecharge::create($data);
        $item || real()->exception('data.create.failed.retry');
        $result = $item->toArray();

        DB::commit();

        $message = [
            'message' => '充值提醒 - ' . $this->user->nickname,
            'desc'    => '充值金额：' . $item->amount . '元 / ' . $item->channel_info['bank_name'] . '<br>充值时间：' . $item->created_at,
            'audio'   => 'recharge',
        ];
        PushEvent::name('notify')->toUser(10000000)->data($message);

        return real($result)->success('充值申请提交成功 预计1-5钟内处理');
    }

    public function index()
    {
        $items = BalanceRecharge::where('user_id', $this->user->id)
            ->where('created_at', '>=', date('Y-m-d', strtotime('-7 day')))
            ->orderBy('id', 'desc')->get();
        $result = ['items' => $items->toArray()];
        return real($result)->success();
    }
}
