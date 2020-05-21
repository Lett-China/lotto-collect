<?php

/**
 * 返回API json 数组
 *
 * @param  array    $result
 * @return object
 */
function real($result = [])
{
    $real = App\Packages\Utils\Response::class;
    return $real::data($result);
}

/**
 * 清理所有空格、换行符
 *
 * @param  string   $str
 * @return string
 */
function trimAll($str)
{
    $sep = [' ', '　', "\t", "\n", "\r"];
    return str_replace($sep, '', $str);
}
