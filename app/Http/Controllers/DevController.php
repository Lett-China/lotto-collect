<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LottoModule\Models\OpenControl;

class DevController extends Controller
{
    public function _index(Request $request)
    {
        $model = new OpenControl();
        $model->createData($request->count);
        //$a = $model->collect77();

        //dump($a);

    }
}
