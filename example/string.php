<?php
use Whilegit\Utils\IString;
use Whilegit\Utils\Misc;

require_once __DIR__ . "/inc.php";
header("Content-type: text/html; charset=utf-8");

echo '随机字符串: '. Misc::random(16) . '<br />';

$str = "string\'a\"\\\'";
echo "IString::stripslashes_deep转义： 前[ {$str} ]  后 [ " . IString::stripslashes_deep($str) . " ]<br />\r\n";
$str = "<div class=\"right\">ba&laba\"ba</div>";
echo "IString::ihtmlspecialchars转义： 前<input type='text' value='{$str}'/>  后 <input type='text' value='" . str_replace('&', '＆', IString::ihtmlspecialchars($str)) . "' /><br />\r\n";

echo "Misc::referer: " . Misc::referer() . '，$_SERVER[\'HTTP_REFERER\']='.  $_SERVER['HTTP_REFERER'] . "<br />\r\n";
echo "Misc::token: " . Misc::token() . "<br />\r\n";