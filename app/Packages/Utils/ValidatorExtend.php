<?php
namespace App\Packages\Utils;

use Illuminate\Support\Facades\Validator;

class ValidatorExtend extends Validator
{
    public static function boot()
    {
        $control = 'App\Packages\Utils\ValidatorExtend';
        Validator::extend('password', $control . '@password');
        Validator::extend('username', $control . '@username');
        Validator::extend('mobile', $control . '@mobile');
        Validator::extend('currency', $control . '@currency');
        Validator::extend('real_name', $control . '@realName');
    }

    public function currency(...$param)
    {
        $regexp = '/^[0-9]+(.[0-9]{1,3})?$/';
        return preg_match($regexp, $param[1]);
    }

    public function mobile(...$param)
    {
        $regexp = '/^1[3578][0-9]{9}$/';
        // if (preg_match('/^13[012][0-9]{8}$/', $param[1])) {
        //     return false;
        // }
        return preg_match($regexp, $param[1]);
    }

    public function password(...$param)
    {
        $regexp = '/^[a-zA-Z][a-zA-Z0-9_]{1,}$/';
        return preg_match($regexp, $param[1]);
    }

    public function realName(...$param)
    {
        $str = $param[1];
        //新疆等少数民族可能有·
        if (strpos($str, '·')) {
            $str = str_replace('·', '', $str);
        }

        $regexp = '/^[\x7f-\xff]+$/';
        return preg_match($regexp, $str);
    }

    public function username(...$param)
    {
        $regexp = '/^[a-zA-Z][a-zA-Z0-9_]{1,}$/';
        return preg_match($regexp, $param[1]);
    }
}
