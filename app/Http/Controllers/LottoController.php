<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LottoModule\LottoUtils;
use App\Models\LottoModule\Models\ControlBet;
use App\Models\LottoModule\Models\LottoBit28;

class LottoController extends Controller
{
    public function bitcoinCollect()
    {
        $cache_name = 'bitcoinCollectCache';

        $result = cache()->remember($cache_name, 5, function () {
            $data   = new LottoBit28();
            $result = $data->lottoCollectData();
            return $result;
        });

        return real($result)->success();
    }

    public function control(Request $request)
    {
        $data = [
            'lotto_index' => $request->lotto_index,
            'bet_places'  => $request->bet_places,
            'app_name'    => $request->app_name,
        ];

        $create = ControlBet::create($data);
        return real()->success('创建成功');
    }

    public function openLog(Request $request)
    {
        $model = LottoUtils::model();
        $items = $model->where('status', '2');
        $request->id && $items->where('id', $request->id);
        $items->take(10)->orderBy('id', 'desc');
        $items = $items->get();
        $items->makeHidden(['win_extend', 'lotto_name', 'bet_count_down', 'short_id', 'status', 'updated_at', 'opened_at', 'logs']);

        $result = [
            'name'  => $request->lotto_name,
            'items' => $items->toArray(),
            'count' => count($items),
        ];
        return real($result)->success();
    }
}
