<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LottoModule\LottoUtils;

class LottoDataController extends Controller
{
    public function control(Request $request)
    {
        $item = LottoUtils::model()->makeVisible(['control'])->find($request->id);

        $item->control = $request->control;
        $item->save() || real()->exception('控制更新失败 请重试');
        return real()->success('成功更新控制值为：' . $request->control);
    }

    public function get(Request $request)
    {
        $items = LottoUtils::model()->with('betStats')
            ->with('betStats')->with('betLog.user:id,nickname,trial')
            ->find($request->id);

        $items->makeVisible(['control']);

        $result = $items->toArray();
        return real($result)->success();
    }

    public function index(Request $request)
    {
        $items = LottoUtils::model()->with('betStats');

        $request->status && $items->where('status', $request->status);
        $request->id && $items->where('id', $request->id);
        $request->win_code && $items->where('open_code', $request->win_code);
        $order = $request->status === '1' ? 'asc' : 'desc';
        $items->orderBy('id', $order);
        // $items->makeVisible(['control']);
        $paginate = $items->paginate(20);

        $paginate->data = $paginate->makeVisible(['control']);
        // dd($result);

        $result = $paginate->toArray();
        return real()->listPage($result)->success();
    }
}
