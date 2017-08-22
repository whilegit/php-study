<?php 
use Utils\Excel;
use Utils\Trace;
require_once "vendor/autoload.php";
/*
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
Excel::export($list, $params);
*/
//Trace::out($_FILES);
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