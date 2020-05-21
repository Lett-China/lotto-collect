<?php
namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use App\Packages\Utils\PushEvent;
use Illuminate\Support\Facades\DB;
use App\Models\LottoModule\LottoUtils;
use App\Models\LottoModule\Models\LottoConfig;

class LottoMockBetCommand extends Command
{
    protected $description = 'lotto bet mock';

    protected $signature = 'lotto:mockBet';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('lotto mock start');

        $lottos = LottoConfig::all();
        foreach ($lottos as $lotto) {
            $online = PushEvent::users('lotto.' . $lotto->name);
            if (count($online['users']) == 0) {
                $this->comment($lotto->name . ': users 0');
                continue;
            }

            $mock = config($lotto->config_file . '.mock');

            if ($mock === null) {
                $this->comment($lotto->name . ': mock null');
                continue;
            }

            $amount  = $this->randAmount();
            $rand    = array_rand($mock['place'], mt_rand(1, 2));
            $total   = [];
            $checked = [];
            $total   = 0;

            $place = is_array($rand) ? $rand : [$rand];
            foreach ($place as $value) {
                $key           = $mock['place'][$value];
                $checked[$key] = [
                    'place'  => $key,
                    'amount' => $amount,
                ];
                $total += $amount;
            }

            DB::beginTransaction();

            $user = User::inRandomOrder()->where('robot', 1)->first();
            $last = LottoUtils::model($lotto->name)->newestLotto();
            if ($last === null) {
                $this->comment($lotto->name . ': last null');
                continue;
            }
            $bet = $user->bet($lotto->name, $last->id)->place($checked, $total, $amount);
            $this->comment($lotto->name . ': users ' . count($online['users']) . ' success');
            DB::rollback();
        }

        return $this->info('lotto mock success');
    }

    public function randAmount()
    {
        $weight = ['a' => 45, 'b' => 20, 'c' => 15, 'd' => 10, 'e' => 5];
        $amount = [
            'a' => [1, 100],
            'b' => [100, 500],
            'c' => [500, 1000],
            'd' => [1000, 3000],
            'e' => [3000, 6000],
        ];
        $rand = function ($weight) {
            $rand = mt_rand(1, (int) array_sum($weight));

            foreach ($weight as $key => $value) {
                $rand -= $value;
                if ($rand <= 0) {
                    return $key;
                }
            }
        };

        $am_key = $rand($weight);
        $am_val = $amount[$am_key];

        return mt_rand($am_val[0], $am_val[1]);
    }
}
