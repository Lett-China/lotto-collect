<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdministratorController extends Controller
{

    public function create(Request $request)
    {
        $rule = [
            'username' => 'required|username|min:4|max:16|unique:admins',
            'password' => 'required|min:6|max:18',
            'nickname' => 'required',
        ];

        $data = $request->all();
        real()->validator($data, $rule);

        $data = [
            'username' => $request->username,
            'password' => $request->password,
            'nickname' => $request->nickname,
        ];
        $admin = Admin::create($data);

        $admin || real()->exception('admin.create.failed');
        $admin->avatarUpdate();
        $result = $admin->toArray();

        return real($result)->success('admin.create.success');
    }

    public function index(Request $request)
    {
        $data = Admin::select();
        $request->nickname && $data->where('nickname', 'regexp', $request->nickname);
        $request->username && $data->where('username', 'regexp', $request->username);
        $result = $data->orderBy('id', 'desc')->paginate(12)->toArray();
        return real()->listPage($result)->success();
    }

    public function delete(Request $request)
    {
        $rule = ['id' => 'required|int'];
        $data = $request->all();
        real()->validator($data, $rule);

        $admin = Admin::find($request->id);
        $admin || real()->exception('this.admin.notexist');
        $admin->lock && real()->exception('该用户被系统保留 无法删除');

        $temp = $admin->delete();
        $temp || real()->exception('admin.delete.failed');

        return real()->success('admin.delete.success');
    }

    public function get(Request $request)
    {
        $rule = ['id' => 'required|int'];
        $data = $request->all();
        real()->validator($data, $rule);

        $admin  = Admin::find($request->id);
        $result = $admin->toArray();

        return real($result)->success();
    }

    public function update(Request $request)
    {
        $rule = ['method' => 'required'];
        $data = $request->all();
        real()->validator($data, $rule);

        $method = $request->method;
        return $this->$method($request);
    }

    public function profileUpdate(Request $request)
    {
        $rule = [
            'id'       => 'required|int',
            'nickname' => 'min:1|max:18',
            'username' => 'username',
        ];

        $data = $request->all();
        real()->validator($data, $rule);

        $admin = Admin::find($request->id);
        $admin || real()->exception('current.admin.notexist');

        $admin->nickname = $request->nickname;
        $admin->username = $request->username;
        $admin->disable  = $request->disable;
        $update          = $admin->save();
        $update || real()->exception('admin.update.failed');
        $result = $admin->toArray();
        return real($result)->success('admin.update.success');
    }

    public function avatarUpdate(Request $request)
    {
        $rule = [
            'id'   => 'required|int',
            'file' => 'required',
        ];

        $data = $request->all();
        real()->validator($data, $rule);

        $admin = Admin::find($request->id);
        $admin || real()->exception('this.admin.notexist');

        $image  = $admin->avatarUpdate($request->file);
        $result = array_merge($admin->toArray(), $image);
        return real($result)->success();
    }

    public function passwordUpdate(Request $request)
    {
        $rule = [
            'id'       => 'required',
            'password' => 'required|confirmed|min:6|max:18',
        ];
        $data = $request->all();
        real()->validator($data, $rule);

        $admin = Admin::find($request->id);
        $admin || real()->exception('this.admin.notexist');
        $admin->password = $request->password;
        $temp            = $admin->save();
        $temp || real()->exception('admin.update.failed');
        return real()->success('admin.password.update.success');
    }

}
