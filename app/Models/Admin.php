<?php
namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\ModelTrait\UserExtendTrait;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable implements JWTSubject, Auditable
{
    use UserExtendTrait, AuditableTrait, Notifiable;

    protected $appends = ['avatar'];

    protected $auditExclude = ['requested_at', 'requested_ip'];

    protected $casts = ['lock' => 'bool', 'disable' => 'bool'];

    protected $connection = 'main_sql';

    protected $fillable = ['username', 'password', 'nickname', 'requested_at', 'requested_ip'];

    protected $hidden = ['password'];

    public function receivesBroadcastNotificationsOn()
    {
        return 'admin.' . $this->id;
    }
}
