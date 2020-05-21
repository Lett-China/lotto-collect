<?php
namespace App\Packages\Utils;

use Illuminate\Support\Facades\Validator;

/**
 * 公共返回类
 */
class Response
{
    private static $cache = 0;

    private static $code = '';

    private static $data = [];

    private static $debug = [];

    private static $message = '';

    /**
     * code/message/debug/cache/data
     * @param  [type] $name
     * @param  [type] $arguments
     * @return void
     */
    public function __call($name, $arguments)
    {
        $param = $arguments[0];
        if ($name === 'data') {
            $param = (array) $param;
        }
        self::${$name} = $param;
        return new self();
    }

    /**
     * code/message/debug/cache/data
     * @param  [type] $name
     * @param  [type] $arguments
     * @return void
     */
    public static function __callStatic($name, $arguments)
    {
        $param = $arguments[0];
        if ($name === 'data') {
            $param = (array) $param;
        }
        self::${$name} = $param;
        return new self();
    }

    /**
     * 错误结果返回
     *
     * @param  string $message
     * @return void
     */
    public static function error($message = '', $exception = false)
    {
        self::$code || self::$code = 400;

        $message       = $message ? $message : self::$code;
        self::$message = self::trans($message);

        return self::response($exception);
    }

    /**
     * 通过异常方式直接返回
     *
     * @param  string $message
     * @return void
     */
    public static function exception($message = '')
    {
        return self::error($message, true);
    }

    /**
     * 将laravel 默认分页数据转换格式
     *
     * @param  array  $data
     * @return void
     */
    public static function listPage($data = [])
    {
        self::$data['items'] = $data['data'];
        self::$data['page']  = [
            'total'   => (int) $data['total'],
            'current' => (int) $data['current_page'],
            'limit'   => (int) $data['per_page'],
            'last'    => (int) $data['last_page'],
        ];
        return new self();
    }

    /**
     * 返回最终结果
     *
     * @param  bool   $exception
     * @return void
     */
    public static function response($exception = false)
    {
        $param = [
            'message' => self::$message,
            'data'    => self::$data,
        ];

        isset(self::$data['page']) && $page = self::$data['page'];

        $parse = self::valueToString($param);

        isset($page) && $parse['data']['page'] = $page;

        $result = [
            'code'    => self::$code,
            'message' => $parse['message'],
            'data'    => $parse['data'],
            'cache'   => self::$cache,
        ];

        self::$debug && $result['debug'] = self::$debug;

        $result = array_filter($result);

        if ($exception === true) {
            $message = json_encode($result, JSON_UNESCAPED_UNICODE);
            throw new \Exception($message, 900);
        }

        return $result;
    }

    /**
     * 正常结果返回
     *
     * @param  string $message
     * @return void
     */
    public static function success($message = '')
    {
        self::$code || self::$code = 200;
        $message || $message       = self::$code;

        self::$message = self::trans($message);

        return self::response();
    }

    /**
     * 使用laravel validator ，如果校验不通过，直接使用异常方式返回结果
     * laravel validator 相关文档
     * https://learnku.com/docs/laravel/5.8/validation/3899
     *
     * @param  array  $data
     * @param  array  $rule
     * @param  array  $message
     * @param  array  $attr
     * @return void
     */
    public static function validator($data = [], $rule = [], $message = [], $attr = [])
    {
        array_walk($rule, function (&$value) {
            strstr($value, 'bail') || $value = 'bail|' . $value;
        });

        $validator = Validator::make($data, $rule, $message, $attr);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return self::exception($error);
        }

        return true;
    }

    /**
     * 转换位对象
     *
     * @param  [type] $mixture
     * @param  int    $depth
     * @return void
     */
    private static function toObject($mixture = null, $depth = 0)
    {
        $func = false;
        $func = function ($mixture, $depth, $depth_count) use (&$func) {
            $obj = new \stdClass();
            if (is_object($mixture)) {
                $mixture = get_object_vars($mixture);
            }
            if (is_array($mixture)) {
                foreach ($mixture as $key => $value) {
                    if (is_array($value) && ($depth === 0 || $depth !== 0 && $depth_count < $depth)) {
                        $value = $func($value, $depth, $depth_count + 1);
                    }
                    $obj->$key = $value;
                }
            }
            return $obj;
        };
        return $func($mixture, $depth, 1);
    }

    /**
     * 转换提示语
     * @param  string $message
     * @return void
     */
    private static function trans($message = '')
    {
        $key  = 'response.' . $message;
        $lang = trans($key);

        if ($lang !== $key) {
            return $lang;
        }

        $lang   = trans('response');
        $params = explode('.', $message);
        $format = $lang['format'];
        $trans  = 0;

        for ($i = 1; $i <= 5; $i++) {
            $value = $params[$i - 1] ?? '';
            $temp  = $lang[$value] ?? $value;
            $mark  = ':' . $i;

            if (!isset($lang[$value]) && isset($params[$i - 1])) {
                $trans++;
            }

            $format = str_replace($mark, $temp, $format);
        }

        if ($trans === count($params)) {
            $format = $message;
        }

        return $format;
    }

    /**
     * 所有数据转换为字符串
     *
     * @param  [type] $mixture
     * @param  array  $ext_params
     * @return void
     */
    private static function valueToString($mixture, $ext_params = [])
    {
        // isset($ext_params['object_depth']) || $ext_params['object_depth'] = 1;
        // if (is_object($mixture)) {
        //     ksort($mixture);
        //     $mixture = self::toObject($mixture, $ext_params['object_depth']);
        //     foreach ($mixture as $key => $value) {
        //         $mixture->$key = self::valueToString($value, $ext_params);
        //     }
        // } elseif (is_array($mixture)) {
        //     ksort($mixture);
        //     foreach ($mixture as $key => $value) {
        //         $mixture[$key] = self::valueToString($value, $ext_params);
        //     }
        // } elseif (is_bool($mixture)) {
        //     $mixture = strval(intval($mixture));
        // } elseif (!is_string($mixture)) {
        //     $mixture = strval($mixture);
        // }
        return $mixture;
    }
}
