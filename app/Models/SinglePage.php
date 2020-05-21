<?php
namespace App\Models;

use App\Models\ModelTrait\ModelTrait;
use Spatie\EloquentSortable\Sortable;
use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\SortableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class SinglePage extends Model implements Sortable
{
    use SortableTrait, Rememberable, SoftDeletes, ModelTrait;

    protected $appends = ['content_html'];

    protected $connection = 'main_sql';

    protected $fillable = ['title', 'content'];

    protected $hidden = ['deleted_at'];

    protected $rememberCacheTag = 'single_page';

    protected $sortable = [
        'order_column_name'  => 'sort',
        'sort_when_creating' => true,
    ];

    public function buildSortQuery()
    {
        return static::query()->where('type', $this->type);
    }

    public function getContentHtmlAttribute()
    {
        return format_html($this->content);
    }
}
