<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Packages\Utils\PushEvent;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\UserWallet\BalanceRecharge;

class RechargeController extends Controller
{
    public function cancel(Request $request)
    {
        $rule = [
            'id'     => 'required|int',
            'cancel' => 'required|max:120',
        ];
        $data    = $request->all();
        $message = ['cancel.required' => '请输入取消备注'];
        real()->validator($data, $rule, $message);

        DB::beginTransaction();
        $item = BalanceRecharge::lockForUpdate()->find($request->id);
        $item || real()->exception('data.notexist');
        $item->status === 1 && real()->exception('该条记录状态为未处理 请重试');

        $expired = date('Y-m-d H:i:s', strtotime('+1 hours'));

        time() >= strtotime($item->updated_at) + 3600 && real()->exception('已超过最后处理时间 请联系超级管理员处理');

        if ($item->status == 2) {
            $user = User::find($item->user_id);
            $user || real()->exception('user.notexist');
            $wallet = $user->wallet->balance('balance.recharge.cancel', $item->id);
            $wallet->remark($request->cancel);
            $wallet->minus($item->amount + $item->award);
        }

        $item->status       = 1;
        $item->remark       = '';
        $item->award        = null;
        $item->confirmed_at = null;
        $item->cancel       = $request->cancel;
        $save               = $item->save();
        $save || real()->exception('data.update.failed');
        DB::commit();
        return $this->get($request, '撤销成功，请重新审核');
    }

    public function get(Request $request, $message = '')
    {
        $rule = ['id' => 'required|int'];
        $data = $request->all();
        real()->validator($data, $rule);

        $item = BalanceRecharge::with('user:id,nickname,real_name')->with('wallet')->find($request->id);
        $item || real()->exception('data.notexist');
        $result = $item->toArray();
        return real($result)->success($message);
    }

    public function index(Request $request)
    {
        $items = BalanceRecharge::with('user:id,nickname')->with('wallet');
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
        $item = BalanceRecharge::lockForUpdate()->find($request->id);
        $item || real()->exception('data.notexist');
        $item->status !== 1 && real()->exception('该条记录以处理，请勿重复处理');

        $user = User::find($item->user_id);
        $user || real()->exception('user.notexist');
        $award = null;
        if ($request->status == 2) {
            $wallet = $user->wallet->balance('balance.recharge', $item->id);
            $wallet->remark($request->remark);
            $wallet->plus($item->amount);

            //首充奖励
            $ratio = config('act.recharge_first.ratio');
            if ($ratio !== null) {
                $date  = date('Y-m-d');
                $count = BalanceRecharge::where('user_id', $user->id)
                    ->where('status', '2')
                    ->where('confirmed_at', '>=', $date)
                    ->where('confirmed_at', '<=', $date . ' 23:59:59')
                    ->count();
                if ($count === 0) {
                    $wallet = $user->wallet->balance('balance.recharge.first.award', $item->id);
                    $award  = bcmul($item->amount, $ratio, 2);
                    $wallet->remark(date('Y年m月d日', strtotime($date)) . '首充奖励');
                    $wallet->plus($award);
                }
            }
        }

        //修改充值状态
        $item->status          = $request->status;
        $item->remark          = $request->remark;
        $item->confirmed_at    = date('Y-m-d H:i:s');
        $award && $item->award = $award;
        $save                  = $item->save();
        $save || real()->exception('data.update.failed');

        $notify = [
            '2' => '您有充值申请已经通过审核 请关注余额变化',
            '3' => '您有充值申请被拒绝 请核对申请信息',
        ];
        $message = [
            'message' => $notify[$request->status],
            'event'   => 'recharge',
            'wallet'  => $user->wallet,
        ];
        PushEvent::name('notify')->toUser($item->user_id)->data($message);
        DB::commit();

        $prompt = [
            '2' => '充值申请已审核通过',
            '3' => '充值申请已拒绝',
        ];
        return $this->get($request, $prompt[$request->status]);
    }

    private function baseValidator(Request $request, $extend = [])
    {
        $rule = [];
        $rule = array_merge($extend, $rule);
        $data = $request->all();
        return real()->validator($data, $rule);
    }
}
