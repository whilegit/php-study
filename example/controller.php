<?php

use Whilegit\Init\Bootstrap;
use Whilegit\Controller\ControllerDistribution;

require_once dirname(__DIR__)."/vendor/autoload.php";

function MM($val = null){
    if(is_string($val)){
        $val = htmlspecialchars($val);
    }
    \Whilegit\Utils\Trace::out($val);
}

$defs = array(
    'WG_ROOT'=>dirname(__DIR__),
    'WG_FILECACHE_DIR'=>__DIR__ . '/TEMP/Cache',
    'WG_DEVELOPMENT' => true,
    'WG_COOKIE_PRE' => '',
    'WG_DEFAULT_CONTROLLER_DIR' => __DIR__ . '/ControllersDir'
);

$dbconfig  = array(
    'dbname' => 'php_study',
    'host' => '127.0.0.1',
    'port' => 3306,
    'username' => 'root',
    'password' => '317507',
    'charset' => 'utf8');
$table_callback = function($table){return 'ims_' . $table;};

Bootstrap::init($defs, $dbconfig, $table_callback);

$controllerDist = new ControllerDistribution();
$controllerDist->process();