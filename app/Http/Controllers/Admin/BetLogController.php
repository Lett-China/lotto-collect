<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LottoModule\LottoUtils;
use App\Models\LottoModule\Models\BetLog;

class BetLogController extends Controller
{
    public function cancel(Request $request)
    {
        $item = BetLog::with('user')->with('details')->find($request->id);
        $item->status === 3 && real()->exception('该订单已取消下注');
        $item->status === 2 && real()->exception('该订单已派奖，不能取消');
        $user         = $item->user;
        $diff         = $item->total - $item->bonus;
        $item->status = 3;
        $item->save();
        if ($diff != 0) {
            $wallet = $user->wallet->balance('lotto.cancel.bet.' . $item->lotto_name, $item->id);
            $wallet->remark('系统取消下注');
            $diff > 0 && $wallet->plus($diff);
            $diff < 0 && $wallet->minus($diff);
        }

        $result = $item->toArray();
        return real($result)->success('取消投注成功');
    }

    public function get(Request $request)
    {
        $rule = ['id' => 'required|int'];
        $data = $request->all();
        real()->validator($data, $rule);

        $item = BetLog::with('user')->with('details')->find($request->id);

        $item || real()->exception('data.notexist');
        $lotto          = LottoUtils::model($item->lotto_name)->find($item->lotto_id);
        $item->lotto    = $lotto;
        $item->win_code = $lotto->win_code;
        $item->lotto_at = $lotto->lotto_at;

        $item->bet_details = $item->details;

        // $option     = LottoConfig::find($item->lotto_name);
        // $item->odds = $option->bet_places;

        $result = $item->toArray();
        return real($result)->success();
    }

    public function index(Request $request)
    {
        $items = BetLog::query();
        $request->user === 'true' && $items->with('user');
        $request->user_id && $items->where('user_id', $request->user_id);
        $request->id && $items->where('id', 'regexp', $request->id);
        if ($request->lotto_name && $request->lotto_name != 'all') {
            $lotto_index = $request->lotto_name;
            $request->lotto_id && $lotto_index .= ':' . $request->lotto_id;
            $items->where('lotto_index', 'regexp', $lotto_index);
        }
        $request->status && $request->status != '2-1' && $items->where('status', $request->status);

        if ($request->status == '2-1') {
            $items->where('status', 2)->where('bonus', '>', 0);
        }

        $items->orderBy('id', 'desc');
        $result = $items->paginate(20)->toArray();
        return real()->listPage($result)->success();
    }
}
