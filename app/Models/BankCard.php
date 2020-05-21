<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Packages\Utils\BankCard as UtilsBankCard;

class BankCard extends Model
{
    use SoftDeletes;

    protected $appends = ['name', 'logo'];

    protected $connection = 'main_sql';

    protected $fillable = ['user_id', 'code', 'card'];

    protected $hidden = ['deleted_at'];

    public static function check($card)
    {
        return UtilsBankCard::info($card);
    }

    public function getLogoAttribute()
    {
        return '#bank-' . strtolower($this->code);
    }

    public function getNameAttribute()
    {
        return UtilsBankCard::name($this->code);
    }
}
