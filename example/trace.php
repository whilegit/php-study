<?php
require_once __DIR__ . '/inc.php';
header("Content-type: text/html; charset=utf-8");

use Whilegit\Utils\Trace;
Trace::monolog('trace.log');
Trace::set_error_handler();
Trace::set_exception_handler();
//Trace::log('Log');
//Trace::out();
