<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LottoModule\LottoChart;

class LottoChartController extends Controller
{
    public function index(Request $request)
    {
        $lotto_name = $request->name;
        $model      = new LottoChart();
        $items      = $model->$lotto_name();
        return view('chart', compact('items'));

        $result = ['items' => $model->$lotto_name()];
        return real($result)->success();

    }
}
