<?php
return [
    //亏损反水
    'bet_rebate'     => [
        'ratio' => 0.01,
        'limit' => 1000,
    ],

    //首充奖励
    'recharge_first' => [
        'ratio' => 0.01,
    ],

    //新注册用户
    'register_first' => [
        'amount' => 8.00,
    ],

    //28 逢2和8额外嘉奖
    'bonus_28'       => [
        'start' => '2020-01-20', //开始时间
        'end'   => '2020-02-20', //结束时间
        'odds'  => '0.01', //额外嘉奖
    ],
];
