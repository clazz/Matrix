<?php

require_once __DIR__.'/../Matrix.php';

if (!function_exists('dump')){
    function dump($var){
        call_user_func_array('var_dump', func_get_args());
    }
}

// 测试数据的大小
define('TEST_MAX_ARRAY_SIZE', 10);
define('TEST_HALF_MAX_ARRAY_SIZE', ~~(TEST_MAX_ARRAY_SIZE / 2));
