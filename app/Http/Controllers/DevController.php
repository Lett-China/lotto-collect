<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LottoModule\LottoWinPlace;

class DevController extends Controller
{
    public function _index(Request $request)
    {
        $code = '07,12,14,17,18,21,28,32,34,41,43,45,49,51,52,57,58,59,61,71';
        $a    = LottoWinPlace::lotto28($code, 'cw28');

        dump($a);
    }
}
