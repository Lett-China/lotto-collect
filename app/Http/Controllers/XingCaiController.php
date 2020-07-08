<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class XingCaiController extends Controller
{
    protected $lotto_mapping = [];

    protected $model_mapping = [];

    public function notify(Request $request)
    {
        $this->model_mapping = config('lotto.collect_api.xing_cai');
        $this->lotto_mapping = config('lotto.model.system');

        $lotto_name = $this->model_mapping[$request->enname];
        $model      = $this->lotto_mapping[$lotto_name];
        $check      = strtoupper(md5($request->enname . $request->expect . $request->opencode . '7f9aa602dab0ed2866af8f4a5a8126b6'));
        if ($request->sign !== $check) {
            return real()->debug($check)->error('sign check error.');
        }

        $lotto_id = $request->expect;
        if ($lotto_name === 'xjssc') {
            $lotto_id = substr($request->expect, 0, 8) . '0' . substr($request->expect, 8);
        }

        $data = [
            'id'        => $request->expect,
            'open_code' => $request->opencode,
            'opened_at' => $request->opentime,
        ];

        try {
            $result = app($model)->lottoOpen($data);
        } catch (\Throwable $th) {
            $result = $th->getMessage();
        }

        // dump($result);

        return real()->debug($result)->success();
    }
}
