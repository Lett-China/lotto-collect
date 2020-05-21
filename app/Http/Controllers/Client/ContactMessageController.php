<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Models\ContactMessage;
use App\Http\Controllers\Controller;
use App\Models\ModelTrait\ThisUserTrait;

class ContactMessageController extends Controller
{
    use ThisUserTrait;
    public function create(Request $request)
    {
        $rule = [
            'name'    => 'required|max:6',
            'mobile'  => 'required|mobile',
            'content' => 'required',
        ];

        $data = [
            'user_id' => $this->user->id,
            'content' => $request->content,
            'mobile'  => $request->mobile,
            'name'    => $request->name,
        ];
        real()->validator($data, $rule);

        $temp = ContactMessage::create($data);
        $temp || real()->exception('system.error.retry');

        return real()->success('留言成功，感谢您的反馈');
    }
}
