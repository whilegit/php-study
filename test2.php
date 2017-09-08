<?php 
use Whilegit\Utils\Excel;
use Whilegit\Utils\Trace;
use Whilegit\Utils\IArray;
use Whilegit\Utils\Charset;
use Whilegit\Database\Basic\IPdo;
use Whilegit\Database\Model;
use Whilegit\Model\Virtual\User;
use Whilegit\Model\Virtual\Contract;
use Whilegit\Utils\Image\ImageCaptcha;
use Whilegit\Utils\Misc;
use Whilegit\Utils\Location\Amap;
//use Whilegit\Utils\Qrcode;
use Whilegit\Utils\Image\Gd;
use Whilegit\Utils\Image\Magick;
use Whilegit\Utils\File;
use Whilegit\Utils\Image\Type;
require_once "vendor/autoload.php";


Trace::out(Type::is_png("test2.php"));


$str = mb_convert_encoding('林忠仁','GBK', 'UTF-8');
//echo $str;
//var_dump(unpack('C*', $str)); exit;
$magick = new Magick();
$magick->input('E:/borrow-agreement.png')->setting_font('E:/msyh.ttf')->setting_pointsize(20)
	   ->draw_text(360,350,'SN20170907153000123456')
	   ->draw_text(485,410, '毛泽东')->draw_text(300,467,'331081198407237619') //出错人
	   ->draw_text(485,525, '蒋介石')->draw_text(300,580,'331081198407237619') //借款人
	   ->draw_text(420,5740, '蒋介石')->draw_text(280,5775,'2017')->draw_text(395, 5775, '09')->draw_text(476, 5775, '07') //借款人签名
	   ->draw_text(420,5852, '毛泽东')->draw_text(280,5887,'2018')->draw_text(395, 5887, '08')->draw_text(476, 5887, '08') //出借人签名
	   ->draw_text(280,6000,'2019')->draw_text(400, 6000, '09')->draw_text(480, 6000, '09') //365签名
       ->output('E:/r_.jpg');
//Magick::resize('E:/gamma.gif', 'E:/gamma_.gif', '1000%');
File::output('E:/r_.jpg');

$params = array(
		'size'     => 600,  //二维码尺寸
		'logopath' => __DIR__.'/static/image/logo.png',   //中间logo的图片位置，为空时不输出logo
		'label'    => '微信扫一扫',   //二维码下方的文字，为空时不输出
);

echo Gd::qrcode('You belong with me', $params, true); exit;


//$ary = Amap::geo('121.3312697411,28.5790573264');
$ary = Amap::geo(array(array('lng'=>'121.457607','lat'=>'28.375191'), array('lng'=>'121.457607','lat'=>'28.375191')));
Trace::out($ary);

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