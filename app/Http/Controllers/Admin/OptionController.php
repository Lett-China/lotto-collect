<?php

namespace App\Http\Controllers\Admin;

use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class OptionController extends Controller
{
    public function get()
    {
        $data   = Option::get();
        $result = [];
        foreach ($data as $source) {
            $name          = $source->name;
            $value         = $source->value;
            $result[$name] = $value;
        }
        return real($result)->success();
    }

    public function update(Request $request)
    {
        $rule = [
            'name'  => 'required',
            'value' => 'required|array',
        ];
        $data = $request->all();
        real()->validator($data, $rule);

        $where = ['name' => $request->name];
        $value = ['value' => $request->value];
        $data  = Option::updateOrCreate($where, $value);

        return real()->success('data.update.success');
    }

    public function updatePatch(Request $request)
    {
        DB::beginTransaction();
        foreach ($request->post() as $name => $value) {
            $where = ['name' => $name];
            $value = ['value' => $value];
            $data  = Option::updateOrCreate($where, $value);
            $data || real()->exception('data.update.faild');
        }
        DB::commit();
        return real()->success('data.update.success');
    }
}
