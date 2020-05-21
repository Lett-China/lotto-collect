<?php
namespace App\Http\Controllers\Client;

use App\Models\SinglePage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SinglePageController extends Controller
{
    public function index(Request $request)
    {
        $data = SinglePage::query();
        $data->where('type', $request->type);
        $data->remember(600);
        $result          = [];
        $result['items'] = $data->ordered()->get()->toArray();
        return real($result)->cache(600)->success();
    }
}
