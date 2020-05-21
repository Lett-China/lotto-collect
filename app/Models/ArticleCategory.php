<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

class ArticleCategory extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;

    protected $connection = 'main_sql';

    protected $fillable = ['name', 'parent', 'desc'];

    protected $hidden = ['deleted_at'];

    public function article()
    {
        return $this->hasMany(Article::class, 'cat_id', 'id');
    }

    public function childCategory()
    {
        return $this->hasMany($this, 'parent', 'id');
    }

    public function children()
    {
        return $this->childCategory()->with('children');
    }
}
