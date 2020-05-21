<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\ContactMessage;
use App\Http\Controllers\Controller;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $data = ContactMessage::with('user:id,nickname');
        $request->status && $data->where('status', $request->status);
        $request->content && $data->where('content', 'regexp', $request->content);
        $request->name && $data->where('name', 'regexp', $request->name);
        $request->mobile && $data->where('mobile', 'regexp', $request->mobile);
        $data->orderBy('id', 'desc');
        $result = $data->paginate(10)->toArray();
        return real()->listPage($result)->success();
    }

    public function update(Request $request)
    {
        $rule = [
            'id'     => 'required|int',
            'status' => 'required',
        ];
        $data = $request->all();
        real()->validator($data, $rule);

        $data         = ContactMessage::with('user:id,nickname')->find($request->id);
        $data->status = $request->status;
        $data->save();

        $result = $data->toArray();
        return real($result)->success('data.update.success');
    }

    public function delete(Request $request)
    {
        $rule = ['id' => 'required|int'];
        $data = $request->all();
        real()->validator($data, $rule);

        $article = ContactMessage::find($request->id);
        $article || real()->exception('data.notexist');
        $article->delete();
        $article->trashed() || real()->exception('data.delete.fail.retry');

        return real()->success('data.delete.success');
    }
}
