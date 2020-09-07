<?php
namespace App\Models\LottoModule\Models;

use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;

class ControlBet extends Model
{
    use Rememberable;

    protected $casts = ['bet_places' => 'array'];

    protected $connection = 'lotto_data';

    protected $fillable = ['user_id', 'lotto_index', 'bet_places', 'app_name'];

    protected $primaryKey = 'lotto_index';

    protected $table = 'control_bets';
}
