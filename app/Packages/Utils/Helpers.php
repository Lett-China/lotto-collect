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

/**
 * 获取中英文首字母
 *
 * @param  string $str
 * @return void
 */
function getInitial($str = '')
{
    $result = '';

    if (preg_match('/^[0-9a-zA-z].*/', $str)) {
        return strtoupper(substr($str, 0, 1));
    }

    $str1 = iconv('UTF-8', 'gb2312', $str);
    $str2 = iconv('gb2312', 'UTF-8', $str1);
    $temp = $str2 === $str ? $str1 : $str;
    $asc  = ord($temp{0}) * 256 + ord($temp{1}) - 65536;

    ($asc >= -20319 && $asc <= -20284) && $result = 'A';
    ($asc >= -20283 && $asc <= -19776) && $result = 'B';
    ($asc >= -19775 && $asc <= -19219) && $result = 'C';
    ($asc >= -19218 && $asc <= -18711) && $result = 'D';
    ($asc >= -18710 && $asc <= -18527) && $result = 'E';
    ($asc >= -18526 && $asc <= -18240) && $result = 'F';
    ($asc >= -18239 && $asc <= -17923) && $result = 'G';
    ($asc >= -17922 && $asc <= -17418) && $result = 'H';
    ($asc >= -17417 && $asc <= -16475) && $result = 'J';
    ($asc >= -16474 && $asc <= -16213) && $result = 'K';
    ($asc >= -16212 && $asc <= -15641) && $result = 'L';
    ($asc >= -15640 && $asc <= -15166) && $result = 'M';
    ($asc >= -15165 && $asc <= -14923) && $result = 'N';
    ($asc >= -14922 && $asc <= -14915) && $result = 'O';
    ($asc >= -14914 && $asc <= -14631) && $result = 'P';
    ($asc >= -14630 && $asc <= -14150) && $result = 'Q';
    ($asc >= -14149 && $asc <= -14091) && $result = 'R';
    ($asc >= -14090 && $asc <= -13319) && $result = 'S';
    ($asc >= -13318 && $asc <= -12839) && $result = 'T';
    ($asc >= -12838 && $asc <= -12557) && $result = 'W';
    ($asc >= -12556 && $asc <= -11848) && $result = 'X';
    ($asc >= -11847 && $asc <= -11056) && $result = 'Y';
    ($asc >= -11055 && $asc <= -10247) && $result = 'Z';
    return strtoupper($result);
}

function object_to_array($data)
{
    if (is_object($data)) {
        $data = get_object_vars($data);
    }
    if (is_array($data)) {
        return array_map(__FUNCTION__, $data);
    } else {
        return $data;
    }
}

function format_html($pee, $br = true)
{
    $pre_tags = [];
    if (trim($pee) === '') {return '';}
    $pee = $pee . "\n";
    if (strpos($pee, '<pre') !== false) {
        $pee_parts = explode('</pre>', $pee);
        $last_pee  = array_pop($pee_parts);
        $pee       = '';
        $i         = 0;

        foreach ($pee_parts as $pee_part) {
            $start = strpos($pee_part, '<pre');

            if ($start === false) {
                $pee .= $pee_part;
                continue;
            }

            $name            = "<pre wp-pre-tag-$i></pre>";
            $pre_tags[$name] = substr($pee_part, $start) . '</pre>';

            $pee .= substr($pee_part, 0, $start) . $name;
            $i++;
        }

        $pee .= $last_pee;
    }
    $pee = preg_replace('|<br\s*/?>\s*<br\s*/?>|', "\n\n", $pee);

    $all_blocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';

    $pee = preg_replace('!(<' . $all_blocks . '[\s/>])!', "\n$1", $pee);
    $pee = preg_replace('!(</' . $all_blocks . '>)!', "$1\n\n", $pee);
    $pee = str_replace(["\r\n", "\r"], "\n", $pee);
    if (strpos($pee, '<option') !== false) {
        $pee = preg_replace('|\s*<option|', '<option', $pee);
        $pee = preg_replace('|</option>\s*|', '</option>', $pee);
    }

    if (strpos($pee, '</object>') !== false) {
        $pee = preg_replace('|(<object[^>]*>)\s*|', '$1', $pee);
        $pee = preg_replace('|\s*</object>|', '</object>', $pee);
        $pee = preg_replace('%\s*(</?(?:param|embed)[^>]*>)\s*%', '$1', $pee);
    }

    if (strpos($pee, '<source') !== false || strpos($pee, '<track') !== false) {
        $pee = preg_replace('%([<\[](?:audio|video)[^>\]]*[>\]])\s*%', '$1', $pee);
        $pee = preg_replace('%\s*([<\[]/(?:audio|video)[>\]])%', '$1', $pee);
        $pee = preg_replace('%\s*(<(?:source|track)[^>]*>)\s*%', '$1', $pee);
    }

    $pee  = preg_replace("/\n\n+/", "\n\n", $pee);
    $pees = preg_split('/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY);
    $pee  = '';

    foreach ($pees as $tinkle) {
        $pee .= '<p>' . trim($tinkle, "\n") . "</p>\n";
    }

    $pee = preg_replace('|<p>\s*</p>|', '', $pee);
    $pee = preg_replace('!<p>([^<]+)</(div|address|form)>!', '<p>$1</p></$2>', $pee);
    $pee = preg_replace('!<p>\s*(</?' . $all_blocks . '[^>]*>)\s*</p>!', '$1', $pee);
    $pee = preg_replace('|<p>(<li.+?)</p>|', '$1', $pee);
    $pee = preg_replace('|<p><blockquote([^>]*)>|i', '<blockquote$1><p>', $pee);
    $pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
    $pee = preg_replace('!<p>\s*(</?' . $all_blocks . '[^>]*>)!', '$1', $pee);
    $pee = preg_replace('!(</?' . $all_blocks . '[^>]*>)\s*</p>!', '$1', $pee);
    if ($br) {
        $pee = str_replace(['<br>', '<br/>'], '<br />', $pee);
        $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee);
        $pee = str_replace('<WPPreserveNewline />', "\n", $pee);
    }
    $pee = preg_replace('!(</?' . $all_blocks . '[^>]*>)\s*<br />!', '$1', $pee);
    $pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee);
    $pee = preg_replace("|\n</p>$|", '</p>', $pee);
    if (!empty($pre_tags)) {
        $pee = str_replace(array_keys($pre_tags), array_values($pre_tags), $pee);
    }
    return $pee;
}

function num_to_rmb($num)
{
    $c1 = '零壹贰叁肆伍陆柒捌玖';
    $c2 = '分角元拾佰仟万拾佰仟亿';
    //精确到分后面就不要了，所以只留两个小数位
    $num = round($num, 2);
    //将数字转化为整数
    $num = $num * 100;
    if (strlen($num) > 10) {
        return '金额太大，请检查';
    }
    $i = 0;
    $c = '';
    while (1) {
        if ($i == 0) {
            //获取最后一位数字
            $n = substr($num, strlen($num) - 1, 1);
        } else {
            $n = $num % 10;
        }
        //每次将最后一位数字转化为中文
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i = $i + 1;
        //去掉数字最后一位了
        $num = $num / 10;
        $num = (int) $num;
        //结束循环
        if ($num == 0) {
            break;
        }
    }
    $j   = 0;
    $len = strlen($c);
    while ($j < $len) {
        //utf8一个汉字相当3个字符
        $m = substr($c, $j, 6);
        //处理数字中很多0的情况,每次循环去掉一个汉字“零”
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left  = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c     = $left . $right;
            $j     = $j - 3;
            $len   = $len - 3;
        }
        $j = $j + 3;
    }
    //这个是为了去掉类似23.0中最后一个“零”字
    if (substr($c, strlen($c) - 3, 3) == '零') {
        $c = substr($c, 0, strlen($c) - 3);
    }
    //将处理的汉字加上“整”
    if (empty($c)) {
        return '零元整';
    } else {
        return $c . '整';
    }
}
