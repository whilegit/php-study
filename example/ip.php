<?php
use Whilegit\Utils\Misc;

require_once __DIR__ . "/inc.php";
header("Content-type: text/html; charset=utf-8");

$misc = new Misc();

echo '客户机的ip地址：' . $misc->real_ip() . '<br />';