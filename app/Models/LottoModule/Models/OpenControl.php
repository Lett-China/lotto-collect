<?php

namespace App\Models\LottoModule\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\LottoModule\LottoFormula;
use App\Models\LottoModule\LottoWinPlace;

class OpenControl extends Model
{
    public $timestamps = false;

    protected $casts = ['used_info' => 'array'];

    protected $connection = 'lotto_data';

    protected $fillable = ['type', 'open_code', 'win_code', 'win_he', 'win_code_sort', 'status', 'used_at', 'used_info'];

    protected $table = 'control_sources';

    public function createData($count)
    {
        for ($i = 1; $i <= $count; $i++) {
            $open_code = $this->openCode();
            $formula   = LottoFormula::bj28($open_code);
            asort($formula['code_arr']);
            $data = [
                'type'          => 'keno',
                'open_code'     => $open_code,
                'win_code'      => $formula['code_str'],
                'win_he'        => $formula['code_he'],
                'win_code_sort' => implode(',', $formula['code_arr']),
                'status'        => 1,
            ];

            $this->create($data);
        }

        return true;
    }

    public function formulaBet($lotto_index, $open_code, $lotto_name = 'basic28')
    {
        $bets = ControlBet::remember(60)->where('lotto_index', $lotto_index)->orderBy('id', 'desc')->get();
        if (count($bets->toArray()) === 0) {
            return 0;
        }

        $win_place = LottoWinPlace::lotto28($open_code, $lotto_name);

        $total_bonus = 0;
        $total_bet   = 0;
        foreach ($bets as $bet) {
            foreach ($bet->bet_places as $value) {
                $total_bet += $value['amount'];
                if (!in_array($value['place'], $win_place)) {
                    continue;
                }
                $odds = $value['odds'];
                $total_bonus += bcmul($value['amount'], $odds, 2);
            }
        }

        // dump($total_bonus, $total_bet);
        return $total_bonus - $total_bet;
    }

    public function openCode()
    {
        $rand = [];
        for ($i = 1; $i <= 80; $i++) {
            $rand[] = sprintf('%02d', $i);
        }
        $r_key = array_rand($rand, 20);

        $open_code = [];
        foreach ($r_key as $value) {
            $open_code[] = $rand[$value];
        }
        $open_code = implode(',', $open_code);

        return $open_code;
    }
}
