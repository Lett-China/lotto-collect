<?php

/**
 * 清理前后空格、回车等
 */
function trimAll($str)
{
    $sep = [' ', '　', "\t", "\n", "\r"];
    return str_replace($sep, '', $str);
}
