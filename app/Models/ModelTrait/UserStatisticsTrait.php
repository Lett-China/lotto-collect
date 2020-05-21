<?php
namespace App\Models\ModelTrait;

trait UserStatisticsTrait
{
    protected $user = null;

    public function __call($method, $parameters)
    {
        $user  = $parameters[0];
        $day   = $parameters[1];
        $table = [
            'withdraw'   => 'balance_withdraws',
            'recharge'   => 'balance_recharges',
            'commission' => 'commissions',
            'betLog'     => 'bet_logs',
        ];

        $date = $this->date($day);
        $data = DB::table($table[$method])->where('user_id', $user);

        in_array($method, ['withdraw', 'recharge']) && $data->where('status', 2);

        isset($date['start']) && $data->where('created_at', '>', $date['start']);
        isset($date['end']) && $data->where('created_at', '<', $date['end']);

        $method == 'betLog' &&
        $result = $data->first([DB::raw('SUM(total) as bet'), DB::raw('SUM(bonus) as bonus')]);

        in_array($method, ['withdraw', 'recharge']) &&
        $result = $data->first([DB::raw('SUM(amount) as total')]);

        $method == 'commission' &&
        $result = $data->first([DB::raw('SUM(total) as total')]);

        return $result;
    }

    private function date($day)
    {
        $result = [];

        $day === 'today' && $result['start']     = date('Y-m-d');
        $day === 'yesterday' && $result['start'] = date('Y-m-d', strtotime('-1 day'));
        $day === 'last7' && $result['start']     = date('Y-m-d', strtotime('-7 day'));
        $day === 'last30' && $result['start']    = date('Y-m-d', strtotime('-30 day'));
        $day !== 'today' && $result['end']       = date('Y-m-d');
        return $result;
    }
}
