<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionLog extends Model
{
    public $timestamps = false;

    protected $connection = 'main_sql';

    protected $fillable = ['user_id', 'level', 'parent_id', 'amount', 'type', 'proportion', 'source'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
