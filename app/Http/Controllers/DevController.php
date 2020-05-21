<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ModelTrait\ThisUserTrait;
use App\Models\LottoModule\Models\LottoBit28;
use App\Models\LottoModule\Models\LottoKenoCa;

class DevController extends Controller
{
    use ThisUserTrait;

    public function _index(Request $request)
    {
        $model = new LottoKenoCa();
        $a     = $model->officialCheck();

        dd($a);
    }
}
