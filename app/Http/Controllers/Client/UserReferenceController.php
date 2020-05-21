<?php

namespace App\Http\Controllers\Client;

use App\Models\User;
use App\Models\Commission;
use Illuminate\Http\Request;
use App\Models\UserReference;
use App\Http\Controllers\Controller;
use App\Models\ModelTrait\ThisUserTrait;

class UserReferenceController extends Controller
{
    use ThisUserTrait;

    public function check(Request $request)
    {
        $this->user->CheckIsTrial();
        $result = $this->validator($request);

        return real($result)->success('验证成功，请核对上级昵称');
    }

    public function commission()
    {
        $this->user->CheckIsTrial();
        $items = Commission::with('logs:parent_id,amount,user_id,type')
            ->where('user_id', $this->user->id)
            ->where('created_at', '>', date('Y-m-d', strtotime('-31 day')))
            ->orderBy('id', 'desc')
            ->get(['id', 'user_id', 'total', 'date']);

        $date  = date('Y-m-d');
        $dates = [];
        for ($i = 0; $i < 30; $i++) {
            $temp = date('Y-m-d', strtotime($date . ' -' . $i . 'day'));

            if ($this->user->created_at > $temp) {
                continue;
            }

            // if ($temp == date('Y-m-d') && date('H' < 3)) {
            //     continue;
            // }

            $dates[$temp] = [
                'total' => '0.00',
                'logs'  => [],
                'date'  => $temp,
            ];
        }

        $stats = [
            'days'   => 0,
            'total'  => 0,
            'person' => 0,
        ];

        foreach ($items as $value) {
            $stats['days'] += 1;
            $dates[$value->date] = $value->toArray();
            $stats['total'] += $value->total;
            $stats['person'] += count($value->logs);
        }

        $result = [
            'items' => array_values($dates),
            'stats' => $stats,
        ];
        return real($result)->success();
    }

    public function create(Request $request)
    {
        $this->user->CheckIsTrial();
        $result = $this->validator($request);
        $create = UserReference::createReference($this->user->id, $request->ref_code);
        $create || real()->exception('添加上级用户失败 请重试');

        return real($result)->success('添加上级用户成功');
    }

    public function level(Request $request)
    {
        $this->user->CheckIsTrial();
        $user_id = $request->user_id ? $request->user_id : $this->user->id;
        $items   = UserReference::with('user:id,nickname')->where('ref_id', $user_id)->get(['user_id', 'ref_id', 'created_at']);
        $current = User::find($user_id, ['id', 'nickname']);

        $specific = UserReference::where('user_id', $user_id)->first();

        if ($user_id === $this->user->id) {
            $excerpt = '您目前共有' . count($items) . '名下级用户';
        } else {
            $excerpt = 'Ta是您的' . $specific->level . '级用户';
        }

        $result = [
            'current'  => $current->toArray(),
            'items'    => $items->toArray(),
            // 'items'    => [],
            'excerpt'  => $excerpt,
            'specific' => $specific,
        ];

        return real($result)->success();
    }

    private function validator(Request $request)
    {
        $rule = ['ref_code' => 'required|max:6'];

        $data = $request->all();
        real()->validator($data, $rule);

        $user = User::where('ref_code', $request->ref_code)->first();
        $user || real()->exception('该推荐人不存在，请检查推荐码');

        $had = UserReference::where('user_id', $this->user->id)->count();
        $had && real()->exception('您已有上级推荐人，无法再补登');

        $had = UserReference::where('ref_str', 'regexp', $this->user->id)->count();
        $had && real()->exception('您已有下级用户，无法再补登');

        if (date('Y-m-d', strtotime('-8 day')) >= $this->user->created_at) {
            return real()->error('您已注册超过7天 无法再补登');
        }

        return [
            'nickname' => $user->nickname,
            'avatar'   => $user->avatar,
            'ref_code' => $user->ref_code,
        ];
    }
}
