<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\Traits\ShiShiCaiTrait;

class LottoSscHeiLongJiang extends BasicModel
{
    use ShiShiCaiTrait;

    public $rememberCacheTag = 'lotto_ssc_heilongjiang';

    protected $configs = [
        'next_second'  => 1200,
        'first_second' => 37200,
        'last_time'    => '22:40:00',
        'first_time'   => '09:00:00',
        'incrementing' => true,
    ];

    protected $lotto_name = 'hljssc';

    protected $table = 'lotto_ssc_heilongjiang';
}
