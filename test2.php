<?php 
use Utils\Excel;
use Utils\Trace;
use Db\IPdo;
use Utils\IArray;
use Utils\Charset;
require_once "vendor/autoload.php";

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
$str = "
<CB>u5341u65b9u521bu5ba2</CB>
<BR>u540du79f0            u5355u4ef7  u6570u91cf u91d1u989d
<BR>u6fb3u6d32u8fdbu53e3Broo155   1    155
<BR>u89c4u683cuff1au4ec0u9526u65e9u9910u8c37u7269
<BR>u6761u7801uff1a9326847000364
<BR>u8ba2u5355u7f16u53f7:SH20170826125623624883
<BR>bu8054u7cfbu4eba:u5415
<BR>u8054u7cfbu7535u8bdd:15068633525
<BR>u5730u5740:u6d59u6c5fu7701u53f0u5ddeu5e02u8defu6865u533a bu65e5u7528u54c1u5546u57ce
<BR>u914du9001u65b9u5f0f:u5febu9012
<BR>u5907u6ce8:
<BR>u4e0bu5355u65f6u95f4:2017-08-26 12:56
<BR>u4ed8u6b3eu65b9u5f0f:u672au652fu4ed8AA
<BR>u8fd0u8d39:9
<BR>u8ba2u5355u91d1u989d:163
<BR>";

$str = Charset::unicode2utf8(str_replace('u','\u',$str), true);
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