<?php
use Whilegit\Utils\Pinyin;

require_once __DIR__ . "/inc.php";
header("Content-type: text/html; charset=utf-8");

echo "'要好岙屌貂'的全拼是：" . Pinyin::get("要好岙屌貂") . "<br />\r\n";