<?php
namespace App\Models\LottoModule\Models;

use App\Models\LottoModule\LottoUtils;
use App\Models\LottoModule\LottoFormula;
use App\Models\LottoModule\Traits\Lotto28Trait;

class LottoKenoDe extends BasicModel
{
    use Lotto28Trait;

    public $rememberCacheTag = 'lotto_keno_de';

    protected $lotto_name = 'de28';

    protected $table = 'lotto_keno_de';

    public function lottoCreate()
    {
        $count = $this->where('status', 1)->where('lotto_at', '>', date('Y-m-d H:i'))->count();
        if ($count >= 5) {
            return 'lotto limit';
        }

        $last_lotto  = $this->orderBy('id', 'desc')->first();
        $last_time   = strtotime($last_lotto->lotto_at);
        $next_second = 90;
        $next_mark   = 0;
        $next_at     = null;

        $next_time = $last_time + $next_second;
        $new_id    = $last_lotto->id + 1;
        if ($next_time < time()) {
            $time_a    = time();
            $time_b    = strtotime('2019-08-06') + 60;
            $diff      = intval(($time_a - $time_b) / 90) + 1;
            $new_id    = 100000 + $diff;
            $next_time = $diff * 90 + $time_b;
        }

        $next_at = date('Y-m-d H:i:s', $next_time);

        if ($next_at !== null & $next_at < date('Y-m-d H:i:s')) {
            return 'lotto_at error : ' . $next_at;
        }

        $data = [
            'id'       => $new_id,
            'status'   => 1,
            'lotto_at' => $next_at,
            'mark'     => $next_mark,
        ];

        return $this->create($data);
    }

    public function lottoOpen()
    {
        $items = $this->where('lotto_at', '<=', date('Y-m-d H:i:s'))
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->get();
        $items->makeVisible(['control']);
        foreach ($items as $item) {
            $this->getBetFromGd28($item->id);
            $this->lottoOpenItem($item);
        }

        return 'update';
    }

    public function lottoOpenItem($item)
    {
        $count   = 0;
        $control = new OpenControl();
        start:

        $open_code = $control->openCode();
        $formula   = LottoFormula::basic28($open_code);

        $lotto_index = $this->lotto_name . ':' . $item->id;

        //???????????????0 27 ??????????????? ???????????????????????????
        if (in_array($formula['code_he'], [0, 27]) && in_array($item->control, ['he_00', 'he_27']) === false) {
            $count = ControlBet::remember(10)->where('lotto_index', $lotto_index)->count();
            if ($count > 0) {
                goto start;
            }
        }

        //?????????????????????
        $control_val = $control->formulaBet($lotto_index, $open_code, $this->lotto_name);
        dump($control_val);
        if ($count <= 50) {
            if ($control_val > 0 && (mt_rand(0, 100) > 60 || $item->control === 'bet')) {
                $count += 1;
                goto start;
            }
        }

        //????????????????????? ??????
        if (stripos($item->control, 'he_') !== false) {
            $temp   = explode('_', $item->control);
            $win_he = (int) $temp[1];
            $source = $control->query()->where('win_he', $win_he)->where('status', 1)->first();

            if ($source === null) {
                goto save;
            }

            $open_code         = $source->open_code;
            $source->status    = 2;
            $source->used_at   = date('Y-m-d H:i:s');
            $source->used_info = [
                'lotto_id'    => $item->id,
                'lotto_model' => __CLASS__,
            ];
            $source->save();
        }

        save:
        $item->open_code = $open_code;
        $item->opened_at = date('Y-m-d H:i:s');
        $item->status    = 2;
        $item->save();

        LottoUtils::lottoOpenBroadcasts($this->lotto_name, $item->id);
    }

    private function getBetFromGd28($lotto_id)
    {
        $uri      = 'http://47.242.70.180:12433/auto/de28con?lotto_id=' . $lotto_id;
        $client   = new \GuzzleHttp\Client(['timeout' => 3]);
        $response = $client->get($uri);
        $data     = json_decode($response->getBody(), true);

        dump($data);

        if (count($data['bet_places']) > 0) {
            $lotto_index = $data['lotto_name'] . ':' . $data['lotto_id'];
            $item        = [
                'lotto_index' => $lotto_index,
                'bet_places'  => $data['bet_places'],
                'app_name'    => 'gd28',
            ];

            $create = ControlBet::create($item);
        }
    }
}
