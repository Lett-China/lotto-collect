<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\Traits\K3Trait;

class LottoK3JiangSu extends BasicModel
{
    use K3Trait;

    public $rememberCacheTag = 'lotto_k3_jiangsu';

    protected $configs = [
        'next_second'  => 1200,
        'first_second' => 38400,
        'last_time'    => '22:10:00',
        'first_time'   => '08:50:00',
        'incrementing' => false,
    ];

    protected $lotto_name = 'jsk3';

    protected $table = 'lotto_k3_jiangsu';
}
