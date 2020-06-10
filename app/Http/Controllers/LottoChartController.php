<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LottoModule\LottoChart;
use App\Models\LottoModule\LottoUtils;

class LottoChartController extends Controller
{
    public function index(Request $request)
    {
        $name = [
            'ca28'  => '加拿大',
            'cw28'  => '加拿大西部',
            'bit28' => '比特币',
            'de28'  => '德国',
            'bj28'  => '北京',
            'pc28'  => '蛋蛋',
        ];

        $chart = [
            'keno-28' => '28',
            'keno-16' => '16',
            'keno-11' => '11',
        ];

        $title = $name[$request->name] . $chart[$request->chart];
        $limit = request()->limit ?: 100;

        $last       = LottoUtils::model($request->name)->where('status', '2')->orderBy('id', 'desc')->first();
        $cache_name = 'lottoTrendChart.' . $request->name . ':' . $last->id . '--' . $limit;

        $items = cache()->remember($cache_name, 86400, function () use ($request) {
            $model = new LottoChart();
            $items = $model->lotto($request->name)->chart($request->chart);
            return $items;
        });
        $limit  = request()->limit ?: 100;
        $id_ass = $items['items'][0]['id'] % 5;

        return view('keno', compact('items', 'limit', 'title', 'request', 'id_ass'));

    }
}