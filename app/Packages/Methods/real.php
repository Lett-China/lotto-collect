<?php

/**
 * 通用接口返回快捷方法
 */
function real($result = [])
{
    $real = new App\Packages\Utils\Response();
    return $real->data($result);
}
