<?php
namespace App\Packages\Utils\Postman;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Packages\Utils\Postman\PostmanDocParse;

class PostmanController extends Controller
{
    /**
     * Postman wiki
     * --------------------------------------
     * @_name       Postmain Collection
     * @_method     GET
     * @_query      info
     * @_query      scope
     * @_query      name
     * --------------------------------------
     * @param  Request $request
     * @return void
     */
    public function collection(Request $request)
    {
        $config = [
            'api/client' => ['@_auth' => 'bearer=:access_token'],
            'api/admin'  => ['@_auth' => 'bearer=:admin_token'],
        ];

        $info = $request->info ?? config('app.name');
        $doc  = PostmanDocParse::name($info)->config($config);

        $request->scope && $doc->scope($request->scope, $request->name);
        $result = $doc->get()->group()->toArray();

        return response()->json($result, 200);
    }
}
