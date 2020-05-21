<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Packages\Utils\PushEvent;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\UserWallet\BalanceWithdraw;

class WithdrawController extends Controller
{
    public function get(Request $request, $message = '')
    {
        $rule = ['id' => 'required|int'];
        $data = $request->all();
        real()->validator($data, $rule);

        $item = BalanceWithdraw::with('user:id,nickname,real_name')->with('wallet')->find($request->id);
        $item || real()->exception('data.notexist');
        $result = $item->toArray();
        return real($result)->success($message);
    }

    public function index(Request $request)
    {
        $items = BalanceWithdraw::with('user:id,nickname');
        $request->status && $items->where('status', $request->status);
        $request->user_id && $items->where('user_id', 'regexp', $request->user_id);
        $request->amount && $items->where('amount', $request->amount);
        $request->id && $items->where('id', $request->id);
        $items->orderBy('id', 'desc');
        $result = $items->paginate(20)->toArray();
        return real()->listPage($result)->success();
    }

    public function update(Request $request)
    {
        $rule = [
            'id'     => 'required|int',
            'status' => 'required|int|between:2,3',
            'remark' => 'required|max:120',
        ];

        $message = ['status.between' => '审核状态错误 请重新选择'];
        $data    = $request->all();
        real()->validator($data, $rule, $message);
        DB::beginTransaction();
        $item = BalanceWithdraw::lockForUpdate()->find($request->id);
        $item || real()->exception('data.notexist');
        $item->status !== 1 && real()->exception('该条记录以处理，请勿重复处理');

        $item->status       = $request->status;
        $item->remark       = $request->remark;
        $item->confirmed_at = date('Y-m-d H:i:s');
        $save               = $item->save();
        $save || real()->exception('data.update.failed');

        $user = User::find($item->user_id);
        $user || real()->exception('user.notexist');

        if ($request->status == 3) {
            $user->wallet->balance('balance.withdraw.failed', $item->id)->plus($request->amount);
        }

        $notify = [
            '2' => '您有提现申请已经通过审核 请关注对应提现渠道',
            '3' => '您有提现申请被拒绝 请核对申请信息',
        ];

        $message = [
            'message' => $notify[$request->status],
            'event'   => 'recharge',
            'wallet'  => $user->wallet,
        ];
        PushEvent::name('notify')->toUser($item->user_id)->data($message);

        DB::commit();

        $message = [
            '2' => '提现申请已审核通过',
            '3' => '提现申请已拒绝',
        ];
        return $this->get($request, $message[$request->status]);
    }
}
