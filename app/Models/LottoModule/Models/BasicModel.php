<?php
namespace App\Models\LottoModule\Models;

use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;
use App\Models\LottoModule\Traits\CommonTrait;

class BasicModel extends Model
{
    use CommonTrait, Rememberable;

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = ['extend' => 'array', 'win_code' => 'string'];

    protected $connection = 'lotto_data';

    protected $fillable = ['id', 'open_code', 'lotto_at', 'opened_at', 'mark', 'status', 'extend', 'control', 'logs'];
}
