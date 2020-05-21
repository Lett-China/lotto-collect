<?php
namespace App\Models\UserWallet;

use \DB;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $casts = ['balance' => 'decimal:3'];

    protected $fillable = ['balance', 'user_id', 'robot'];

    protected $hidden = ['created_at', 'updated_at', 'robot'];

    protected $lessLimit = 0;

    protected $logParams = [];

    protected $primaryKey = 'user_id';

    protected $table = 'user_wallets';

    protected $walletField = '';

    protected $walletSleep = false;

    public function balance($name, $id = null)
    {
        $this->walletField              = 'balance';
        $this->logParams['source_name'] = $name;
        $this->logParams['source_id']   = $id;
        $this->logParams['user_id']     = $this->user_id;
        return $this;
    }

    public function execute($amount = 0)
    {
        $amount || real()->exception('wallet.params.error');
        DB::beginTransaction();
        $field = $this->walletField;
        sleep($this->walletSleep);

        $this->logParams['amount'] = $amount;
        $this->logParams['before'] = $this->$field;
        $this->increment($field, $amount);
        $this->logParams['after'] = $this->$field;

        if ($this->$field < $this->lessLimit && $amount < 0 && !$this->robot) {
            real()->exception('wallet.balance.not.enough');
        }

        WalletLog::create($this->logParams);
        DB::commit();
        return true;
    }

    public function extend($params)
    {
        $this->logParams['extend'] = $params;
        return $this;
    }

    public function lessLimit($amount = 0)
    {
        $this->lessLimit = $amount;
        return $this;
    }

    public function minus($amount = 0)
    {
        $amount = -abs($amount);
        return $this->execute($amount);
    }

    public function plus($amount = 0)
    {
        $amount = abs($amount);
        return $this->execute($amount);
    }

    public function remark($remark)
    {
        $this->logParams['remark'] = $remark;
        return $this;
    }

    public function sleep($second = 0)
    {
        $this->walletSleep = $second;
        return $this;
    }
}
