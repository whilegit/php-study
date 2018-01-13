<?php
require_once __DIR__ . '/trace.php';
use Whilegit\Tree\Gongpai\DebugMiddle;
use Whilegit\Tree\Gongpai\GongpaiTree;
use Whilegit\Utils\Trace;

$gongpai_debug_middle = new DebugMiddle();
$gongpai = new GongpaiTree($gongpai_debug_middle);

Trace::out($gongpai->output());

