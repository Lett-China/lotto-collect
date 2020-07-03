<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\Traits\ShiShiCaiTrait;

class LottoSscChongqing extends BasicModel
{
    use ShiShiCaiTrait;

    public $rememberCacheTag = 'lotto_ssc_chongqing';

    protected $configs = [
        'incrementing' => false,
        'next_second'  => 1200,
        'first_second' => 2400,
        'last_time'    => '23:50:00',
        'first_time'   => '00:30:00',
    ];

    protected $table = 'lotto_ssc_chongqing';
}
