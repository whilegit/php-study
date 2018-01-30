<?php
use Whilegit\Utils\Comm;

require_once __DIR__ . "/inc.php";
header("Content-type: text/html; charset=utf-8");

$ret = Comm::get('http://www.sina.com.csn/');
//var_dump($ret); exit;
if(!Comm::is_error($ret)){
    echo 'Http访问测试:  '.$ret['responseline']."<br />\r\n";
}else{
    echo 'Http访问测试: 错误。原因：  '.var_export($ret['msg'], true)."<br />\r\n";
}