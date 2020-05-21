<?php
namespace App\Models;

use App\Models\ModelTrait\ModelTrait;
use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Option extends Model implements Auditable
{
    use ModelTrait, Rememberable, AuditableTrait;

    protected $connection = 'main_sql';

    protected $fillable = ['name', 'value'];

    protected $hidden = ['deleted_at'];

    protected $rememberCacheTag = 'option';

    public function getValueAttribute($value)
    {
        $json = json_decode($value);
        return $json ?: $value;
    }

    public function setValueAttribute($value)
    {
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        $this->attributes['value'] = $value;
    }
}
