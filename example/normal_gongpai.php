<?php
use Whilegit\Database\Model;
use Whilegit\Database\Basic\IPdo;
use Whilegit\Tree\NormalGongpai\GongpaiModel;
//use Whilegit\Model\Virtual\While_Member; //虚拟模型，报错不要理会

require_once __DIR__ . '/trace.php';

$db_config  = array(
    'dbname' => 'php_study',
    'host' => '127.0.0.1',
    'port' => 3306,
    'username' => 'root',
    'password' => '317507',
    'charset' => 'utf8');

IPdo::instance('master', $db_config);
IPdo::instance()->table(function($table){return "ims_{$table}";});
Model::model_init(IPdo::instance());

$gpitem = GongpaiModel::get(array('user_id'=>'1'));
$entry = $gpitem->findPosition();

$user_id = 9;
$entry->addSub($user_id);
MM(GongpaiModel::get(array('user_id'=>$user_id)));
