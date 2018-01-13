<?php
require_once __DIR__ . '/trace.php';
use Whilegit\Utils\Trace;
use Whilegit\Database\Basic\IPdo;
use Whilegit\Database\Model;

use Whilegit\Model\Virtual\While_Member; //虚拟模型，报错不要理会
use Whilegit\Model\Virtual\While_Order;  //虚拟模型，报错不要理会

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

$list = While_Member::ls();
$list = While_Member::ls(array('user_id >= '=> 1), '*','user_id');
$list = While_Member::ls('2,3', '*','user_id');
$list = While_Member::count(array('user_id >= ' => 1));
Trace::out($list);
