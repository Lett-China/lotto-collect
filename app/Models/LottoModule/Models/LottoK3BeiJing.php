<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\Traits\K3Trait;

class LottoK3BeiJing extends BasicModel
{
    use K3Trait;

    public $rememberCacheTag = 'lotto_k3_beijing';

    protected $configs = [
        'next_second'  => 1200,
        'first_second' => 34800,
        'last_time'    => '23:40:00',
        'first_time'   => '09:20:00',
        'incrementing' => true,
    ];

    protected $lotto_name = 'bjk3';

    protected $table = 'lotto_k3_beijing';
}
