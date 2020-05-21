<?php
namespace App\Models\LottoModule\Models;

use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;

class LottoWarning extends Model
{
    use Rememberable;

    public $rememberCacheTag = 'lotto_errors';

    protected $casts = ['model_extend' => 'array'];

    protected $connection = 'lotto_data';

    protected $content_mapping = [
        'lotto_at_system'  => '已由系统自动修正，请核对后续期号开奖时间',
        'lotto_at_warning' => '开奖时间可能存在错误，如有错误请修正',
        'lotto_at_error'   => '开奖时间错误，可能存在刷分，请联系程序员处理',
    ];

    protected $fillable = ['id', 'model_name', 'model_id', 'level', 'field', 'value', 'source', 'content', 'handled_at'];

    protected $table = 'lotto_warning';

    public function getContentAttribute($value)
    {
        try {
            $key   = $this->field . '_' . $this->level;
            $value = $this->content_mapping[$key];
        } catch (\Throwable $th) {
            return $value;
        }
        return $value;
    }

    public function getShortNameAttribute()
    {
        $temp = explode('\\', $this->model_name);
        return end($temp);
    }

    public static function lottoAt($level, $model_name, $model_id, $value, $source)
    {
        $data = [
            'model_name' => $model_name,
            'model_id'   => $model_id,
            'value'      => $value,
            'source'     => $source,
            'field'      => 'lotto_at',
            'level'      => $level,
        ];

        return self::create($data);
    }

    public static function openCode($level, $model_name, $model_id, $value, $source)
    {
        $data = [
            'model_name' => $model_name,
            'model_id'   => $model_id,
            'value'      => $value,
            'source'     => $source,
            'field'      => 'open_code',
            'level'      => $level,
        ];

        return self::create($data);
    }
}
