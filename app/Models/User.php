<?php
namespace App\Models;

use Watson\Rememberable\Rememberable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject, Auditable
{
    use AuditableTrait, Rememberable;

    public $incrementing = false;

    protected $casts = ['disable' => 'bool', 'robot' => 'bool', 'trial' => 'bool'];

    protected $connection = 'main_sql';

    protected $fillable = ['id', 'nickname', 'real_name', 'password', 'safe_word', 'mobile', 'contact_qq', 'status', 'robot', 'trial', 'requested_at', 'requested_ip', 'created_ip'];

    protected $hidden = ['password', 'safe_word'];
}
