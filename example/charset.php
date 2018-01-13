<?php
require_once __DIR__ . "/inc.php";
use Whilegit\Utils\Charset;
use Whilegit\Utils\Trace;

$str = Charset::unicode2utf8('\uffe5', true);
//Trace::out(unpack('C*', $str));
Trace::out($str);