<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpenBet extends Model
{
    protected $connection = 'main_sql';

    protected $fillable = ['lotto_name', 'lotto_id', 'bet_detail', 'bet_id'];

    protected $table = 'open_bets';
}
