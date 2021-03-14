<?php

date_default_timezone_set('America/Vancouver');
$datetime = 'Mar 14, 2021 03:11:00 AM';
$time     = strtotime($datetime);
// var_dump(substr('03:11:00 AM', 0, 2));
// var_dump(ceil(date('j', $time) / 7));
// var_dump(date('H', $time));
//每年3月的第二个星期天，加拿大时间在3点 ，时间偏移1小时
$time_offset = 0;
if (date('m', $time) === '03' && ceil(date('j', $time) / 7) == 2 && date('w', $time) === '0' && substr('03:11:00 AM', 0, 2) === '03') {
    if (date('H', $time) == '03') {
        echo 'bbbb';
        $time += 3600;
    }
}

var_dump($time_offset);
// var_dump(date('w', $time), $datetime);
date_default_timezone_set('Asia/Shanghai');
$official_at = date('Y-m-d H:i:s', $time);

var_dump($official_at);
