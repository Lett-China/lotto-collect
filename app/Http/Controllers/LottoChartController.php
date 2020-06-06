<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LottoModule\LottoChart;
use Illuminate\Http\Request;

class LottoChartController extends Controller
{
    public function index(Request $request)
    {
        $model = new LottoChart();
        $items = $model->lotto($request->name)->chart($request->chart);

        $result = $items;
        // return real($result)->success();
        $limit = request()->limit ?: 100;
        $type = intval(substr($request->chart, -2));
        switch ($type) {
            case 28:
                return view('chart28', compact('result', 'limit'));
                break;
            case 16:
                return view('chart16', compact('result', 'limit'));
                break;
            case 11:
                return view('chart11', compact('result', 'limit'));
                break;

        }

        // if (strpos($request->chart, '28') !== false) {
        //     return view('chart28', compact('result', 'limit'));
        // } else {
        //     return view('chart16', compact('result', 'limit'));
        // }

        // $result = ['items' => $model->$lotto_name()];
        // return real($result)->success();

    }
}
