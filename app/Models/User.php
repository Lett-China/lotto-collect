<?php
namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\UserWallet\WalletTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\LottoModule\UserBetTrait;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\ModelTrait\UserExtendTrait;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject, Auditable
{
    use UserExtendTrait, AuditableTrait, WalletTrait, UserBetTrait;

    public $incrementing = false;

    protected $appends = ['avatar'];

    protected $casts = ['disable' => 'bool', 'robot' => 'bool', 'trial' => 'bool'];

    protected $connection = 'main_sql';

    protected $fillable = ['id', 'nickname', 'real_name', 'password', 'safe_word', 'mobile', 'status', 'robot', 'trial', 'requested_at', 'requested_ip', 'created_ip', 'ref_code'];

    protected $hidden = ['password'];

    public function CheckIsTrial()
    {
        if ($this->trial === null) {
            return null;
        }
        $this->trial === true && real()->exception('您为试玩账号 请注册后再操作');
    }

    public function bankCard()
    {
        return $this->hasMany(BankCard::class, 'user_id', 'id')->orderBy('id', 'desc');
    }

    public function checkIP()
    {
        $result               = [];
        $temp                 = explode('.', $this->requested_ip);
        $requested_ip         = $temp[0] . '.' . $temp[1] . '.';
        $result['req_regexp'] = $this->where('requested_ip', 'like', $requested_ip . '%')->count();
        $result['req_100']    = $this->where('requested_ip', $this->requested_ip)->count();

        $temp                 = explode('.', $this->created_ip);
        $created_ip           = $temp[0] . '.' . $temp[1] . '.';
        $result['reg_regexp'] = $this->where('created_ip', 'like', $created_ip . '%')->count();
        $result['reg_100']    = $this->where('created_ip', $this->created_ip)->count();
        return $result;
    }

    public function createOption()
    {
        $data = [
            'user_id'  => $this->id,
            'bet_chat' => true,
        ];
        return UserOption::create($data);
    }

    public function option()
    {
        return $this->hasOne(UserOption::class, 'user_id', 'id');
    }

    public function safeWordCheck($word)
    {
        return Hash::check($word, $this->safe_word);
    }

    public function setIdAttribute($value)
    {
        $a                      = strtotime('2020-01-01');
        $b                      = strtotime(date('Y-m-d'));
        $diff                   = (int) (($b - $a) / 86400) + 100;
        $temp                   = str_pad($diff, 8, '0', STR_PAD_RIGHT);
        $id                     = $temp + mt_rand(10000, 99999);
        $this->attributes['id'] = $id;
        if ($value === 'trial') {
            $this->attributes['mobile'] = '258' . $id;
        }
    }

    public function setMobileAttribute($value)
    {
        if ($value === 'trial') {
            $id                         = $this->id;
            $this->attributes['mobile'] = '258' . $id;
        } else {
            $this->attributes['mobile'] = $value;
        }
    }

    public function statistics($day)
    {
        $date = function ($day) {
            $result                                  = [];
            $day === 'today' && $result['start']     = date('Y-m-d');
            $day === 'yesterday' && $result['start'] = date('Y-m-d', strtotime('-1 day'));
            $day === 'last7' && $result['start']     = date('Y-m-d', strtotime('-7 day'));
            $day === 'last30' && $result['start']    = date('Y-m-d', strtotime('-30 day'));
            $day !== 'today' && $result['end']       = date('Y-m-d');
            return $this->statsDate                  = $result;
        };

        $date($day);

        $stats = function ($method) {
            $config = [
                'withdraw'   => [
                    'table' => 'balance_withdraws',
                    'field' => 'created_at',
                ],
                'recharge'   => [
                    'table' => 'balance_recharges',
                    'field' => 'created_at',
                ],
                'commission' => [
                    'table' => 'commissions',
                    'field' => 'created_at',
                ],
                'betLog'     => [
                    'table' => 'bet_logs',
                    'field' => 'confirmed_at',
                ],
            ];

            $table = $config[$method]['table'];
            $field = $config[$method]['field'];
            $date  = $this->statsDate;
            $data  = DB::table($table)->where('user_id', $this->id);

            in_array($method, ['withdraw', 'recharge']) && $data->where('status', 2);

            isset($date['start']) && $data->where($field, '>', $date['start']);
            isset($date['end']) && $data->where($field, '<', $date['end']);

            $method == 'betLog' &&
            $result = $data->first([DB::raw('SUM(total) as bet'), DB::raw('SUM(bonus) as bonus')]);

            in_array($method, ['withdraw', 'recharge']) &&
            $result = $data->first([DB::raw('SUM(amount) as total')]);

            $method == 'commission' &&
            $result = $data->first([DB::raw('SUM(total) as total')]);

            return $result;
        };

        $bet = $stats('betLog');

        return [
            'profit'     => sprintf('%01.2f', $bet->bonus - $bet->bet),
            'bet'        => sprintf('%01.2f', $bet->bet),
            'bonus'      => sprintf('%01.2f', $bet->bonus),
            'red_bag'    => '0.00',
            'commission' => sprintf('%01.2f', $stats('commission')->total),
            'recharge'   => sprintf('%01.2f', $stats('recharge')->total),
            'withdraw'   => sprintf('%01.2f', $stats('withdraw')->total),
        ];
    }
}
