<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LottoModule\LottoChart;

class LottoChartController extends Controller
{
    public function index(Request $request)
    {
        $name = [
            'ca28'  => '加拿大',
            'cw28'  => '加拿大西部',
            'bit28' => '比特币',
        ];

        $chart = [
            'keno-28' => '28',
            'keno-16' => '16',
            'keno-11' => '11',
        ];

        $title = $name[$request->name] . $chart[$request->chart];

        $model = new LottoChart();
        $items = $model->lotto($request->name)->chart($request->chart);

        $result = $items;
        $limit  = request()->limit ?: 100;

        $type = intval(substr($request->chart, -2));

        return view('keno', compact('result', 'limit', 'title', 'request'));

    }
}
