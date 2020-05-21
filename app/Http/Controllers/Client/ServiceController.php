<?php

namespace App\Http\Controllers\Client;

use App\Models\PushMessage;
use Illuminate\Http\Request;
use App\Packages\Utils\PushEvent;
use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;
use App\Models\ModelTrait\ThisUserTrait;

class ServiceController extends Controller
{
    use ThisUserTrait;

    public function image(Request $request)
    {
        $rule = ['file' => 'required'];
        $data = $request->all();
        real()->validator($data, $rule);

        $image = PushMessage::uploadImage($request->file);

        $data = [
            'to'           => 10000000,
            'from'         => $this->user->id,
            'message_type' => 'image',
            'message'      => $image['path'],
            'index'        => 'private',
        ];

        $create          = PushMessage::create($data);
        $create->last_at = strtotime($create->created_at);
        $create->target  = $create->target()->first(['id', 'nickname'])->toArray();

        $result = $create->toArray();
        $push   = PushEvent::name('service')->toUser(10000000)->data($result);
        $push   = PushEvent::name('service')->toUser($this->user->id)->data($result);

        return real($result)->success('image.upload.success');
    }

    public function index()
    {
        $index = '10000000:' . $this->user->id;
        $items = PushMessage::where('index', $index);
        $items->orderBy('id', 'desc');
        $result = $items->paginate(20)->toArray();
        $unread = PushMessage::where('to', $this->user->id)->where('read', 0)->count();

        // dd($unread, $this->user->id);
        $extend = ['unread' => $unread > 0 ? true : false];
        return real($extend)->listPage($result)->success();
    }

    public function read()
    {
        $update = ['read' => 1, 'read_at' => date('Y-m-d H:i:s')];
        $temp   = PushMessage::where('to', $this->user->id)->where('from', 10000000)->where('read', 0)->update($update);
        return real()->debug($temp)->success();
    }

    public function send(Request $request)
    {
        $rule = ['message' => 'required'];
        $data = $request->all();
        real()->validator($data, $rule);

        $data = [
            'to'           => 10000000,
            'from'         => $this->user->id,
            'message_type' => 'text',
            'message'      => $request->message,
            'index'        => 'private',
        ];

        $create          = PushMessage::create($data);
        $create->last_at = strtotime($create->created_at);
        $create->target  = $create->target()->first(['id', 'nickname'])->toArray();

        $result = $create->toArray();
        $push   = PushEvent::name('service')->toUser(10000000)->data($result);
        $push   = PushEvent::name('service')->toUser($this->user->id)->data($result);

        return real($result)->debug($push)->success('message.send.success');
    }
}
