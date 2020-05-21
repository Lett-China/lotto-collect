<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ArticleCategory as Category;

class ArticleCategoryController extends Controller
{

    public function index()
    {
        $items  = $this->getCategories();
        $result = ['items' => $items];

        return real($result)->success();
    }

    /**
     *  创建文章分类
     * --------------------------------------
     * @_group         article.manage.module
     * @_name          category.create
     * @_method        POST
     * @_form          name|category.name
     * @_form          desc|category.desc
     * @_form          parent|parent.id
     * --------------------------------------
     * @param  Request $request
     * @return void
     */
    public function create(Request $request)
    {
        $rule = [
            'name'   => 'required|max:16',
            'parent' => 'int',
            'desc'   => 'max:64',
        ];
        $data = $request->all();
        real()->validator($data, $rule);

        if ($request->parent) {
            $parent = Category::find($request->parent);
            $parent || real()->exception('parent.category.notexist');
        }

        $data = [
            'name'   => $request->name,
            'parent' => $request->parent ?? 0,
            'desc'   => $request->desc ?? '',
        ];

        $create = Category::create($data);
        $create || real()->exception('category.create.failed');

        $items  = $this->getCategories();
        $result = ['items' => $items];
        return real($result)->success('category.create.success');
    }

    /**
     *  更新文章分类
     * --------------------------------------
     * @_group         article.manage.module
     * @_name          category.update
     * @_method        POST
     * @_form          id|category.id
     * @_form          name|category.name
     * @_form          desc|category.desc
     * @_form          parent|parent.id
     * --------------------------------------
     * @param  Request $request
     * @return void
     */
    public function update(Request $request)
    {
        $rule = [
            'id'   => 'required',
            'name' => 'required|max:16',
        ];
        $data = $request->all();
        real()->validator($data, $rule);

        if ($request->parent) {
            $parent = Category::find($request->parent);
            $parent || real()->exception('parent.category.notexist');
        }

        $cat = Category::find($request->id);
        $cat || real()->exception('category.notexist');

        $cat->name   = $request->name;
        $cat->parent = $request->parent ?? 0;
        $cat->desc   = $request->desc ?? '';

        $temp = $cat->save();
        $temp || real()->exception('category.update.faild.retry');

        $items  = $this->getCategories();
        $result = ['items' => $items];
        return real($result)->success('category.update.success');
    }

    /**
     * 删除文章分类
     * --------------------------------------
     * @_group         article.manage.module
     * @_name          category.delete
     * @_method        GET
     * @_query         id|category.id
     * --------------------------------------
     * @param  Request $request
     * @return void
     */
    public function delete(Request $request)
    {
        $rule = ['id' => 'required'];
        $data = $request->all();
        real()->validator($data, $rule);

        $cat = Category::withCount('article')->find($request->id);
        $cat || real()->exception('category.notexist');
        $cat->article_count > 0 && real()->exception('category.article.count.notzero');

        $cat->delete();
        $cat->trashed() || real()->exception('category.delete.fail.retry');

        $items  = $this->getCategories();
        $result = ['items' => $items];
        return real($result)->success('category.delete.success');
    }

    public function getCategories()
    {
        $cat = Category::withCount('article');
        return $cat->orderBy('id', 'desc')->get()->toArray();

    }

}
