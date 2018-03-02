<?php
namespace Whilegit\Controller;

use Whilegit\Utils\IString;

class Utils{
    /*
     * 初始化Get/Post/Cookie的请求参数
     */
    public static function init_gpc($cookie_pre = ''){
        $_GET = IString::stripslashes_deep($_GET);
        $_POST = IString::stripslashes_deep($_POST);
        $_COOKIE = IString::stripslashes_deep($_COOKIE);

        $cplen = strlen($cookie_pre);
        foreach($_COOKIE as $key => $value) {
            if(substr($key, 0, $cplen) == $cookie_pre) {
                $_GPC[substr($key, $cplen)] = $value;
            }
        }
        unset($cplen, $key, $value);
        
        $_GPC = array_merge($_GET, $_POST, $_GPC);
        $_GPC = IString::ihtmlspecialchars($_GPC);
        
        return $_GPC;
    }
}