<?php
namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\UserReference;
use App\Models\UserWallet\Wallet;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ModelTrait\ThisAdminTrait;
use App\Models\UserWallet\BalanceRecharge;
use App\Models\ModelTrait\UserStatisticsTrait;

class MemberController extends Controller
{
    use ThisAdminTrait, UserStatisticsTrait;

    public function balanceLog(Request $request)
    {
        $user = User::find($request->user_id);
        $data = $user->walletLog();

        $request->source == 'recharge' && $data->where('source_name', 'regexp', 'recharge');
        $request->source == 'withdraw' && $data->where('source_name', 'regexp', 'withdraw');

        $data->orderBy('id', 'desc');
        $paginate = $data->paginate(20);

        $result = $paginate->toArray();
        return real()->listPage($result)->success();
    }

    public function checkIP(Request $request)
    {
        $user  = User::find($request->user_id);
        $check = $user->checkIP();
        extract($check);
        $message = "请求IP 模糊匹配:$req_regexp / 完全匹配:$req_100\n注册IP 模糊匹配:$reg_regexp / 完全匹配:$reg_100";

        return real()->success($message);
    }

    public function get(Request $request)
    {
        $data = User::with('wallet')->find($request->id);
        request()->offsetSet('day', 'today');
        $data->stats    = $this->stats($request)['data']['items'];
        $data->ref_info = UserReference::getReference($request->id);

        $result = $data->toArray();
        return real($result)->success();
    }

    public function index(Request $request)
    {
        $data = User::with('wallet')->where('robot', '0');
        $request->id && $data->where('id', 'regexp', $request->id);
        $request->mobile && $data->where('mobile', 'regexp', $request->mobile);
        $request->nickname && $data->where('nickname', 'regexp', $request->nickname);
        $request->real_name && $data->where('real_name', 'regexp', $request->real_name);

        $requested_ip = $request->requested_ip;
        if ($request->requested_ip && strstr($request->requested_ip, '.')) {
            $temp         = explode('.', $request->requested_ip);
            $requested_ip = $temp[0] . '.' . $temp[1] . '.';
        }

        $request->requested_ip && $data->where('requested_ip', 'like', $requested_ip . '%');

        $created_ip = $request->created_ip;
        if ($request->created_ip && strstr($request->created_ip, '.')) {
            $temp       = explode('.', $request->created_ip);
            $created_ip = $temp[0] . '.' . $temp[1] . '.';
        }

        $request->created_ip && $data->where('created_ip', 'like', $created_ip . '%');

        $request->disable && $data->where('disable', 1);
        $data->orderBy('sort', 'desc');

        $pagination       = $data->paginate(20);
        $data             = $pagination->makeVisible(['avatar']);
        $pagination->data = $data;
        $result           = $pagination->toArray();

        foreach ($result['data'] as $key => $value) {
            if ($value['wallet'] === null) {
                $data = [
                    'user_id' => $value['id'],
                    'balance' => 0.00,
                ];

                Wallet::create($data);
            }
        }

        return real()->listPage($result)->success();
    }

    public function nextLevel(Request $request)
    {
        $user_id = $request->user_id;
        $items   = UserReference::with('user:id,nickname')->where('ref_id', $user_id)->get(['user_id', 'ref_id', 'created_at']);
        $current = User::find($user_id, ['id', 'nickname']);

        $specific = UserReference::where('user_id', $user_id)->first();

        $excerpt = 'TA目前共有' . count($items) . '名下级用户';

        $result = [
            'current'  => $current->toArray(),
            'items'    => $items->toArray(),
            // 'items'    => [],
            'excerpt'  => $excerpt,
            'specific' => $specific,
        ];

        return real($result)->success();
    }

    public function stats(Request $request)
    {
        $user   = User::find($request->id);
        $stats  = $user->statistics($request->day);
        $result = [
            'items' => [
                ['title' => '投注金额', 'amount' => $stats['bet']],
                ['title' => '中奖金额', 'amount' => $stats['bonus']],
                ['title' => '活动礼金', 'amount' => '0.00'],
                ['title' => '下级提成', 'amount' => $stats['commission']],
                ['title' => '充值金额', 'amount' => $stats['recharge']],
                ['title' => '提现金额', 'amount' => $stats['withdraw']],
            ],
        ];

        $result = ['items' => $stats];

        return real($result)->success();
    }

    public function update(Request $request)
    {
        $rule = [
            'id'       => 'required|int',
            'mobile'   => 'int',
            'nickname' => 'min:1|max:32',
            'disable'  => 'bool',
            'status'   => 'int',
        ];
        $data = $request->all();
        real()->validator($data, $rule);

        $data = User::find($request->id);

        $request->nickname && $data->nickname      = $request->nickname;
        $request->mobile && $data->mobile          = $request->mobile;
        isset($request->disable) && $data->disable = $request->disable;
        $request->password && $data->password      = $request->password;
        isset($request->status) && $data->status   = $request->status;

        $data->save();

        $result = $data->toArray();
        return real($result)->success('user.profile.update.success');
    }

    public function walletUpdate(Request $request)
    {
        $rule = [
            'id'          => 'required',
            'amount'      => 'required',
            'source_name' => 'required',
            'remark'      => 'required|min:1|max:256',
        ];
        $data = $request->all();
        real()->validator($data, $rule);

        DB::beginTransaction();
        $user = User::find($request->id);
        $user || real()->exception('user.notexist');
        $wallet = $user->wallet->balance($request->source_name);
        $wallet->remark($request->remark);
        $request->amount > 0 && $wallet->plus($request->amount);
        $request->amount < 0 && $wallet->minus($request->amount);

        if ($request->source_name === 'balance.recharge.service') {
            $request->amount < 0 && real()->exception('充值金额只能是正数');

            $data = [
                'user_id'      => $user->id,
                'amount'       => $request->amount,
                'status'       => 2,
                'channel'      => 'service',
                'remark'       => $request->remark,
                'confirmed_at' => date('Y-m-d H:i:s'),
            ];

            $item = BalanceRecharge::create($data);
            $item || real()->exception('recharge.order.create.failed');

            //首充奖励
            $ratio = config('act.recharge_first.ratio');
            if ($ratio !== null) {
                $date  = date('Y-m-d');
                $count = BalanceRecharge::where('user_id', $user->id)
                    ->where('status', '2')
                    ->where('confirmed_at', '>=', $date)
                    ->where('confirmed_at', '<=', $date . ' 23:59:59')
                    ->where('id', '!=', $item->id)
                    ->count();

                if ($count === 0) {
                    $wallet = $user->wallet->balance('balance.recharge.first.award', $item->id);
                    $award  = bcmul($item->amount, $ratio, 2);
                    $wallet->remark(date('Y年m月d日', strtotime($date)) . '首充奖励');
                    $temp = $wallet->plus($award);
                    $temp || real()->exception('首充赠送失败');
                    $item->award = $award;
                    $temp        = $item->save();
                    $temp || real()->exception('首充赠送失败2');
                }
            }
        }
        DB::commit();
        $result = $wallet->toArray();
        return real($result)->success('user.balance.update.success');
    }
}
