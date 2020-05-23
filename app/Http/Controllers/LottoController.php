<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LottoModule\LottoUtils;
use App\Models\LottoModule\Models\LottoBit28;

class LottoController extends Controller
{
    public function bitcoinCollect()
    {
        $data   = new LottoBit28();
        $result = $data->lottoCollectData();
        return real($result)->success();
    }

    public function openLog(Request $request)
    {
        $model = LottoUtils::model();
        $items = $model->where('status', '2');
        $request->id && $items->where('id', $request->id);
        $items->take(10)->orderBy('id', 'desc');
        $items = $items->get();
        $items->makeHidden(['win_extend', 'win_code', 'lotto_name', 'bet_count_down', 'short_id', 'status', 'updated_at', 'opened_at']);

        $result = [
            'name'  => $request->lotto_name,
            'items' => $items->toArray(),
            'count' => count($items),
        ];
        return real($result)->success();
    }
}
