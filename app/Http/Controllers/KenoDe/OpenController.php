<?php

namespace App\Http\Controllers\KenoDe;

use App\Models\OpenBet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LottoModule\Models\LottoKenoDe;

class OpenController extends Controller
{
    public function betCreate(Request $request)
    {
        $rule = [
            'lotto_name' => 'required|int',
            'lotto_id'   => 'required|int',
            'bet_detail' => 'required',
            'bet_id'     => 'required|int',
        ];
        $data = $request->all();
        real()->validator($data, $rule);
        $data = [
            'lotto_name' => $request->lotto_name,
            'lotto_id'   => $request->lotto_id,
            'bet_id'     => $request->bet_id,
            'bet_detail' => $request->bet_detail,
        ];
        $temp = OpenBet::create($data);
        $temp || real()->exception('create.error');
        return real()->success('create.success');
    }

    /**
     * 控制开奖
     */
    public function control(Request $request)
    {
        $rule = [
            'lotto_name' => 'required|int',
            'lotto_id'   => 'required|int',
            'bet_id'     => 'required|int',
            'control'    => 'required|int',
        ];
        $data = $request->all();
        real()->validator($data, $rule);
        $update = OpenBet::where('bet_id', $request->bet_id)->update(['control' => $request->control]);
        $update || real()->exception('control.error');
        return real()->success('control.success');
    }

    public function last(Request $request)
    {
        request()->offsetSet('lotto_name', 'de28');
        $items = LottoKenoDe::where('status', '2')
            ->take(10)->orderBy('id', 'desc')
            ->get();
        $items->makeHidden(['win_extend', 'win_code', 'lotto_name', 'bet_count_down', 'short_id', 'status', 'mark', 'updated_at', 'opened_at']);
        // dd($items);
        $items  = $items->toArray();
        $result = ['items' => $items, 'count' => count($items)];
        return real($result)->success();
    }
}
