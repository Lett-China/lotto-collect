<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LottoModule\Models\LottoBeiJing8;

class DevController extends Controller
{
    public function _index(Request $request)
    {
        $model = new LottoBeiJing8();

        $a = $model->collect77();

        dump($a);

    }
}
