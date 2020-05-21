<?php
namespace App\Models\UserWallet;

use App\Models\Withdraw;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WalletLog extends Model
{
    protected $appends = ['title', 'icon_font'];

    protected $casts = [
        'extend' => 'array',
        'amount' => 'decimal:3',
        'before' => 'decimal:3',
        'after'  => 'decimal:3',
    ];

    protected $fillable = ['user_id', 'amount', 'source_id', 'source_name', 'remark', 'before', 'after', 'extend'];

    protected $icon = [
        'lotto'    => 'game',
        'recharge' => 'recharge',
        'withdraw' => 'withdraw',
        'system'   => 'record',
    ];

    protected $table = 'user_wallet_logs';

    public function getIconFontAttribute()
    {
        $icon      = array_keys($this->icon);
        $source    = explode('.', $this->source_name);
        $intersect = array_intersect_assoc($icon, $source);

        if (!$intersect) {return 'record';}
        return $this->icon[array_shift($intersect)];
    }

    public function getTitleAttribute()
    {
        return $this->trans($this->source_name);
    }

    public function withdraw()
    {
        return $this->HasOne(Withdraw::class, 'id', 'source_id');
    }

    private static function trans($message = '')
    {
        $key  = 'wallet.' . $message;
        $lang = trans($key);

        if ($lang !== $key) {
            return $lang;
        }

        $lang = trans('wallet');

        $params = explode('.', $message);
        $format = $lang['format'];
        $trans  = 0;

        for ($i = 1; $i <= 5; $i++) {
            $value = $params[$i - 1] ?? '';
            $temp  = $lang[$value] ?? $value;
            $mark  = ':' . $i;

            if (!isset($lang[$value]) && isset($params[$i - 1])) {
                $trans++;
            }

            $format = str_replace($mark, $temp, $format);
        }

        if ($trans === count($params)) {
            $format = $message;
        }

        return $format;
    }
}
