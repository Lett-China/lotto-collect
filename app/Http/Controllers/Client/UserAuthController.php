<?php
namespace App\Http\Controllers\Client;

use App\Models\User;
use App\Packages\Utils\SMS;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\UserReference;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\ModelTrait\ThisUserTrait;

class UserAuthController extends Controller
{
    use ThisUserTrait;

    public function get()
    {
        $this->user->requested_ip || $this->user->avatarUpdate();
        $this->user->updateRequestedAt();

        $wallet = $this->user->wallet;
        $wallet || $this->user->createWallet();

        $option = $this->user->option;
        $option || $this->user->createOption();

        $result = [
            'id'        => $this->user->id,
            'nickname'  => $this->user->nickname,
            'real_name' => $this->user->real_name,
            'avatar'    => $this->user->avatar,
            'mobile'    => $this->user->mobile,
            'status'    => $this->user->status,
            'safe_word' => $this->user->safe_word ? true : false,
            'wallet'    => $this->user->wallet,
            'option'    => $this->user->option,
            'ref_code'  => $this->user->ref_code,
            'trial'     => $this->user->trial,
            'ref_info'  => UserReference::getReference($this->user->id),
            'ws_host'   => env('WS_HOST') . ':' . env('WS_PORT'),
        ];

        return real($result)->success();
    }

    public function login(Request $request)
    {
        $credentials = [
            'mobile'   => $request->mobile,
            'password' => $request->password,
        ];

        $token = auth('users')->attempt($credentials);
        $token || real()->exception('login.request.failed');
        $ttl = auth('users')->factory()->getTTL() * 60;

        $user = User::where('mobile', $request->mobile)->first();
        $user->disable == 1 && real()->exception('您的账号被封禁');

        $this->user = $user;
        $result     = [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $ttl,
            'user_info'    => $this->get()['data'],
        ];
        return real($result)->success('login.request.success');
    }

    public function passwordUpdate(Request $request)
    {
        $this->user && $this->user->CheckIsTrial();
        $rule = [
            'mobile'   => 'required',
            'password' => 'required|min:6|max:18',
            'ver_code' => 'required',
        ];

        $data = $request->all();
        real()->validator($data, $rule);

        $check = SMS::check('password', $request->mobile, $request->ver_code);
        $check || real()->exception('ver_code.invalid');

        $user = User::where('mobile', $request->mobile)->first();
        $user || real()->exception('this.user.notexist');

        $user->password = $request->password;
        $temp           = $user->save();
        $temp || real()->exception('password.reset.failed');

        return real()->success('password.reset.success');
    }

    public function randNickname()
    {
        $arr_1    = config('nickname.arr_1');
        $arr_2    = config('nickname.arr_2');
        $rand_1   = rand(0, 331);
        $rand_2   = rand(0, 325);
        $nickname = $arr_1[$rand_1] . $arr_2[$rand_2];
        $result   = ['nickname' => $nickname];

        return real($result)->success();
    }

    public function register(Request $request)
    {
        if ($request->robot !== '112233') {
            $rule = [
                'mobile'   => 'required|mobile|unique:users',
                'password' => 'required|min:6|max:18',
                'nickname' => 'required|max:12',
                'ver_code' => 'required|int',
            ];

            $message = ['mobile.mobile' => '手机号不符合注册规则 请尝试更换'];

            $data = $request->all();
            real()->validator($data, $rule, $message);
        }

        if ($request->ver_code !== '112233') {
            $key  = 'register:' . $request->mobile;
            $temp = cache()->get($key);
            if (!$temp || $request->ver_code != $temp['ver_code']) {
                return real()->error('ver_code.check.invalid');
            }
            cache()->forget($key);
        }
        DB::beginTransaction();

        if ($request->ref_code) {
            if (strlen($request->ref_code) == 6) {
                $temp = User::where('ref_code', $request->ref_code)->first();
                $temp || real()->exception('该推荐码用户不存在 请重试');
                $temp->robot && real()->exception('该推荐码用户不能绑定下级');
                $temp->trial && real()->exception('该推荐码为试用账户 不能绑定下级');
                $ref_code = $request->ref_code;
            }
            if (strlen($request->ref_code) == 8) {
                $temp = User::find($request->ref_code);
                $temp || real()->exception('该推荐码用户不存在 请重试');
            }
            $ref_code = $temp->ref_code;
        }

        $nickname = $request->nickname ?? 'user-' . Str::random(8);
        $data     = [
            'id'         => 'user',
            'mobile'     => $request->mobile,
            'password'   => $request->password,
            'nickname'   => $nickname,
            'ref_code'   => mt_rand(100000, 999999),
            'status'     => 1,
            'robot'      => false,
            'created_ip' => $request->ip(),
        ];
        $request->robot === '112233' && $data['robot'] = 1;

        $user = User::create($data);
        $user || real()->exception('register.failed.retry');
        // $user->avatarUpdate();
        $user->createWallet();
        $user->createOption();

        if ($request->ref_code) {
            UserReference::createReference($user->id, $ref_code);
        }

        DB::commit();
        // $user->avatarUpdate();

        // //新用户注册送彩金
        // $ip_arr     = explode('.', $request->ip());
        // $ip_regexp  = $ip_arr[0] . '.' . $ip_arr[1] . '%';
        // $limit_date = date('Y-m-d H:i:s', strtotime('-1 day'));
        // $count_1    = User::where('requested_ip', 'like', $ip_regexp)->where('id', '!=', $user->id)->where('requested_at', '>', $limit_date)->count();
        // $count_2    = User::where('requested_ip', $request->ip())->where('id', '!=', $user->id)->count();

        // if ($count_1 <= 2 && $count_2 == 0 && !$request->robot) {
        //     $amount = config('act.register_first.amount');
        //     $user->wallet->balance('user.register.gift')->plus($amount);
        // }

        if ($request->method == 'login') {
            return $this->login($request);
        }

        return real()->success('register.request.success');
    }

    public function safeWordCheck(Request $request)
    {
        $safe_word = $request->safe_word;
        $check     = Hash::check($safe_word, $this->user->safe_word);
        $check || real()->exception('safe_word.check.error');
        return real()->success('safe_word.check.success');
    }

    //试玩账户
    public function trial(Request $request)
    {
        DB::beginTransaction();

        $nickname = $this->randNickname()['data']['nickname'];
        $data     = [
            'id'         => 'trial',
            'mobile'     => 'trial',
            'password'   => 'aa112233',
            'nickname'   => $nickname,
            'ref_code'   => 'Trials',
            'status'     => 1,
            'robot'      => false,
            'trial'      => true,
            'real_name'  => '试玩账户',
            'created_ip' => $request->ip(),
        ];

        $user = User::create($data);
        $user || real()->exception('register.failed.retry');
        // $user->avatarUpdate();
        $user->createWallet();
        $user->createOption();

        DB::commit();
        // $user->avatarUpdate();

        $user->wallet->balance('user.trial.recharge')->plus(5000);

        $request->offsetSet('mobile', $user->mobile);
        $request->offsetSet('password', $data['password']);
        return $this->login($request);
    }

    public function update(Request $request)
    {
        $rule = ['method' => 'required'];
        $data = $request->all();
        real()->validator($data, $rule);
        $method = $request->method;
        return $this->$method($request);
    }

    private function avatarUpdate(Request $request)
    {
        $this->user->CheckIsTrial();
        $rule = ['file' => 'required'];
        $data = $request->all();
        real()->validator($data, $rule);

        $result = $this->user->avatarUpdate($request->file);

        return real($result)->success('avatar.upload.success');
    }

    private function mobileUpdate(Request $request)
    {
        $this->user->CheckIsTrial();
        $user = $this->user;
        $user->mobile == $request->mobile && real()->exception('新手机号与当前手机号相同');

        $rule = [
            'mobile'   => 'required',
            'ver_code' => 'required',
        ];
        $data = $request->all();
        real()->validator($data, $rule);

        $check = SMS::check('mobileUpdate', $request->mobile, $request->ver_code);
        $check || real()->exception('ver_code.invalid');

        $exist = User::where('mobile', $request->mobile)->where('id', '!=', $user->id)->first();
        $exist && real()->exception('该手机号已被其它用户绑定');

        $user->mobile = $request->mobile;
        $save         = $user->save();
        $save || real()->exception('mobile.reset.failed');

        return real()->success('mobile.reset.success');
    }

    private function nicknameUpdate(Request $request)
    {
        $rule = ['nickname' => 'required|min:1|max:32'];
        $data = $request->all();
        real()->validator($data, $rule);

        $user           = $this->user;
        $user->nickname = $request->nickname;
        $save           = $user->save();

        $save || real()->exception('nickname.update.failed');
        return real($data)->success('nickname.update.success');
    }

    private function optionUpdate(Request $request)
    {
        $rule = ['bet_chat' => 'required|bool'];
        $data = $request->all();
        real()->validator($data, $rule);

        $option           = $this->user->option;
        $option->bet_chat = $request->bet_chat;
        $save             = $option->save();

        $save || real()->exception('option.update.failed');
        return real($data)->success('option.update.success');
    }

    private function realNameUpdate(Request $request)
    {
        $this->user->CheckIsTrial();
        DB::beginTransaction();
        $rule    = ['real_name' => 'required|min:2|max:4|real_name'];
        $data    = $request->all();
        $message = ['real_name.real_name' => '姓名校验失败 仅支持中文姓名'];
        real()->validator($data, $rule, $message);
        $user = $this->user;
        $user->real_name && real()->exception('您已经登记过真实姓名 暂不支持修改');

        $user->real_name = $request->real_name;
        $save            = $user->save();

        $save || real()->exception('real_name.update.failed');

        // //新用户注册送彩金 如果送过了 有重名的 注册大于7天的均不赠送
        // $has_gift = WalletLog::where('user_id', $user->id)->where('source_name', 'user.register.gift')->first();
        // $has_name = User::where('real_name', $request->real_name)->where('id', '!=', $user->id)->first();

        // if ($has_gift === null && $has_name === null && date('Y-m-d H:i:s', strtotime('-7 day')) < $user->created_at) {
        //     $amount = config('act.register_first.amount');
        //     $user->wallet->balance('user.register.gift')->plus($amount);
        // }

        DB::commit();

        $data['wallet'] = $user->wallet;
        return real($data)->success('real_name.update.success');
    }

    private function safeWordUpdate(Request $request)
    {
        $this->user->CheckIsTrial();
        $rule = [
            'mobile'    => 'required',
            'safe_word' => 'required|int|digits:6',
            'ver_code'  => 'required',
        ];

        $data = $request->all();
        real()->validator($data, $rule);

        $check = SMS::check('safeWord', $this->user->mobile, $request->ver_code);
        $check || real()->exception('ver_code.invalid');

        $this->user->safe_word = $request->safe_word;
        $temp                  = $this->user->save();
        $temp || real()->exception('safe_word.reset.failed');

        return real()->success('safe_word.reset.success');
    }
}
