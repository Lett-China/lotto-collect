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

    protected $appends = ['win_extend', 'short_id'];

    protected $casts = ['extend' => 'array', 'id' => 'string'];

    protected $connection = 'lotto_data';

    protected $fillable = ['id', 'open_code', 'lotto_at', 'opened_at', 'mark', 'status', 'extend', 'control', 'logs'];

    protected $hidden = ['extend', 'control'];

    public function getShortIdAttribute()
    {
        $length = mb_strlen($this->id);
        if ($length >= 11) {
            $need = $length - 4;
            return (string) substr($this->id, -$need);
        } else {
            return (string) $this->id;
        }
    }
}
