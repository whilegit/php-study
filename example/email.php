<?php
use Whilegit\Utils\Comm;
require_once __DIR__ . "/inc.php";
header("Content-type: text/html; charset=utf-8");
$config = array(
    'smtp' => array(
        'type'   => '163',				  //qq
        'server' => "ssl://smtp.163.'com", //ssl://smtp.qq.com
        'port'   => 465,                  //465(ssl)或25(明文时,server字段不要加ssl://)
        'authmode' => ''                  //非qq或163时，authmode为空时将在server上加ssl://协议头
    ),
    'username' => '6215714@163.com',
    'password' => '317507Ok',
    'signature' => '林忠仁', //签名(一般附在正文的最后)
    'sender' => '微动云商',
);
$ret = Comm::email($config, '6215714@qq.com', 'PHPMailer发送邮件测试', 'hello first email<br>');
echo '发送邮件: ' . var_export($ret, true) . '<br />';