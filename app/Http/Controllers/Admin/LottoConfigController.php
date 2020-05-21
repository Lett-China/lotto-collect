<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LottoModule\Models\LottoConfig;

class LottoConfigController extends Controller
{
    public function get(Request $request)
    {
        $lotto = $request->lotto_name;
        $item  = LottoConfig::remember(600)->find($lotto);
        $item || real()->exception('data.notexist');
        $result = $item->toArray();
        return real($result)->success();
    }

    public function update(Request $request)
    {
        $rule = ['method' => 'required'];
        $data = $request->all();
        real()->validator($data, $rule);

        $method = $request->method;
        return $this->$method($request);
    }

    private function betSettingUpdate(Request $request)
    {
        $item = LottoConfig::find($request->lotto_name);
        $item || real()->exception('data.notexist');

        $enum = $request->place;

        $data = [
            'place' => $request->place,
            'ratio' => $request->ratio,
            'odds'  => $request->odds,
        ];

        $result = $item->betSettingUpdate($enum, $data);

        return real($result)->success();
    }

    private function combinationUpdate(Request $request)
    {
        $item = LottoConfig::find($request->lotto_name);
        $item || real()->exception('data.notexist');

        $fields = [
            'bet_quota',
            'stop_ahead',
            'open_wait',
            'disable',
            'lotto_rule',
        ];
        $item->setUpdate($fields);
        $save = $item->save();
        $save || real()->exception('data.update.failed');
        $result = $item->toArray();
        return real($result)->success('data.update.success');
    }
}
