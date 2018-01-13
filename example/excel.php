<?php
use Whilegit\Utils\Excel;
use Whilegit\Utils\Trace;

require_once __DIR__ . '/inc.php';
$list = array(
            array('aaaaaa'=>1, 'baaaaaaaaa'=>2),
            array('aaaaaa'=>3, 'baaaaaaaaa'=>4) );
$params = array(
            'columns'=>array(
        		array('field'=>'a', 'width'=>'24', 'title'=>'项目1'),
        		array('field'=>'b', 'width'=>'24', 'title'=>'项目2'),
                ),
            'title' => '项目表');

//Excel::export($list);

//$path = __DIR__ . '/TEMP/excel.xls';
//Excel::export($list, $params, $path);

//处理上传的excel文件
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