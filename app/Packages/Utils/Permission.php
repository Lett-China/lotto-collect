<?php

namespace App\Packages\Utils;

use ReflectionClass;

class Permission
{

    private static $class  = [];
    private static $doc    = [];
    private static $name   = '';
    private static $scope  = '';
    private static $config = [];

    /**
     * 获取指定目录下的所有 controller
     *
     * @param  array  $dir
     * @return void
     */

    private static function getControllers()
    {
        $app    = app();
        $routes = $app->routes->getRoutes();
        $class  = [];

        foreach ($routes as $value) {
            if (!isset($value->action['controller'])) {
                continue;
            }

            if (strstr($value->uri, 'telescope')) {
                continue;
            }

            if (self::$scope && !strstr($value->uri, self::$scope)) {
                continue;
            }

            $controller = $value->action['controller'];
            $temp       = explode('@', $controller);
            $class[]    = $temp[0];
        }

        $class = array_flip($class);
        $class = array_keys($class);

        self::$class = $class;

        // dd($class);

        return new self();
    }

    /**
     * 根据controller解析Doc
     *
     * @param  array  $class
     * @return void
     */
    public static function getDocComments()
    {
        $class  = self::$class;
        $result = [];
        foreach ($class as $value) {
            $_class  = new ReflectionClass($value);
            $methods = $_class->getMethods();
            foreach ($methods as $method) {
                $doc = $method->getDocComment();
                if (!strpos($doc, '@_role')) {
                    continue;
                }
                $controller = $value . '@' . $method->name;

                $temp     = ['controller' => $controller];
                $result[] = array_merge($temp, self::parseParam($doc));
            }
        }

        dd($result);

        self::$doc = $result;

        return new self();
    }

    /**
     * 解析文本 获取doc格式
     *
     * @param  array  $doc
     * @return void
     */
    private static function parseParam($doc, $route = '')
    {
        $sep = ["\t", "\n", "\r", "\r\n", "\r"];
        $doc = str_replace($sep, "\n", $doc);
        $doc = explode("\n", $doc);

        //清理不必要的注释文件
        array_walk($doc, function (&$value) {
            $value = trim($value);
            $value = strpos($value, '@_role') ? trim($value) : '';
        });
        $doc = array_filter($doc);

        //将参数转换为数组
        $params = [];
        foreach ($doc as $value) {
            $value               = trim(strstr($value, '@'));
            $_key                = trim(strstr($value, ' ', true));
            $_value              = trim(strstr($value, ' '));
            $_value && $params[] = $_value;
        }

        dump($params);

        $result = [];

        foreach ($params as $value) {
            $temp   = explode('|', $value);
            $result = [
                'permission' => [
                    'name'  => $temp[0],
                    'trans' => self::trans($temp[0]),
                ],
                'group'      => [
                    'name'  => $temp[1],
                    'trans' => self::trans($temp[1]),
                ],
            ];
        }

        // $result = $temp;
        return $result;
    }

    /**
     * 转换提示语
     *
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

        $lang    = trans('response');
        $params  = explode('.', $message);
        $format  = $lang['format'];
        $transed = 0;

        for ($i = 1; $i <= 5; $i++) {
            $value = $params[$i - 1] ?? '';
            $temp  = $lang[$value] ?? $value;
            $mark  = ':' . $i;

            if (!isset($lang[$value]) && isset($params[$i - 1])) {
                $transed++;
            }

            $format = str_replace($mark, $temp, $format);
        }

        if ($transed === count($params)) {
            return $message;
        }

        return $format;

    }

    /**
     * 返回不带分组的wiki
     *
     * @return void
     */
    public static function get()
    {
        self::getControllers()->getDocComments();
        return new self();
    }

    public static function name($name = 'API.Docs')
    {
        self::$name = $name;
        return new self();
    }
    public static function config($config = [])
    {
        self::$config = $config;
        return new self();
    }

    public static function scope($scope, $name = '')
    {
        $name = $name ?? strtoupper($scope);
        self::$name .= '-';
        self::$name .= $name;
        self::$scope = $scope;
        return new self();
    }

    public static function toArray()
    {
        return self::$doc;
    }

    /**
     * PostMan event 预设
     *
     * @param  string  $key
     * @return array
     */
    private static function postmanEvent($param)
    {

        $temp                       = explode('|', $param);
        $key                        = $temp[0];
        isset($temp[1]) || $temp[1] = 'access_token';

        $event = [
            'set_token' => [
                'listen' => 'test',
                'script' => [
                    'type' => 'text/javascript',
                    'exec' => [
                        'var jsonData = pm.response.json();',
                        'pm.environment.set("' . $temp[1] . '", jsonData.data.access_token);',
                    ],
                ],
            ],
        ];

        return $event[$key];
    }

}
