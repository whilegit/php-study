<?php
use Whilegit\Cache\FileCache;
use Whilegit\Cache\MysqlCache;
use Whilegit\Database\Model;
use Whilegit\Database\Basic\IPdo;

require_once __DIR__ . '/inc.php';

use Whilegit\Model\Virtual\While_Core_Cache; //虚拟模型，报错不要理会

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

$cache = new FileCache(__DIR__ . '/TEMP/Cache');


$cache = new MysqlCache(
    function($k){
        $record = While_Core_Cache::get($k);
        if(empty($record)) return null;
        $ret = array('content'=>$record->content, 'timeout'=>$record->timeout);
        return $ret;
    }, 
    function($k, $v, $t){
        $record = new While_Core_Cache();
        $record->setAttr('key', $k);
        $record->setAttr('content', $v);
        $record->setAttr('timeout', $t);
        $record->updateOrSave();
    },
    function($k){
        $record = While_Core_Cache::get($k);
        if(!empty($record)){
            $record->delete();
        }
    }
);

$key = 'uniacid:11';
$value = array('a','b','c');
$timeout = 1;
$cache->setCache($key, $value, $timeout);
$a9 = $cache->getCache($key);

//删除缓存
$cache->delCache($key);

$key = 'uniacid:9';
$value = array(3,2,1);
$timeout = 60;
$cache->setCache($key, $value, $timeout);
$a10 = $cache->getCache($key);

MM(array($a9, $a10));
