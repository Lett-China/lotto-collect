<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\Traits\K3Trait;

class LottoK3HeBei extends BasicModel
{
    use K3Trait;

    public $rememberCacheTag = 'lotto_k3_hebei';

    protected $configs = [
        'next_second'  => 1200,
        'first_second' => 38400,
        'last_time'    => '22:10:00',
        'first_time'   => '08:50:00',
        'incrementing' => false,
    ];

    protected $table = 'lotto_k3_hebei';
}
