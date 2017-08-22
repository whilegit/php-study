<?php 
use Utils\Misc;
use Utils\Comm;
use Utils\Trace;
use Utils\IString;
use Utils\Sms;
require_once "vendor/autoload.php";

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
//Trace::out($config);

$str = "string\'a\"\\\'";
echo "IString::stripslashes_deep转义： 前[ {$str} ]  后 [ " . IString::stripslashes_deep($str) . " ]<br />\r\n";
$str = "<div class=\"right\">ba&laba\"ba</div>";
echo "IString::ihtmlspecialchars转义： 前<input type='text' value='{$str}'/>  后 <input type='text' value='" . str_replace('&', '＆', IString::ihtmlspecialchars($str)) . "' /><br />\r\n";

echo "Misc::referer: " . Misc::referer() . '，$_SERVER[\'HTTP_REFERER\']='.  $_SERVER['HTTP_REFERER'] . "<br />\r\n";
echo "Misc::token: " . Misc::token() . "<br />\r\n";

//Sms::send();
Sms::init('LTAIuK5a7gprq5rd','moMRNbrKlvSoqxI9uqQiLg94z0zJRr');
//Trace::out(Sms::senda());
//Trace::out(Sms::send('林忠仁', 'SMS_86610162', '18968596872', array('code'=>'123456')));

//Trace::out(Sms::query('18968596872', date('Ymd'), 10, 1));

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
/** Include PHPExcel */

// Create new PHPExcel object
echo date('H:i:s') , " Create new PHPExcel object" , EOL;
$objPHPExcel = new PHPExcel();
// Set document properties
echo date('H:i:s') , " Set document properties" , EOL;
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
->setLastModifiedBy("Maarten Balliauw")
->setTitle("PHPExcel Test Document")
->setSubject("PHPExcel Test Document")
->setDescription("Test document for PHPExcel, generated using PHP classes.")
->setKeywords("office PHPExcel php")
->setCategory("Test result file");
// Add some data
echo date('H:i:s') , " Add some data" , EOL;
$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('A1', 'Hello')
->setCellValue('B2', 'world!')
->setCellValue('C1', 'Hello')
->setCellValue('D2', 'world!');
// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('A4', 'Miscellaneous glyphs')
->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');
// Rename worksheet
echo date('H:i:s') , " Rename worksheet" , EOL;
$objPHPExcel->getActiveSheet()->setTitle('Simple');
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);
// Save Excel 2007 file
echo date('H:i:s') , " Write to Excel2007 format" , EOL;
$callStartTime = microtime(true);
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
$callEndTime = microtime(true);
$callTime = $callEndTime - $callStartTime;
echo date('H:i:s') , " File written to " , str_replace('.php', '.xlsx', pathinfo(__FILE__, PATHINFO_BASENAME)) , EOL;
echo 'Call time to write Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
// Echo memory usage
echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;
// Save Excel5 file
echo date('H:i:s') , " Write to Excel5 format" , EOL;
$callStartTime = microtime(true);
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save(str_replace('.php', '.xls', __FILE__));
$callEndTime = microtime(true);
$callTime = $callEndTime - $callStartTime;
echo date('H:i:s') , " File written to " , str_replace('.php', '.xls', pathinfo(__FILE__, PATHINFO_BASENAME)) , EOL;
echo 'Call time to write Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
// Echo memory usage
echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;
// Echo memory peak usage
echo date('H:i:s') , " Peak memory usage: " , (memory_get_peak_usage(true) / 1024 / 1024) , " MB" , EOL;
// Echo done
echo date('H:i:s') , " Done writing files" , EOL;
echo 'Files have been created in ' , getcwd() , EOL;


?>
</body>
</html>