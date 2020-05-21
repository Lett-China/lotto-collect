<?php

namespace App\Http\Controllers\Client;

use App\Models\Option;
use App\Models\Article;
use App\Models\OptionFocus;
use App\Http\Controllers\Controller;
use App\Models\ModelTrait\ThisUserTrait;

class OptionController extends Controller
{
    use ThisUserTrait;

    public function focus()
    {
        $data = OptionFocus::query();
        $data->remember(600);
        $data->where('scope', 'regexp', 'wap_home');
        $data->orderBy('id', 'desc');
        $fields = ['image', 'mapping', 'params'];
        return $data->get($fields)->toArray();
    }

    public function get()
    {
        // $result          = $this->option();
        $result          = [];
        $result['focus'] = $this->focus();
        $result['lotto'] = $this->lotto();
        $result['bet']   = [
            '1' => [
                'title' => '待开奖',
            ],
            '2' => [
                'title' => '已返奖',
            ],
            '3' => [
                'title' => '取消投注',
            ],
            '4' => [
                'title' => '投注异常',
            ],
        ];
        $result['promote'] = $this->promote();
        return real($result)->success();
    }

    public function option()
    {
        $option = Option::remember(600)->get();
        $result = [];
        foreach ($option as $source) {
            $name          = $source->name;
            $value         = $source->value;
            $result[$name] = $value;
        }
        return $result;
    }

    private function dialog()
    {
        if ($this->user === null) {
            return null;
        }
    }

    private function lotto()
    {
        return config('lotto.web');
    }

    private function promote()
    {
        $article = Article::find('100008');
        return ['intro' => $article->content];
    }
}
