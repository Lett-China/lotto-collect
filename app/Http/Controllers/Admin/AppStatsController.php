<?php

namespace App\Http\Controllers\Admin;

use App\Models\AppStat;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AppStatsController extends Controller
{
    public function index(Request $request)
    {
        $items = AppStat::query();
        $request->date && $items->where('date', 'regexp', $request->date);
        $items->orderBy('id', 'desc');
        $result = $items->paginate(20)->toArray();
        return real()->listPage($result)->success();
    }

    public function update(Request $request)
    {
        $rule = ['date' => 'required'];

        $data = $request->all();
        real()->validator($data, $rule);

        AppStat::createData($request->date, true);

        $result = AppStat::where('date', $request->date)->first();

        return real($result->toArray())->success();
    }
}
