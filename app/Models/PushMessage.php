<?php

namespace App\Models;

use Intervention\Image\Facades\Image;
use App\Models\ModelTrait\StorageTrait;
use Illuminate\Database\Eloquent\Model;

class PushMessage extends Model
{
    use StorageTrait;

    public $current_user = null;

    protected $casts = ['read' => 'bool'];

    protected $connection = 'main_sql';

    protected $fillable = ['to', 'from', 'message', 'message_type', 'index', 'read', 'read_at'];

    public function getMessageAttribute($value)
    {
        if ($this->message_type == 'image') {
            return $this->getOssImageStyle($value, 'max');
        }

        return $value;
    }

    public function getTargetIdAttribute()
    {
        $self = $this->current_user ? $this->current_user->id : 10000000;
        $str  = str_replace($self, '', $this->index);
        return str_replace(':', '', $str);
    }

    public function setIndexAttribute()
    {
        $temp = [$this->to, $this->from];
        asort($temp);
        $value                     = implode(':', $temp);
        $this->attributes['index'] = $value;
    }

    public function target()
    {
        return $this->hasOne(User::class, 'id', 'target_id');
    }

    public static function uploadImage($file)
    {
        $image = Image::make($file);

        $stream = $image->stream()->__toString();

        $name   = md5(str_random(40)) . '.png';
        $path   = '/chat/' . $name;
        $system = \Storage::disk('oss');
        $system->put($path, $stream);

        $result = [
            'url'  => \Storage::url($path),
            'path' => $path,
            'name' => $name,
        ];

        return $result;
    }
}
