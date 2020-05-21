<?php

namespace App\Packages\Utils\Postman;

use ReflectionClass;

class PostmanDocParse
{

    private static $class  = [];
    private static $route  = [];
    private static $doc    = [];
    private static $name   = '';
    private static $scope  = '';
    private static $config = [];
    private static $option = [];

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

        $option     = [];
        $class      = [];
        $uri        = [];
        $config_key = array_keys(self::$config);

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

            $controller       = $value->action['controller'];
            $temp             = explode('@', $controller);
            $class[]          = $temp[0];
            $uri[$controller] = $value->uri;

            foreach ($config_key as $c_value) {
                if (strstr($value->uri, $c_value)) {
                    $option[$value->uri] = $c_value;
                }
            }
        }

        $class = array_flip($class);
        $class = array_keys($class);

        self::$class  = $class;
        self::$route  = $uri;
        self::$option = $option;

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
                if (!strpos($doc, '@_')) {
                    continue;
                }
                $controller = $value . '@' . $method->name;

                if (!isset(self::$route[$controller])) {
                    continue;
                }

                $url = self::$route[$controller];

                $result[] = self::parseParam($doc, $url);
            }
        }

        self::$doc = $result;

        return new self();
    }

    /**
     * 解析文本 获取doc格式
     *
     * @param  array  $doc
     * @return void
     */
    private static function parseParam($doc, $route)
    {
        $sep = ["\t", "\n", "\r", "\r\n", "\r"];
        $doc = str_replace($sep, "\n", $doc);
        $doc = explode("\n", $doc);

        //清理不必要的注释文件
        array_walk($doc, function (&$value) {
            $value = trim($value);
            $value = strpos($value, '@_') ? trim($value) : '';
        });
        $doc = array_filter($doc);

        //将参数转换为数组
        $params    = [];
        $hasParams = [];
        foreach ($doc as $value) {
            $value               = trim(strstr($value, '@'));
            $_key                = trim(strstr($value, ' ', true));
            $_value              = trim(strstr($value, ' '));
            $_value && $params[] = [$_key, $_value];
            $hasParams[]         = $_key;
        }

        if (isset(self::$option[$route])) {
            $temp   = self::$option[$route];
            $config = self::$config[$temp];

            if (in_array('@_auth', $hasParams)) {
                unset($config['@_auth']);
            }

            foreach ($config as $c_key => $c_value) {
                $hasParams[] = $c_key;
                $params[]    = [$c_key, $c_value];
            }
        }

        $hasParams['@_url'] ?? $params[] = ['@_url', $route];

        $result = [];

        foreach ($params as $value) {
            $_key = substr($value[0], 2);

            switch ($_key) {
                case 'header':
                    $temp  = explode('|', $value[1]);
                    $__key = explode('=', trim($temp[0] ?? ''));

                    $desc                                = $__key[0];
                    isset($temp[1]) && $temp[1] && $desc = $temp[1];

                    $__value            = trim($__key[1] ?? '');
                    $__temp             = strstr($__value, ':');
                    $__temp && $__value = str_replace(':', '{{', $__value) . '}}';

                    $temp = [
                        'key'         => trim($__key[0]),
                        'description' => self::trans(trim($desc)),
                        'value'       => $__value,
                    ];

                    $result['request'][$_key][] = $temp;
                    break;

                case 'form':
                case 'form:file':
                    $temp  = explode('|', $value[1]);
                    $__key = explode('=', trim($temp[0] ?? ''));

                    $desc                                = $__key[0];
                    isset($temp[1]) && $temp[1] && $desc = $temp[1];

                    $__value            = trim($__key[1] ?? '');
                    $__temp             = strstr($__value, ':');
                    $__temp && $__value = str_replace(':', '{{', $__value) . '}}';
                    $__type             = $_key === 'form:file' ? 'file' : 'text';

                    $temp = [
                        'key'         => trim($__key[0]),
                        'description' => self::trans(trim($desc)),
                        'value'       => $__value,
                        'type'        => $__type,
                    ];

                    $result['request']['body']['mode']       = 'formdata';
                    $result['request']['body']['formdata'][] = $temp;
                    break;

                case 'url':
                    $temp = parse_url($value[1]);
                    $path = array_values(array_filter(explode('/', $temp['path'])));

                    $result['request']['url']['host'] = $temp['host'] ?? '{{host}}';
                    $result['request']['url']['path'] = $path;

                    $query = [];
                    isset($temp['query']) && parse_str($temp['query'], $query);

                    foreach ($query as $q_key => $q_value) {
                        $result['request']['url']['query'][] = [
                            'key'   => $q_key,
                            'value' => $q_value,
                        ];
                    }
                    break;

                case 'query':
                    $temp  = explode('|', $value[1]);
                    $__key = explode('=', trim($temp[0] ?? ''));

                    $__value            = trim($__key[1] ?? '');
                    $__temp             = strstr($__value, ':');
                    $__temp && $__value = str_replace(':', '{{', $__value) . '}}';
                    $__type             = $_key === 'form:file' ? 'file' : 'text';

                    $desc                                = $__key[0];
                    isset($temp[1]) && $temp[1] && $desc = $temp[1];

                    $temp = [
                        'key'         => $__key[0],
                        'description' => self::trans(trim($desc)),
                        'value'       => $__value,
                    ];

                    $result['request']['url']['query'][] = $temp;
                    break;

                case 'method':
                    $result['request']['method'] = strtoupper($value[1]);
                    break;

                case 'event':
                    $result['event'][] = self::postmanEvent($value[1]);
                    break;

                case 'auth':
                    $__key = explode('=', $value[1]);

                    $__type             = $__key[0];
                    $__value            = trim($__key[1] ?? '');
                    $__temp             = strstr($__value, ':');
                    $__temp && $__value = str_replace(':', '{{', $__value) . '}}';

                    $auth            = [];
                    $auth['type']    = $__type;
                    $auth[$__type][] = [
                        'key'   => 'token',
                        'value' => $__value,
                        'type'  => 'string',
                    ];

                    $result['request']['auth'] = $auth;
                    break;

                case 'name':
                case 'group':
                    $result[$_key] = self::trans($value[1]);
                    break;

                default:
                    $result[$_key] = $value[1];
                    break;
            }
        }

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
        $trans = 0;

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
            return $message;
        }

        return $format;

    }

    /**
     * 返回带分组的DOC
     *
     * @return object
     */
    public static function group()
    {
        $item = [];
        foreach (self::$doc as $value) {
            if (isset($value['group'])) {
                $group = $value['group'];
                unset($value['group']);
                $item[$group]['name']   = $group;
                $item[$group]['item'][] = $value;
            } else {
                $item[] = $value;
            }
        }

        self::$doc = array_values($item);
        return new self();
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
        return [
            'info' => [
                'name'   => self::$name,
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],

            'item' => self::$doc,
        ];
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
