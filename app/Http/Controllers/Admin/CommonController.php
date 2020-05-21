<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommonController extends Controller
{
    public function imageCreate(Request $request)
    {
        $rule = ['file' => 'required'];
        $data = $request->all();
        real()->validator($data, $rule);

        $file  = $request->file;
        $image = \Intervention\Image\Facades\Image::make($file);
        if ($request->width && $request->height) {
            $image->fit($request->width, $request->height);
        }

        $suffix = $request->suffix ?: 'jpg';
        $path   = $request->path ?: '/upload';
        $stream = $image->stream($suffix, 100)->__toString();
        $name   = md5($stream) . '.' . $suffix;
        $path   = $path . '/' . $name;
        $system = \Storage::disk();
        $system->put($path, $stream);

        $result = [
            'url'  => $system->url($path . '?x-oss-process=style/max'),
            'path' => $path,
            'name' => $name,
            'host' => $system->url('/'),
        ];

        return real($result)->success();
    }

}
