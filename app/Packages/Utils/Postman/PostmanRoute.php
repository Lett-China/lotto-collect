<?php
namespace App\Packages\Utils\Postman;

use Illuminate\Support\Facades\Route;

class PostmanRoute
{
    public static function register()
    {
        $controller = 'App\Packages\Utils\Postman\PostmanController@collection';
        Route::middleware('api')->get('api/utils/postman', $controller);
    }
}
