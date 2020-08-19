<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\Traits\ShiShiCaiTrait;

class LottoSscTianJin extends BasicModel
{
    use ShiShiCaiTrait;

    public $rememberCacheTag = 'lotto_ssc_tianjin';

    protected $configs = [
        'incrementing' => false,
        'next_second'  => 1200,
        'first_second' => 37200,
        'last_time'    => '23:00:00',
        'first_time'   => '09:20:00',
    ];

    protected $lotto_name = 'tjssc';

    protected $table = 'lotto_ssc_tianjin';
}
