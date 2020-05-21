<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\Traits\ShiShiCaiTrait;

class LottoSscXinJiang extends BasicModel
{
    use ShiShiCaiTrait;

    public $rememberCacheTag = 'lotto_ssc_xinjiang';

    protected $configs = [
        'next_second'  => 1200,
        'first_second' => 30000,
        'last_time'    => '02:00:00',
        'first_time'   => '10:20:00',
        'incrementing' => false,
    ];

    protected $table = 'lotto_ssc_xinjiang';
}
