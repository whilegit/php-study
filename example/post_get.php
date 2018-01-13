<?php
use Whilegit\Utils\Comm;

require_once __DIR__ . "/inc.php";
header("Content-type: text/html; charset=utf-8");

$ret = Comm::get('http://www.sina.com.cn/');
var_dump($ret); exit;
if(isset($ret['success']) && $ret['success'] == false){
    echo 'Http访问测试:  .'.$ret['msg']."<br />\r\n";
}else{
    echo 'Http访问测试:  '.var_export($ret['responseline'], true)."<br />\r\n";
}