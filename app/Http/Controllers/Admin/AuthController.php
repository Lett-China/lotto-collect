<?php
namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function avatarUpdate(Request $request)
    {
        $rule = ['file' => 'required'];
        $data = $request->all();
        real()->validator($data, $rule);

        $user   = auth('admin')->user();
        $result = $user->avatarUpdate($request->file);

        return real($result)->success();
    }

    public function get()
    {
        $user = auth('admin')->user();
        $user->updateRequestedAt();
        //获取在线客户TOKEN
        $user->service_token = auth('users')->tokenById(10000000);
        $user->ws_host       = env('WS_HOST') . ':' . env('WS_PORT');
        $result              = $user->toArray();
        return real($result)->success();
    }

    /**
     * 管理员登录
     * --------------------------------------
     * @_group       admin.auth.module
     * @_name        admin.login
     * @_method      POST
     * @_form        username=:admin_username
     * @_form        password=:admin_password
     * @_event       set_token|admin_token
     * --------------------------------------
     * @param  Request $request
     * @return void
     */
    public function login(Request $request)
    {
        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];
        $admin = auth('admin');
        $token = $admin->attempt($credentials);
        $token || real()->exception('login.request.failed');

        $admin->user()->disable === '1' && real()->exception('login.disable');

        $ttl = auth('admin')->factory()->getTTL() * 60;

        $result = [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $ttl,
        ];
        return real($result)->success('login.request.success');
    }

    public function passwordUpdate(Request $request)
    {
        $user = auth('admin')->user();

        $check = Hash::check($request->current, $user->password);
        $check || real()->exception('current.password.check.failed');

        $rule = [
            'current'  => 'required',
            'password' => 'required|confirmed|min:6|max:18',
        ];
        $data = $request->all();
        real()->validator($data, $rule);

        $user->password = $request->password;
        $save           = $user->save();

        $save || real()->exception('password.update.failed');
        return real()->success('password.update.success');
    }

    public function profileUpdate(Request $request)
    {
        $rule = ['nickname' => 'min:1|max:18'];
        $data = $request->all();
        real()->validator($data, $rule);
        $user = auth('admin')->user();

        $request->nickname && $user->nickname = $request->nickname;

        $save = $user->save();
        $save || real()->exception('profile.update.failed');
        return real()->success('profile.update.success');
    }

    public function update(Request $request)
    {
        // return real()->error('模拟更新错误');
        $rule = ['method' => 'required'];
        $data = $request->all();
        real()->validator($data, $rule);

        $method = $request->method;
        return $this->$method($request);
    }
}
