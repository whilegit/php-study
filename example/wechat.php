<?php
use Whilegit\Wechat\WechatUtils;
require_once __DIR__ . "/inc.php";
header("Content-type: text/html; charset=utf-8");

MM(WechatUtils::is_weixin());