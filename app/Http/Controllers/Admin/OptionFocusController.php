<?php

namespace App\Http\Controllers\Admin;

use App\Models\OptionFocus;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OptionFocusController extends Controller
{
    public function index()
    {
        $data = OptionFocus::query();
        $data->orderBy('id', 'desc');
        $result          = [];
        $result['items'] = $data->get()->toArray();
        return real($result)->success();

    }

    public function create(Request $request)
    {
        $rule = [
            'mapping' => 'required',
            'params'  => 'required|array',
            'scope'   => 'required|array',
        ];
        $data = $request->all();
        real()->validator($data, $rule);

        $data = [
            'mapping' => $request->mapping,
            'params'  => $request->params,
            'image'   => $request->image,
            'scope'   => $request->scope,
        ];

        $create = OptionFocus::create($data);
        $create || real()->exception('option.create.failed');

        $result = $create->toArray();
        return real($result)->success('option.create.success');
    }

    public function update(Request $request)
    {
        $rule = [
            'id'      => 'required|int',
            'mapping' => 'required',
            'params'  => 'required|array',
            'scope'   => 'required|array',
        ];
        $data = $request->all();
        real()->validator($data, $rule);

        $data          = OptionFocus::find($request->id);
        $data->mapping = $request->mapping;
        $data->params  = $request->params;
        $data->image   = $request->image;
        $data->scope   = $request->scope;
        $save          = $data->save();

        $save || real()->exception('focus.update.failed');
        $result = $data->toArray();
        return real($result)->success('focus.update.success');
    }

    public function delete(Request $request)
    {
        $rule = ['id' => 'required|int'];
        $data = $request->all();
        real()->validator($data, $rule);

        $focus = OptionFocus::find($request->id);
        $focus || real()->exception('this.focus.notexist');

        $focus->delete();
        $focus->trashed() || real()->exception('this.focus.delete.fail.retry');

        return real()->success('this.focus.delete.success');
    }

}
