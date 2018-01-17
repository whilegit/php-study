<?php
error_reporting(E_ALL);
ini_set('dispay_errors', 'On');

define('ROOT_PATH', str_replace('\\','/',dirname(__DIR__)));
define('IN_IA', true);

require_once dirname(__DIR__)."/vendor/autoload.php";

function MM($val = null){
    if(is_string($val)){
        $val = htmlspecialchars($val);
    }
    \Whilegit\Utils\Trace::out($val);
}
