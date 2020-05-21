<?php
namespace App\Models\LottoModule\Models;

use App\Models\User;
use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;

class BetLog extends Model
{
    use Rememberable;

    public $rememberCacheTag = 'bet_log';

    protected $appends = ['lotto_name', 'lotto_id', 'lotto_title', 'lotto_icon', 'profit', 'str_place'];

    protected $casts = [
        'bets'   => 'object',
        'extend' => 'object',
        'total'  => 'decimal:3',
        'amount' => 'decimal:3',
        'bonus'  => 'decimal:3',
    ];

    protected $connection = 'main_sql';

    protected $fillable = ['user_id', 'lotto_index', 'total', 'amount', 'bonus', 'extend', 'confirmed_at', 'open_code', 'trial'];

    protected $hidden = ['extend'];

    public function details()
    {
        return $this->hasMany(BetLogDetail::class, 'log_id', 'id');
    }

    public function getLottoIconAttribute()
    {
        $item = LottoConfig::remember(600)->find($this->lotto_name);
        if ($item === null) {
            return null;
        }
        return $item->icon_font;
    }

    public function getLottoIdAttribute()
    {
        $temp = explode(':', $this->lotto_index);
        return $temp[1];
    }

    public function getLottoNameAttribute()
    {
        $temp = explode(':', $this->lotto_index);
        return $temp[0];
    }

    public function getLottoTitleAttribute()
    {
        $item = LottoConfig::remember(600)->find($this->lotto_name);
        if ($item === null) {
            return null;
        }
        return $item->title;
    }

    public function getProfitAttribute()
    {
        if ($this->status == 2) {
            return sprintf('%01.3f', $this->bonus - $this->total);
        } else {
            return null;
        }
    }

    public function getStrPlaceAttribute()
    {
        if (!$this->details) {
            return null;
        }

        $result = [];

        foreach ($this->details as $value) {
            $result[] = $value->name;
        }

        return implode('、', $result);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
