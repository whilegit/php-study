<?php
use Whilegit\Cache\FileCache;

require_once __DIR__ . '/inc.php';

$cache = new FileCache(__DIR__ . '/TEMP/Cache');

$key = 'uniacid:1';
$value = array(1,2,3);
$timeout = 0;
//$cache->setCache($key, $value, $timeout);
$cache->delCache($key);
//MM($cache->getCache($key));
