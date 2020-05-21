<?php

namespace App\Models\UserWallet;

use App\Models\User;
use App\Models\UserWallet\Wallet;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class BalanceRecharge extends Model implements Auditable
{
    use AuditableTrait;

    protected $appends = ['channel_info'];

    protected $casts = ['amount' => 'decimal:3'];

    protected $connection = 'main_sql';

    protected $fillable = ['user_id', 'amount', 'award', 'status', 'channel', 'name', 'remark', 'cancel', 'confirmed_at'];

    protected $table = 'balance_recharges';

    public function getChannelInfoAttribute()
    {
        $channels = config('recharge.channel');
        return $channels[$this->channel];
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id', 'user_id');
    }
}
