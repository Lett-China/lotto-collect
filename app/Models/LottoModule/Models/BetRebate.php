<?php

namespace App\Models\LottoModule\Models;

use Illuminate\Database\Eloquent\Model;

class BetRebate extends Model
{
    public $timestamps = false;

    protected $connection = 'main_sql';

    protected $fillable = ['user_id', 'date', 'profit', 'bonus', 'bet', 'award', 'ratio'];
}
