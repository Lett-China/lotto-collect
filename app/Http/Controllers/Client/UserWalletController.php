<?php
namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LottoModule\LottoUtils;
use App\Models\ModelTrait\ThisUserTrait;
use App\Models\LottoModule\Models\BetLog;

class UserWalletController extends Controller
{
    use ThisUserTrait;

    public function balanceLog(Request $request)
    {
        $data = $this->user->walletLog();

        $request->status == 1 && $data->where('source_name', 'balance.recharge');
        $request->status == 2 && $data->where('source_name', 'balance.withdraw');

        $data->where('created_at', '>=', date('Y-m-d', strtotime('-30 day')));
        $data->orderBy('id', 'desc');
        $paginate       = $data->paginate(20);
        $paginate->data = $paginate->makeHidden(['extend', 'source_name', 'source_id', 'remark']);
        $result         = $paginate->toArray();

        return real()->listPage($result)->success();
    }

    public function betLog(Request $request)
    {
        $status = $request->status;
        $data   = $this->user->betLog()->with('details');
        $data->where('created_at', '>=', date('Y-m-d', strtotime('-30 day')));
        $data->orderBy('id', 'desc');

        $status == 1 && $data->where('status', 1);
        $status == 2 && $data->where('bonus', '>', 0)->where('status', 2);
        $status == 3 && $data->where('bonus', '=', 0)->where('status', 2);

        $paginate       = $data->paginate(20);
        $paginate->data = $paginate->makeHidden(['lotto_index', 'confirmed_at', 'updated_at', 'trial']);
        $result         = $paginate->toArray();

        return real()->listPage($result)->success();
    }

    public function betLogGet(Request $request)
    {
        $rule = ['id' => 'required|int'];
        $data = $request->all();
        real()->validator($data, $rule);

        $item = BetLog::with('details')->find($request->id);

        $item || real()->exception('data.notexist');
        $lotto = LottoUtils::model($item->lotto_name)
            ->find($item->lotto_id)
            ->makeHidden(['mark', 'opened_at', 'updated_at']);
        $item->lotto    = $lotto;
        $item->win_code = $lotto->win_code;
        $item->lotto_at = $lotto->lotto_at;

        $result = $item->toArray();
        return real($result)->success();
    }
}
