<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\Traits\Lotto28Trait;

class LottoPc28 extends BasicModel
{
    use Lotto28Trait;

    public $rememberCacheTag = 'lotto_beijing8';

    protected $configs = [
        'next_second'  => 300,
        'first_second' => 33000,
        'last_time'    => '23:55:00',
        'first_time'   => '09:00:00',
        'incrementing' => true,
    ];

    protected $formula_name = 'pc28';

    protected $table = 'lotto_beijing8';
}
