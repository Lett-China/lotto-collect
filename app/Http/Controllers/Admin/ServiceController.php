<?php
namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\PushMessage;
use Illuminate\Http\Request;
use App\Packages\Utils\PushEvent;
use App\Http\Controllers\Controller;

class ServiceController extends Controller
{
    public function check()
    {
        $count  = PushMessage::where('read', 0)->where('to', 10000000)->count();
        $result = ['unread' => $count];
        return real($result)->success();
    }

    public function image(Request $request)
    {
        $rule = ['file' => 'required'];
        $data = $request->all();
        real()->validator($data, $rule);

        $image = PushMessage::uploadImage($request->file);

        $data = [
            'to'           => $request->to,
            'from'         => 10000000,
            'message_type' => 'image',
            'message'      => $image['path'],
            'index'        => 'private',
        ];

        $create          = PushMessage::create($data);
        $create->last_at = strtotime($create->created_at);
        $create->target  = $create->target()->first(['id', 'nickname'])->toArray();

        $result = $create->toArray();
        $push   = PushEvent::name('service')->toUser($request->to)->data($result);
        $push   = PushEvent::name('service')->toUser(10000000)->data($result);

        return real($result)->success('image.upload.success');
    }

    public function last()
    {
        $data = PushMessage::with('target:id,nickname')
        // ->where('created_at', '>', date('Y-m-d H:i:s', time() - 86400))
            ->orderBy('id', 'desc')
            ->get()->toArray();
        $group = collect($data)->groupBy('index');

        $last = [];
        $new  = false;
        foreach ($group as $value) {
            $temp = $value[0];

            $unread = false;
            foreach ($value as $_value) {
                if ($_value['read'] == false && $_value['to'] == 10000000) {
                    $unread = true;
                    $new    = true;
                    continue;
                }
            }

            $target = $temp['target'];
            unset($temp['target']);
            $last[$target['id']] = [
                'target'  => $target,
                'record'  => [$temp],
                'last_at' => strtotime($temp['created_at']),
                'unread'  => $unread,
            ];
        }

        $result = [
            'items'  => $last,
            'latest' => array_values($last)[0]['target']['id'],
            'new'    => $new,
        ];
        return real($result)->success();
    }

    public function read(Request $request)
    {
        $rule = ['id' => 'required'];

        $data = $request->all();
        real()->validator($data, $rule);

        $update = ['read' => 1, 'read_at' => date('Y-m-d H:i:s')];

        $temp = PushMessage::where('from', $request->id)->where('to', 10000000)->where('read', 0)->update($update);
        return real()->debug($temp)->success();
    }

    public function record(Request $request)
    {
        $target = User::find($request->id);
        $extend = [
            'target' => [
                'nickname' => $target->nickname,
                'id'       => $target->id,
                'avatar'   => $target->avatar],
        ];

        $data   = PushMessage::where('index', 'regexp', $request->id)->latest('id');
        $result = $data->paginate(50)->toArray();

        $result['data'] = array_reverse($result['data']);
        return real($extend)->listPage($result)->success();
    }

    public function send(Request $request)
    {
        $rule = [
            'to'      => 'required|int',
            'message' => 'required',
        ];

        $data = $request->all();
        real()->validator($data, $rule);

        $data = [
            'to'           => $request->to,
            'from'         => 10000000,
            'message_type' => 'text',
            'message'      => $request->message,
            'index'        => 'private',
        ];

        $create          = PushMessage::create($data);
        $create->last_at = strtotime($create->created_at);
        $create->target  = $create->target()->first(['id', 'nickname'])->toArray();

        $result = $create->toArray();
        PushEvent::name('service')->toUser($request->to)->data($result);
        PushEvent::name('service')->toUser(10000000)->data($result);

        return real($result)->success('message.send.success');
    }
}
