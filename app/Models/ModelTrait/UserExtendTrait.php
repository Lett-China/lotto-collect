<?php
namespace App\Models\ModelTrait;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
trait UserExtendTrait
{
    use StorageTrait;

    protected $avatarPath = [
        'App\Models\User'  => '/avatar/',
        'App\Models\Admin' => '/admin/avatar/',
    ];

    public function avatarUpdate($file = null)
    {
        if ($file === null) {
            $identicon = new \Identicon\Identicon();
            $file      = $identicon->getImageData($this->id, 360, null, 'EFF3F6');
            $image     = Image::canvas(480, 480, '#eff3f6');
            $image->insert($file, 'top-left', 60, 60);
        } else {
            $image = Image::make($file);
            $image->fit(480, 480);
        }
        $stream = $image->stream()->__toString();

        $name   = md5($this->id) . '.png';
        $path   = $this->avatarPath[__CLASS__] . $name;
        $system = \Storage::disk('oss');
        $system->put($path, $stream);
        $key     = __CLASS__ . '\getAvatarAttribute' . ':' . $this->id;
        $updated = time();
        Cache::forever($key, $updated);
        $result = [
            'url'  => $this->getOssImageStyle($path, 'avatar') . '&time=' . $updated,
            'path' => $path,
            'name' => $name,
        ];

        return $result;
    }

    public function getAvatarAttribute()
    {
        // $name    = md5($this->id);
        // $key     = __CLASS__ . '\getAvatarAttribute' . ':' . $this->id;
        // $updated = Cache::rememberForever($key, function () {
        //     return time();
        // });
        // $path = $this->avatarPath[__CLASS__] . $name . '.png';
        // return $this->getOssImageStyle($path, 'avatar') . '&time=' . $updated;

        $name = substr($this->id, -1);
        $path = '/file/avatar/avatar-0' . $name . '.png';
        return $this->getOssImageStyle($path, 'avatar');
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function setSafeWordAttribute($value)
    {
        $this->attributes['safe_word'] = Hash::make($value);
    }

    public function updateRequestedAt()
    {
        $cache_name = __CLASS__ . '\updateRequestedAt' . ':' . $this->id;
        if (Cache::has($cache_name)) {
            return $this;
        }
        Cache::put($cache_name, time(), 300);
        $this->requested_at = date('Y-m-d H:i:s');
        $this->requested_ip = request()->ip();
        $this->save();
        return $this;
    }
}
