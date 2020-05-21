<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ModelTrait\ThisUserTrait;

class StatisticsController extends Controller
{
    use ThisUserTrait;

    public function get(Request $request)
    {
        $header = [
            'today'     => [
                'title' => '今日盈亏 · 元',
                'desc'  => '因缓存原因可能存在5-10分钟延时',
            ],

            'yesterday' => [
                'title' => '昨日盈亏 · 元',
                'desc'  => '昨日数据 不包含今日实时数据',
            ],
            'last7'     => [
                'title' => '最近7天盈亏 · 元',
                'desc'  => '最近7天数据 不包含今日实时数据',
            ],
            'last30'    => [
                'title' => '最近30天盈亏 · 元',
                'desc'  => '最近30天数据 不包含今日实时数据',
            ],
        ];

        $header = $header[$request->day];

        $stats  = $this->user->statistics($request->day);
        $result = [
            'header' => [
                'title'  => $header['title'],
                'amount' => $stats['profit'],
                'desc'   => $header['desc'],
            ],

            'items'  => [
                ['title' => '投注金额', 'amount' => $stats['bet']],
                ['title' => '中奖金额', 'amount' => $stats['bonus']],
                ['title' => '活动礼金', 'amount' => '0.00'],
                ['title' => '下级提成', 'amount' => $stats['commission']],
                ['title' => '充值金额', 'amount' => $stats['recharge']],
                ['title' => '提现金额', 'amount' => $stats['withdraw']],
            ],
        ];

        return real($result)->success();
    }
}
