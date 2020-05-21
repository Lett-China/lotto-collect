<?php

namespace App\Http\Controllers\KenoDe;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LottoModule\Models\LottoKenoDe;

class WebController extends Controller
{
    public function home(Request $request)
    {
        $model  = new LottoKenoDe;
        $hidden = ['win_extend', 'win_code', 'lotto_name', 'bet_count_down', 'short_id', 'status', 'mark', 'updated_at', 'opened_at'];
        request()->offsetSet('lotto_name', 'de28');
        $items = $model->where('status', '2')
            ->take(7)->orderBy('id', 'desc')
            ->get();
        $items->makeHidden($hidden);

        foreach ($items as $item) {
            $item->lotto_at = date('H:i:s d/m/Y', strtotime($item->lotto_at) - 25200);
        }

        $items    = $items->toArray();
        $newest   = $model->newestLotto();
        $hidden[] = 'open_code';
        $newest->makeHidden($hidden);

        $timestamp          = strtotime($newest->lotto_at);
        $newest->count_down = $timestamp - time();
        $newest->lotto_at   = date('H:i:s d/m/Y', strtotime($newest->lotto_at) - 25200);

        $newest = $newest->toArray();
        $result = ['items' => $items, 'newest' => $newest];
        return real($result)->success('return success');
    }

    function list(Request $request) {
        $model  = new LottoKenoDe;
        $hidden = ['win_extend', 'win_code', 'lotto_name', 'bet_count_down', 'short_id', 'status', 'mark', 'updated_at', 'opened_at'];
        request()->offsetSet('lotto_name', 'de28');

        $items = $model->where('status', '2')
            ->take(20)->orderBy('id', 'desc');

        $paginate       = $items->paginate(20);
        $paginate->data = $paginate->makeHidden($hidden);

        foreach ($paginate->data as $item) {
            $item->lotto_at = date('H:i:s d/m/Y', strtotime($item->lotto_at) - 25200);
        }

        $result = $paginate->toArray();
        return real()->listPage($result)->success();
    }
}
