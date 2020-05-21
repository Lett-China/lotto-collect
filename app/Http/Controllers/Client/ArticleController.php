<?php

namespace App\Http\Controllers\Client;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ArticleController extends Controller
{
    public function get(Request $request)
    {
        $result = Article::remember(600)->find($request->id)->toArray();
        return real($result)->success();
    }

    public function index(Request $request)
    {
        $fields = ['title', 'thumb', 'excerpt', 'id', 'created_at'];
        $data   = Article::query($fields)->where('status', 1)->where('cat_id', $request->cat_id);
        $data->orderBy('id', 'desc');
        $data->remember(600);
        $items  = $data->get();
        $result = ['items' => $items->toArray()];
        return real($result)->success();
    }
}
