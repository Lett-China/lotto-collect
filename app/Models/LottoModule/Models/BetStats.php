<?php

namespace App\Models\LottoModule\Models;

use Illuminate\Database\Eloquent\Model;

class BetStats extends Model
{
    public $incrementing = false;

    protected $casts = [
        'bet_total'  => 'decimal:3',
        'bet_people' => 'string',
        'win_total'  => 'decimal:3',
        'win_people' => 'string',
        'win_total'  => 'decimal:3',
    ];

    protected $connection = 'main_sql';

    protected $fillable = ['id', 'lotto_index', 'bet_total', 'bet_people', 'win_total', 'win_people'];

    protected $primaryKey = 'lotto_index';

    protected $table = 'bet_stats';
}
