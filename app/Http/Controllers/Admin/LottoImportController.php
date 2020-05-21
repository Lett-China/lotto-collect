<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LottoModule\LottoUtils;

class LottoImportController extends Controller
{
    public function api(Request $request)
    {
        $rule = [
            'source_code' => 'required',
            'source_date' => 'required',
        ];
        $data = $request->all();
        real()->validator($data, $rule);

        $code = $request->source_code;
        $date = $request->source_date;

        $data = LottoUtils::openCaiAPI($code, 'day', $date);
        isset($data->rows) || real($data)->exception('源数据错误，请重试');
        $import = $this->import($data);
        $result = ['import' => $import, 'code' => $code, 'date' => $date];
        return real($result)->success();
    }

    public function import($source)
    {
        $mapping = config('lotto.model.collect');
        if (isset($mapping[$source->code]) === false) {
            return false;
        }

        $model  = $mapping[$source->code];
        $result = [];
        foreach (array_reverse($source->data) as $value) {
            $data = [
                'id'        => $value->expect,
                'open_code' => $value->opencode,
                'opened_at' => $value->opentime,
            ];

            try {
                $temp     = app($model)->lottoOpen($data);
                $result[] = $value->expect . ': ' . $temp;
            } catch (\Throwable $th) {
                $result[] = $value->expect . ': ' . $th->getMessage();
            }
        }

        return $result;
    }

    public function text(Request $request)
    {
        $rule = ['source_text' => 'required'];
        $data = $request->all();
        real()->validator($data, $rule);

        $data = json_decode($request->source_text);
        $data || real()->exception('源数据格式错误 请重试');

        $import = $this->import($data);
        $result = ['import' => $import];
        return real($result)->success();
    }
}
