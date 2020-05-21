<?php

namespace App\Http\Controllers\Admin;

use App\Models\SinglePage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SinglePageController extends Controller
{
    public function index(Request $request)
    {
        $data = SinglePage::query();
        $data->where('type', $request->type);
        $result          = [];
        $result['items'] = $data->ordered()->get()->toArray();
        return real($result)->success();
    }

    public function create(Request $request)
    {
        $rule = [
            'title'   => 'required|max:30',
            'content' => 'required',
        ];
        $data = $request->only(['title', 'content', 'type']);
        real()->validator($data, $rule);

        $create = SinglePage::create($data);
        $create || real()->exception('create.failed');

        $result = $create->toArray();
        return real($result)->success('create.success');
    }

    public function update(Request $request)
    {
        $rule = [
            'id'      => 'required|int',
            'title'   => 'required|max:30',
            'content' => 'required',
        ];
        $data = $request->only(['id', 'title', 'content']);
        real()->validator($data, $rule);

        $data = SinglePage::find($request->id);
        $data || real()->exception('data.notexist');
        $sort = $data->sort;

        $data->title   = $request->title;
        $data->content = $request->content;
        $data->save();

        if ($sort != $request->sort) {
            $ids = SinglePage::where('type', $data->type)
                ->where('id', '!=', $data->id)
                ->ordered()->pluck('id')->toArray();
            array_splice($ids, $request->sort - 1, 0, $data->id);
            SinglePage::setNewOrder($ids);
            $data          = SinglePage::find($request->id);
            $data->refresh = true;
        }

        $result = $data->toArray();
        return real($result)->success('data.update.success');
    }

    public function delete(Request $request)
    {
        $rule = ['id' => 'required|int'];
        $data = $request->all();
        real()->validator($data, $rule);

        $data = SinglePage::find($request->id);
        $data || real()->exception('data.notexist');
        $data->delete();
        $data->trashed() || real()->exception('data.delete.fail.retry');
        return real()->success('data.delete.success');
    }
}
