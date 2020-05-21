<?php
namespace App\Http\Controllers\Client;

use App\Models\User;
use App\Packages\Utils\SMS;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ModelTrait\ThisUserTrait;

class SMSController extends Controller
{
    use ThisUserTrait;

    public function get(Request $request)
    {
        $rule = ['mobile' => 'required|int'];
        $data = $request->all();
        real()->validator($data, $rule);
        $method = $request->privateMethod;
        return $this->$method($request);
    }

    private function mobileUpdate(Request $request)
    {
        $this->user->CheckIsTrial();
        $this->user = auth('users')->user();
        $this->user->mobile == $request->mobile && real()->exception('新手机号与当前手机号相同');
        $exist = User::where('mobile', $request->mobile)->where('id', '!=', $this->user->id)->first();
        $exist && real()->exception('该手机号已被其它用户绑定');
        $result = $this->sendMessage('mobileUpdate', $request->mobile);
        return real($result)->success('ver_code.get.success');
    }

    private function password(Request $request)
    {
        $this->user && $this->user->CheckIsTrial();
        $check = User::query()->where('mobile', $request->mobile)->first();
        $check || real()->exception('this.mobile.user.notexist');
        $result = $this->sendMessage('password', $request->mobile);
        return real($result)->success('ver_code.get.success');
    }

    private function register(Request $request)
    {
        $rule    = ['mobile' => 'required|mobile|unique:users|int'];
        $data    = $request->all();
        $message = [
            'mobile.mobile' => '手机号不符合注册规则 请尝试更换',
            'mobile.unique' => '该手机以注册 请直接登陆',
        ];
        real()->validator($data, $rule, $message);

        $result = $this->sendMessage('register', $request->mobile);
        return real($result)->success('ver_code.get.success');
    }

    private function safeWord()
    {
        $this->user->CheckIsTrial();
        $result = $this->sendMessage('safeWord', $this->user->mobile);
        return real($result)->success('ver_code.get.success');
    }

    private function sendMessage($prefix, $mobile)
    {
        return SMS::send($prefix, $mobile);
    }
}
