<?php
namespace App\Models\LottoModule\Models;

use App\Models\ModelTrait\ModelTrait;
use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;
use App\Models\LottoModule\Models\BetLog;
use App\Models\LottoModule\Models\BetStats;
use App\Models\LottoModule\Models\LottoConfig;
use App\Models\LottoModule\Traits\CommonTrait;

class BasicModel extends Model
{
    use CommonTrait, Rememberable, ModelTrait;

    public $incrementing = false;

    public $timestamps = false;

    protected $appends = ['win_code', 'lotto_name', 'win_extend', 'bet_count_down', 'short_id'];

    protected $casts = ['extend' => 'array', 'win_code' => 'string'];

    protected $connection = 'lotto_data';

    protected $fillable = ['id', 'open_code', 'lotto_at', 'opened_at', 'mark', 'status', 'extend', 'control', 'logs'];

    protected $hidden = ['extend', 'control', 'logs'];

    public function betLog()
    {
        return $this->hasMany(BetLog::class, 'lotto_index', 'lotto_index');
    }

    public function betStats()
    {
        return $this->hasOne(BetStats::class, 'lotto_index', 'lotto_index')->withDefault([
            'bet_total'  => '0.00',
            'bet_people' => '0',
            'win_total'  => '0.00',
            'win_people' => '0',
            'win_total'  => '0.00',
        ]);
    }

    public function getBetCountDownAttribute()
    {
        if ($this->status != 1) {
            return -1;
        }
        try {
            $config    = LottoConfig::remember(600)->find(request()->lotto_name, ['stop_ahead']);
            $ahead     = $config->stop_ahead;
            $timestamp = strtotime($this->lotto_at);
            return $timestamp - time() - $ahead;
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function getLottoIndexAttribute()
    {
        return request()->lotto_name . ':' . $this->id;
    }

    public function getLottoNameAttribute()
    {
        // dd($this->lotto_name);
        $lotto_name = request()->lotto_name;
        // if (isset($this->lotto_name)) {
        //     $lotto_name = $this->lotto_name;
        // }
        return $lotto_name;
    }

    public function getShortIdAttribute()
    {
        $length = mb_strlen($this->id);
        if ($length >= 11) {
            return (string) substr($this->id, -7);
        } else {
            return (string) $this->id;
        }
    }

    public function newestLotto()
    {
        $config   = LottoConfig::remember(600)->find(request()->lotto_name, ['stop_ahead']);
        $datetime = date('Y-m-d H:i:s', time() + $config->stop_ahead);
        return $this->where('status', 1)->where('lotto_at', '>', $datetime)->first();
    }

    public function setLottoName($name)
    {
        request()->offsetSet('lotto_name', $name);
        return $this;
    }
}
