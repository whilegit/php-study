<?php 
use Whilegit\Utils\Misc;
use Whilegit\Utils\Comm;
use Whilegit\Utils\Trace;
use Whilegit\Utils\IString;
use Whilegit\Utils\Sms;
use Whilegit\Utils\Excel;
use Whilegit\Utils\Pinyin;

require_once "vendor/autoload.php";

Trace::out(Misc::is_windows());
$list = array(
		array('a'=>1, 'b'=>2),
		array('a'=>3, 'b'=>4),
);
$params = array(
		'columns'=>array(
				array('field'=>'a', 'width'=>'24', 'title'=>'项目1'),
				array('field'=>'b', 'width'=>'24', 'title'=>'项目2'),
		),
		'title' => '项目表'
);
//Excel::export($list, $params);

Misc::cookie(Misc::random(6), 'test cookies, might be timeout in 60 seconds', 60);

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
				'server' => "ssl://smtp.163.'com", //ssl://smtp.qq.com
				'port'   => 465,                  //465(ssl)或25(明文时,server字段不要加ssl://)
				'authmode' => ''                  //非qq或163时，authmode为空时将在server上加ssl://协议头
		),
		'username' => '6215714@163.com',
		'password' => '317507Ok',
		'signature' => '林忠仁', //签名(一般附在正文的最后)
		'sender' => '十方创客',
);

$ret = Comm::email($config, '6215714@qq.com', '十方通知', 'hello first email<br>');
echo '发送邮件: ' . var_export($ret, true) . '<br />';

$ret = Comm::get('http://www.sina.com.cn/');
if(isset($ret['success']) && $ret['success'] == false){
	echo 'Http访问测试:  .'.$ret['msg']."<br />\r\n";
}else{
	echo 'Http访问测试:  '.var_export($ret['responseline'], true)."<br />\r\n";
}

Trace::monolog('m.log');
Trace::log($config);

$str = "string\'a\"\\\'";
echo "IString::stripslashes_deep转义： 前[ {$str} ]  后 [ " . IString::stripslashes_deep($str) . " ]<br />\r\n";
$str = "<div class=\"right\">ba&laba\"ba</div>";
echo "IString::ihtmlspecialchars转义： 前<input type='text' value='{$str}'/>  后 <input type='text' value='" . str_replace('&', '＆', IString::ihtmlspecialchars($str)) . "' /><br />\r\n";

echo "Misc::referer: " . Misc::referer() . '，$_SERVER[\'HTTP_REFERER\']='.  $_SERVER['HTTP_REFERER'] . "<br />\r\n";
echo "Misc::token: " . Misc::token() . "<br />\r\n";

Sms::init('LTAIuK5a7gprq5rd','moMRNbrKlvSoqxI9uqQiLg94z0zJRr');
//Trace::out(Sms::send('林忠仁', 'SMS_86610162', '18968596872', array('code'=>'123456')));
//Trace::out(Sms::query('18968596872', date('Ymd'), 10, 1));

echo "'要好岙屌貂'的全拼是：" . Pinyin::get("要好岙屌貂") . "<br />\r\n";



?>
</body>
</html>
