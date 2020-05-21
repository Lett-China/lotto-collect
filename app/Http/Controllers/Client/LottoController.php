<?php
namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LottoModule\LottoUtils;
use App\Models\ModelTrait\ThisUserTrait;
use App\Models\LottoModule\Models\BetLog;
use App\Models\LottoModule\Models\LottoConfig;

class LottoController extends Controller
{
    use ThisUserTrait;

    public function betLog(Request $request)
    {
        $hidden = ['extend', 'lotto_index', 'trial', 'updated_at', 'confirmed_at', 'lotto_info.win_function', 'bet_places'];
        $data   = $this->user->betLog()->with('details')->orderBy('id', 'desc')->take(10);
        $all    = $data->orderBy('id', 'desc')->get()->makeHidden($hidden);
        $lotto  = $data->where('lotto_index', 'regexp', $request->lotto_name)->get()->makeHidden($hidden);

        $result = [
            'lotto'  => $lotto->toArray(),
            'all'    => $all->toArray(),
            'wallet' => $this->user->wallet,
        ];
        return real($result)->success();
    }

    public function betting(Request $request)
    {
        $rule = [
            'id'      => 'required',
            'checked' => 'required',
            'amount'  => 'required|currency',
        ];

        $message = ['id.required' => '暂无最新投注期号 请重试'];
        $data    = $request->all();
        real()->validator($data, $rule, $message);

        $name  = $request->lotto_name;
        $id    = $request->id;
        $total = $request->total;
        $place = [];

        foreach ($request->checked as $key => $value) {
            if ($value === true) {
                $place[$key] = [
                    'place'  => $key,
                    'amount' => $request->amount,
                ];
            }
        }

        $temp = $this->user->bet($name, $id)->place($place, $total, $request->amount);
        if ($temp !== true) {
            return $temp;
        }
        $lotto_index = $name . ':' . $id;
        $result      = [
            'success' => $temp,
            'was_bet' => $this->getWasBet($lotto_index),
            'wallet'  => $this->user->wallet,
        ];

        return real($result)->success('恭喜您下注成功 祝君好运');
    }

    public function config(Request $request)
    {
        $lotto  = $request->lotto_name;
        $config = LottoConfig::remember(600)->find($lotto)
            ->makeHidden(['sort', 'bet_quota', 'stop_ahead', 'disable', 'lotto_rule', 'updated_at', 'created_at', 'win_function', 'config_file'])
            ->toArray();
        return real($config)->debug($lotto)->success();
    }

    public function last(Request $request)
    {
        $model = LottoUtils::model();
        $model->makeHidden('open_code', 'opened_at', 'mark', 'extend');

        $lotto  = $request->lotto_name;
        $config = LottoConfig::remember(600)->find($lotto);
        if ($config->lotto_disable === true) {
            $data = $model->where('status', '!=', '1')->orderBy('id', 'desc')->take(10)->remember(600)->get();
        } else {
            $datetime = date('Y-m-d H:i:s', time() + $config->stop_ahead);
            $data     = $model->where('lotto_at', '<=', $datetime)->orderBy('id', 'desc')->take(10)->remember(600)->get();
        }

        $data->makeHidden(['updated_at', 'mark', 'status', 'bet_count_down', 'opened_at', 'open_code']);

        $result = [
            'items'  => $data->toArray(),
            'reload' => 10,
        ];
        return real($result)->debug($config->lotto_disable)->success();
    }

    public function newest(Request $request)
    {
        $lotto    = $request->lotto_name;
        $config   = LottoConfig::remember(600)->find($lotto);
        $datetime = date('Y-m-d H:i:s', time() + $config->stop_ahead);
        $model    = LottoUtils::model();
        $data     = $model->where('status', 1)->where('lotto_at', '>', $datetime)->first();

        if ($data === null) {
            $result = [
                'message' => '暂无最新一期<br>请稍后再试',
                'disable' => true,
                'was_bet' => (object) [],
            ];

            return real($result)->success();
        }

        $data->makeHidden(['open_code', 'status', 'mark', 'opened_at', 'win_code', 'lotto_name', 'win_extend', 'updated_at']);
        // $data || real()->exception('暂无最新一期<br>请稍后再试');

        $data->was_bet = $this->getWasBet($data->lotto_index);
        $result        = $data->toArray();
        return real($result)->success();
    }

    public function sendMessage(Request $request)
    {
        $rule = ['message' => 'required|max:10'];
        $data = $request->all();
        real()->validator($data, $rule);

        try {
            $content = explode(' ', $request->message);
            $content = array_filter($content);
            $content = array_merge($content, []);
            $str     = $content[0];
            $amount  = $content[1];
        } catch (\Throwable $th) {
            return real()->error('未识别到有效投注 请重新输入');
        }

        $money_reg = '/^[1-9]\d*|^[1-9]\d*.\d+[1-9]$/';
        preg_match($money_reg, $amount) || real()->exception('未识别到有效投注 请重新输入');

        $lotto  = $request->lotto_name;
        $config = LottoConfig::remember(600)->find($lotto);

        $checked   = [];
        $str_place = '';
        foreach ($config->bet_places as $key => $value) {
            if ((isset($value['regexp']) && in_array($str, $value['regexp'])) || $str == $value['name'] || $str == str_replace('-', '', $value['name'])) {
                $checked[$value['place']] = true;
                $str_place                = $value['name'];
            }

            if (count($checked) >= 1) {
                continue;
            }
        }

        $checked || real()->exception('未识别到有效投注 请重新输入');

        $result = [
            'checked'   => $checked,
            'amount'    => sprintf('%.2f', $amount),
            'total'     => sprintf('%.2f', $amount),
            'str_place' => $str_place,
        ];

        return real($result)->success();
    }

    public function united(Request $request)
    {
        $result = [
            'config' => $this->config($request)['data'],
            'newest' => $this->newest($request)['data'],
            'lasts'  => $this->last($request)['data'],
            'wallet' => $this->user->wallet,
        ];

        return real($result)->success();
    }

    private function getWasBet($lotto_index)
    {
        $bet_log = BetLog::where('user_id', $this->user->id)->where('lotto_index', $lotto_index)->get();
        $place   = [];
        foreach ($bet_log as $value) {
            foreach ($value->details as $detail) {
                $temp                  = isset($place[$detail->place]) ? $place[$detail->place] + $detail->amount : $detail->amount;
                $place[$detail->place] = sprintf('%.2f', $temp);
            }
        }

        return (object) $place;
    }
}
