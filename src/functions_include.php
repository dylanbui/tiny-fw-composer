<?php

// Don't redefine the functions if included multiple times.
//if (!function_exists('GuzzleHttp\Psr7\str')) {
//    require __DIR__ . '/functions.php';
//}


if (!function_exists('tinyfw_now_to_mysql')) {
    function tinyfw_now_to_mysql()
    {
        return date('Y-m-d H:i:s');
    }
}
