<?php
namespace App\Models\LottoModule\Models;

use App\Models\ModelTrait\ModelTrait;
use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;

class LottoConfig extends Model
{
    use ModelTrait, Rememberable;

    public $incrementing = false;

    public $rememberCacheTag = 'lotto_config';

    protected $appends = ['bet_places', 'place_group', 'win_function', 'lotto_rule_html', 'disable_status', 'disable_content'];

    protected $casts = ['bet_quota' => 'object', 'disable' => 'object'];

    protected $connection = 'main_sql';

    protected $fillable = ['bet_quota', 'stop_ahead', 'open_wait', 'disable', 'lotto_rule', 'config_file'];

    protected $primaryKey = 'name';

    public function getBetPlacesAttribute()
    {
        return config($this->config_file . '.bet_places');
    }

    public function getDisableContentAttribute()
    {
        if (isset($this->disable->content)) {
            return format_html($this->disable->content);
        }
        return null;
    }

    public function getDisableStatusAttribute()
    {
        if (isset($this->disable->status)) {
            if ($this->disable->status === true && date('Y-m-d H:i:s') > $this->disable->time[0] && date('Y-m-d H:i:s') <= $this->disable->time[1]) {
                return true;
            }
        }

        return false;
    }

    public function getLottoRuleHtmlAttribute()
    {
        return format_html($this->lotto_rule);
    }

    public function getPlaceGroupAttribute()
    {
        return config($this->config_file . '.place_group');
    }

    public function getPlaceSettingAttribute()
    {
        $result = (object) [];

        $places = json_decode(json_encode($this->bet_places));
        foreach ($places as $value) {
            $temp          = $value->place;
            $result->$temp = $value;
        }

        return $result;
    }

    public function getWinFunctionAttribute()
    {
        return config($this->config_file . '.win_function');
    }
}
