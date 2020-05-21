<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LottoModule\Models\LottoConfig;

class ConfigController extends Controller
{
    public function get()
    {
        $focus = [
            'items' => config('focus.items'),
            'scope' => config('focus.scope'),
        ];

        $result = [
            'focus_mapping' => $focus['items'],
            'focus_scope'   => $focus['scope'],
            'lotto_all'     => $this->LottoConfig(),
            'open_cai_code' => config('lotto.open_cai.code'),
            'lotto_control' => ['hero28', 'bit28', 'de28'],
            'audio'         => $this->audio(),
        ];

        return real($result)->success();
    }

    private function LottoConfig()
    {
        $fields = ['title', 'name'];
        $data   = LottoConfig::orderBy('sort', 'asc')->get($fields);
        $result = [];
        foreach ($data as $value) {
            $result[$value->name] = $value;
        }
        return $result;
    }

    private function audio()
    {
        $result = [
            'message'      => 'https://hero-28.oss-cn-hongkong.aliyuncs.com/file/message.mp3',
            'withdraw'     => 'https://hero-28.oss-cn-hongkong.aliyuncs.com/file/withdraw.mp3',
            'recharge'     => 'https://hero-28.oss-cn-hongkong.aliyuncs.com/file/recharge.mp3',
            'gdErrorAgent' => 'https://hero-28.oss-cn-hongkong.aliyuncs.com/file/gd-error-agent-card.mp3',
            'gdErrorTotal' => 'https://hero-28.oss-cn-hongkong.aliyuncs.com/file/gd-error-kuying.mp3',
        ];

        return $result;
    }
}
