<?php 
use Whilegit\Utils\Excel;
use Whilegit\Utils\Trace;
use Whilegit\Utils\IArray;
use Whilegit\Utils\Charset;
use Whilegit\Database\Basic\IPdo;
use Whilegit\Database\Model;
use Whilegit\Model\Virtual\User;
use Whilegit\Model\Virtual\Contract;
use Whilegit\Utils\ImageCaptcha;
use Whilegit\Utils\Misc;
require_once "vendor/autoload.php";

$captcha = Misc::random(6);
/* 保存验证码 */
$image_captchar = new ImageCaptcha($captcha);
$image_captchar->output();
exit;


Trace::monolog('m2.log');

function init(){
	$db_config  = array(
		 'dbname' => 'jpress',
		 'host' => '127.0.0.1',
		 'port' => 3306,
		 'username' => 'root',
		 'password' => '317507Ok()lzr',
		 'charset' => 'utf8');
	
	IPdo::instance('master', $db_config);
	IPdo::instance()->table(function($table){return "jpress_{$table}";});
	Model::model_init(IPdo::instance());
}
Trace::set_error_handler();
Trace::set_exception_handler();
init();

$list = User::ls();
$list = Contract::ls(array('id >= '=>21), '*','contract_number');
$list = Contract::ls('21,22,23,24', '*','contract_number');
$list = Contract::count(array('id >= ' => 21));
$list = Contract::sum('amount','21,22,23,24');
Trace::out($list);

$list = array(
		array('a'=>1, 'b'=>2),
		array('a'=>3, 'b'=>4),
);
$params = array(
		'columns'=>array(
				array('tag'=>'aaaa', 'width'=>'24', 'title'=>'项目1'),
				array('tag'=>'bbbb', 'width'=>'24', 'title'=>'项目2'),
		),
		'title' => '项目表'
);

$params = array(
		'div' => '项目表',
		'p' =>array(
				array('tag'=>'span', 'width'=>'24', 'title'=>'项目1'),
				array('tag'=>'span', 'width'=>'24', 'title'=>'项目2'),
		),

);

//Excel::export($list, $params);
//Trace::out($_FILES);


$str = Charset::unicode2utf8('\uffe5', true);
//unpack('C*', $str))
Trace::out($str);

$xml =  IArray::toxml($params);
Trace::out(IArray::parsexml($xml));

IPdo::test();
if(!empty($_FILES['excel'])){
	
	$postfile = $_FILES['excel'];
	$ary = Excel::import($postfile);
	Trace::out($ary);
}
?>
<html>
<head></head>
<body>
<form method="post" action='' enctype='multipart/form-data'>
	<input type='file' name='excel'>
	<input type='submit' value='submit'>
</form>

</body>

</html>