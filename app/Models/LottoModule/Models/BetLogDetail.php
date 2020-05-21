<?php
namespace App\Models\LottoModule\Models;

use Illuminate\Database\Eloquent\Model;

class BetLogDetail extends Model
{
    public $timestamps = false;

    protected $casts = [
        'amount'      => 'decimal:3',
        'bonus'       => 'decimal:3',
        'odds'        => 'decimal:3',
        'odds_settle' => 'decimal:3',
    ];

    protected $connection = 'main_sql';

    protected $fillable = ['log_id', 'name', 'place', 'amount', 'bonus', 'odds', 'odds_settle', 'extend'];
}
