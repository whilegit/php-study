<?php

use Whilegit\Init\Bootstrap;
use Whilegit\View\Template;
use Whilegit\Controller\ControllerDistribution;

require_once dirname(dirname(__DIR__))."/vendor/autoload.php";

function MM($val = null){
    if(is_string($val)){
        $val = htmlspecialchars($val);
    }
    \Whilegit\Utils\Trace::out($val);
}


//常量定义
$defs = array(
    'WG_ROOT'=>__DIR__,
    'WG_FILECACHE_DIR'=>__DIR__ . '/Cache',
    'WG_DEVELOPMENT' => true,
    'WG_REWRITE_ON' => true,
    'WG_COOKIE_PRE' => '',
    'WG_DEFAULT_CONTROLLER_DIR' => __DIR__ . '/ControllersDir',
    'WG_TEMPLATE_SPECIAL_DIR' => __DIR__ . '/Callback/DefaultTemplateSpecial',
    'WG_TEMPLATE_PATH' => __DIR__ . '/Templates',
    'WG_COMPILE_PATH' => __DIR__ . '/Compiled',
    'WG_INCLUDE_TEMPLATE_PATH' => __DIR__ . '/Templates/includes',
    'WG_INCLUDE_COMPILED_PATH' => __DIR__ . '/Compiled/includes'
);

//数据库连接参数
$dbconfig  = array(
    'dbname' => 'php_study',
    'host' => '127.0.0.1',
    'port' => 3306,
    'username' => 'root',
    'password' => '317507',
    'charset' => 'utf8');
$table_callback = function($table){return 'ims_' . $table;};

//常量定义和数据库连接参数的初始化
Bootstrap::init($defs, $dbconfig, $table_callback);

//默认模板引擎的初始化
Template::init(array(
    'template_special_dir' => WG_TEMPLATE_SPECIAL_DIR,
    'template_path'=>WG_TEMPLATE_PATH,
    'compile_path' => WG_COMPILE_PATH,
    'include_template_path' => WG_INCLUDE_TEMPLATE_PATH,
    'include_compile_path' => WG_INCLUDE_COMPILED_PATH,
));

$controllerDist = new ControllerDistribution();
$controllerDist->process();
