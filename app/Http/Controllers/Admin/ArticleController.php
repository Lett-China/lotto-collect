<?php

namespace App\Http\Controllers\Admin;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ArticleController extends Controller
{
    /**
     * 获取文章列表
     * --------------------------------------
     * @_group         article.manage.module
     * @_name          article.list
     * @_method        GET
     * @_query         id
     * @_query         title
     * @_query         page
     * @_query         nickname
     * @_query         status
     * --------------------------------------
     * @param  Request $request
     * @return void
     */
    public function index(Request $request)
    {

        $data = article::with('author:id,nickname')->with('category:id,name');

        $request->cat_id && $data->where('cat_id', $request->cat_id);
        $request->id && $data->where('id', $request->id);
        $request->status && $data->where('status', $request->status);
        $request->title && $data->where('title', 'regexp', $request->title);
        $data->orderBy('id', 'desc');

        $result = $data->paginate(10)->toArray();

        return real()->listPage($result)->success();
    }

    /**
     * 获取文章详情
     * --------------------------------------
     * @_group         article.manage.module
     * @_name          article.detail.get
     * @_method        GET
     * @_query         id
     * --------------------------------------
     * @param  Request $request
     * @return void
     */
    public function get(Request $request)
    {
        $rule = ['id' => 'required|int'];
        $data = $request->all();
        real()->validator($data, $rule);

        $article = Article::find($request->id);
        $article || real()->exception('this.article.notexist');

        $result = $article->toArray();
        return real($result)->success();
    }

    /**
     * 创建文章
     * --------------------------------------
     * @_group         article.manage.module
     * @_name          article.create
     * @_method        POST
     * @_form          title
     * @_form          content
     * @_form          excerpt
     * @_form          status|article.status.desc
     * --------------------------------------
     * @param  Request $request
     * @return void
     */
    public function create(Request $request)
    {

        $this->baseValidator($request);

        $data = [
            'title'     => $request->title,
            'content'   => $request->content,
            'excerpt'   => $request->excerpt,
            'status'    => $request->status ?? true,
            'author_id' => auth('admin')->id(),
            'thumb'     => $request->thumb,
            'cat_id'    => $request->cat_id,
        ];

        $article = Article::create($data);
        $article || real()->exception('article.create.failed.retry');
        $result = $article->toArray();
        return real($result)->success('articel.create.success');
    }

    /**
     * 更新文章
     * --------------------------------------
     * @_group         article.manage.module
     * @_name          article.update
     * @_method        POST
     * @_form          id
     * @_form          title
     * @_form          content
     * @_form          excerpt
     * @_form          status|article.status.desc
     * @_form          created_at
     * --------------------------------------
     * @param  Request $request
     * @return void
     */
    public function update(Request $request)
    {

        $extend = ['id' => 'required|int'];
        $this->baseValidator($request, $extend);

        $article = Article::find($request->id);

        $article || real()->exception('this.article.notexist');

        $article->title   = $request->title;
        $article->content = $request->content;
        $article->excerpt = $request->excerpt;
        $article->status  = $request->status;
        $article->cat_id  = $request->cat_id;
        $article->thumb   = $request->thumb;

        $temp = $article->save();
        $temp || real()->exception('this.article.update.failed');
        $article = Article::with('category:id,name')->find($request->id);
        $result  = $article->toArray();
        return real($result)->success('article.update.success');
    }

    /**
     * 删除文章
     * --------------------------------------
     * @_group         article.manage.module
     * @_name          article.delete
     * @_method        GET
     * @_query         id
     * --------------------------------------
     * @param  Request $request
     * @return void
     */
    public function delete(Request $request)
    {
        $rule = ['id' => 'required|int'];
        $data = $request->all();
        real()->validator($data, $rule);

        $article = Article::find($request->id);
        $article || real()->exception('this.article.notexist');

        $article->delete();

        $article->trashed() || real()->exception('this.article.delete.fail.retry');

        return real()->success('this.article.delete.success');
    }

    /**
     * 文章基本验证
     * @param  Request $request
     * @param  array   $extend
     * @return void
     */
    private function baseValidator(Request $request, $extend = [])
    {
        $rule = [
            'title'   => 'required|max:256',
            'content' => 'required',
            'excerpt' => 'max:1200',
            'status'  => 'bool',
        ];

        $rule = array_merge($extend, $rule);

        $data = $request->all();
        return real()->validator($data, $rule);
    }

}
