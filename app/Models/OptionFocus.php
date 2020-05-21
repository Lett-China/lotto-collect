<?php
namespace App\Models;

use App\Models\ModelTrait\ModelTrait;
use Watson\Rememberable\Rememberable;
use App\Models\ModelTrait\StorageTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

class OptionFocus extends Model implements Auditable
{
    use SoftDeletes, ModelTrait, StorageTrait, Rememberable, AuditableTrait;

    protected $casts = ['params' => 'array', 'scope' => 'array'];

    protected $connection = 'main_sql';

    protected $fillable = ['mapping', 'params', 'image', 'scope', 'extend'];

    protected $hidden = ['deleted_at'];

    protected $rememberCacheTag = 'option_focus';

    protected $table = 'option_focus';

    public function getImageAttribute($value)
    {
        return $this->getOssImageStyle($value, 'max');
    }

    public function setImageAttribute($value)
    {
        $this->attributes['image'] = $this->getImagePath($value);
    }
}
