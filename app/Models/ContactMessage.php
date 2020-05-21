<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $connection = 'main_sql';

    protected $fillable = ['user_id', 'name', 'mobile', 'content'];

    protected $hidden = ['deleted_at'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
