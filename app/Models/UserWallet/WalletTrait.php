<?php

namespace App\Models\UserWallet;

trait WalletTrait
{
    public function createWallet()
    {
        $data = [
            'user_id' => $this->id,
            'balance' => 0.00,
            'robot'   => $this->robot,
        ];
        Wallet::create($data);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id', 'id')->lockForUpdate();
    }

    public function walletLog()
    {
        return $this->hasMany(WalletLog::class, 'user_id', 'id');
    }
}
