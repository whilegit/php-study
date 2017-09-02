<?php 
use Whilegit\Utils\Excel;
use Whilegit\Utils\Trace;
use Whilegit\Utils\IArray;
use Whilegit\Utils\Charset;
use Whilegit\Database\Basic\IPdo;
use Whilegit\Database\Model;
use Whilegit\Model\Virtual\AddonArticle;
use Whilegit\Model\Virtual\Mtypes;
require_once "vendor/autoload.php";

Trace::monolog('m2.log');

function init(){
	$db_config  = array(
		 'dbname' => 'dedecmsv57utf8sp2',
		 'host' => '127.0.0.1',
		 'port' => 3306,
		 'username' => 'root',
		 'password' => '317507Ok()lzr',
		 'charset' => 'utf-8');
	
	IPdo::instance('master', $db_config);
	IPdo::instance()->table(function($table){return "dede_{$table}";});
	Model::model_init(IPdo::instance());
}
Trace::set_error_handler();
Trace::set_exception_handler();
init();

//$sql = "Select * from `user` Where id=:id";
//Trace::out(IPdo::instance()->update('user', array('email'=>'Lzrrrzzz'), array('id'=>"1")));
//$e = IPdo::instance()->fetchall('Select `username`,`email` From `user` Where `username`=:username And `email`=:email', array('username'=>'lzrr', 'email'=>'6215715@qq.com'), 'username');
//$e = IPdo::instance()->get('user', array('id'=>1));
//$e = IPdo::instance()->allfields('addonarticle');
//Trace::out($e);

//Trace::out(IPdo::instance()->query($sql, array(':id'=>1)));
/*
PdoConnection::config($db_config, 'master');
$pdo = PdoConnection::get();
var_dump($pdo); exit;
*/
/*
class AddonArticle extends Model{
	protected static $table = '';
	protected static $pk = '';             // 主键名称
	protected static $fields = null;
	protected static $redirect_map = array('typeid'=>'\Mtypes');
}*/

/*
class Mtypes extends Model{
	protected static $table = '';
	protected static $pk = '';         // 主键名称
	protected static $fields = null;
	protected static $redirect_map = null;
}
*/
AddonArticle::redirectMap('typeid','Mtypes');
$model = AddonArticle::get(1); //
//$model = new AddonArticle();
$model->setAttr("typeid", 3);
/*
$model->setAttr("redirecturl", 'http://www.sina.com.cn/');
$model->setAttr("templet", '0');
$model->setAttr("userip", '127.0.0.1');
$model->setAttr("body", 'bodybody');*/
$model->save();
//$model->pull();
$typeid = $model->getAttr('typeid');
$mtypes = $model->redirect('typeid');
Trace::out($mtypes);


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