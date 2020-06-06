<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LottoModule\LottoChart;

class LottoChartController extends Controller
{
    public function index(Request $request)
    {
        // $lotto_name = $request->name;
        $model = new LottoChart();
        $items = $model->lotto($request->name)->chart($request->chart);

        $result = $items;
        return real($result)->success();

        // $code_array = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15'
        //     , '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27',
        // ];
        // return view('chart', compact('items', 'code_array'));
        // $result = ['items' => $model->$lotto_name()];
        // return real($result)->success();

    }
}
