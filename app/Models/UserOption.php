<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOption extends Model
{
    protected $casts = ['bet_chat' => 'bool'];

    protected $connection = 'main_sql';

    protected $fillable = ['user_id', 'bet_chat'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $primaryKey = 'user_id';
}
