<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\Traits\K3Trait;

class LottoK3AnHui extends BasicModel
{
    use K3Trait;

    public $rememberCacheTag = 'lotto_k3_anhui';

    protected $configs = [
        'next_second'  => 1200,
        'first_second' => 39600,
        'last_time'    => '22:00:00',
        'first_time'   => '09:00:00',
        'incrementing' => false,
    ];

    protected $lotto_name = 'ahk3';

    protected $table = 'lotto_k3_anhui';
}
