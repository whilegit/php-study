<?php
use Whilegit\Utils\IArray;
use Whilegit\Utils\Trace;

require_once __DIR__ . "/inc.php";


$params = array(
    'div' => '项目表',
    'p' =>array(
        array('tag'=>'span', 'width'=>'24', 'title'=>'项目1'),
        array('tag'=>'span', 'width'=>'24', 'title'=>'项目2'),
    ),
);

$xml =  IArray::toxml($params, 'root');
//Trace::out($xml);
Trace::out(IArray::parsexml($xml));