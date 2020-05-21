<?php

namespace App\Models\UserWallet;

use App\Models\User;
use App\Models\UserWallet\Wallet;
use Illuminate\Database\Eloquent\Model;

class BalanceWithdraw extends Model
{
    protected $appends = ['bank_name', 'bank_logo'];

    protected $casts = ['amount' => 'decimal:3'];

    protected $connection = 'main_sql';

    protected $fillable = ['user_id', 'amount', 'status', 'bank_card', 'bank_code', 'remark', 'confirmed_at'];

    protected $table = 'balance_withdraws';

    public function getBankLogoAttribute()
    {
        return '#bank-' . strtolower($this->bank_code);
    }

    public function getBankNameAttribute()
    {
        $config = config('bank.name');
        return $config[$this->bank_code];
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
