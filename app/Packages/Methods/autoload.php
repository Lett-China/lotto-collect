<?php

$dirname  = dirname(__FILE__);
$filename = scandir($dirname);

foreach ($filename as $file) {
    if (in_array($file, ['.', '..'])) {
        continue;
    }
    $file = $dirname . '/' . $file;

    include_once $file;
}
