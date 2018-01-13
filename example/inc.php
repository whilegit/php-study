<?php
error_reporting(E_ALL);
ini_set('dispay_errors', 'On');
require_once dirname(__DIR__)."/vendor/autoload.php";

function MM($val = null){
    \Whilegit\Utils\Trace::out($val);
}