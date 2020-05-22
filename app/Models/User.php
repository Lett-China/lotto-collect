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

    protected $connection = 'main_sql';

    protected $hidden = ['password', 'safe_word'];
}
