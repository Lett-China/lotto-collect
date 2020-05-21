<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\Traits\K3Trait;

class LottoK3HuBei extends BasicModel
{
    use K3Trait;

    public $rememberCacheTag = 'lotto_k3_hubei';

    protected $configs = [
        'next_second'  => 1200,
        'first_second' => 40800,
        'last_time'    => '22:00:00',
        'first_time'   => '09:20:00',
        'incrementing' => false,
    ];

    protected $table = 'lotto_k3_hubei';
}
