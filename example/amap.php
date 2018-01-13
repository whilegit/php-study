<?php
use Whilegit\Utils\Trace;
use Whilegit\Utils\Location\Amap;

require_once __DIR__ . '/inc.php';

$ary = Amap::geo('121.3312697411,28.5790573264');
//$ary = Amap::geo(array(array('lng'=>'121.457607','lat'=>'28.375191'), array('lng'=>'121.457607','lat'=>'28.375191')));
Trace::out($ary);