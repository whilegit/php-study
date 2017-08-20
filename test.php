<?php 
use Utils\Misc;
use Utils\Communication;
use Utils\Trace;
require_once "vendor/autoload.php";


?>
<html>
<head><meta charset="utf-8"><title>测试</title></head>
<body>
<?php


$misc = new Misc();

echo '客户机的ip地址：' . $misc->real_ip() . '<br />';
echo '随机字符串: '. $misc->random(16) . '<br />';

$config = array(
		'smtp' => array(
				'type'   => '163',				  //qq
				'server' => 'ssl://smtp.163.com', //ssl://smtp.qq.com
				'port'   => 465,                  //465(ssl)或25(明文时,server字段不要加ssl://)
				'authmode' => ''                  //非qq或163时，authmode为空时将在server上加ssl://协议头
		),
		'username' => '6215714@163.com',
		'password' => '317507Ok',
		'signature' => '林忠仁', //签名(一般附在正文的最后)
		'sender' => '十方创客',
);

//$ret = Communication::ihttp_email($config, '6215714@qq.com', '十方通知', 'hello first email<br>');
//echo '发送邮件: ' . var_export($ret, true) . '<br />';

$ret = Communication::ihttp_get('http://www.sina.com.cn/');
if(isset($ret['success']) && $ret['success'] == false){
	echo 'Http访问测试:  .'.$ret['msg'].'.<br />';
}else{
	echo 'Http访问测试:  '.var_export($ret['responseline'], true).'.<br />';
}
Trace::monolog('m.log');
Trace::log($config);
Trace::out($config);
?>
</body>
</html>