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
        

        //REWRITE规则. 常量 WG_REWRITE_ON 在入口文件处定义
        /**
         <VirtualHost *:80>
             ServerAdmin webmaster@dummy-host2.example.com
             DocumentRoot "D:\wwwroot\php-study\example\App"
             ServerName php-study
             ErrorLog "D:\logs\php-study-error.log"
             CustomLog "D:\logs\php-study-access.log" common
             <Directory "D:\wwwroot\php-study\example\App">
            	Options Indexes FollowSymLinks ExecCGI
            	AllowOverride All
            	Order Allow,Deny
            	Allow from all
            	RewriteEngine on
            	RewriteCond %{REQUEST_FILENAME} !-d
            	RewriteCond %{REQUEST_FILENAME} !-f
            	RewriteRule ^(.*)(\?(.*))?$ index.php?r=$1&$2 [QSA,PT,L]
             </Directory>
         </VirtualHost>
         */
        if(defined('WG_REWRITE_ON') && WG_REWRITE_ON == true && !empty($_GPC['r'])){
            $dot = strrpos($_GPC['r'], '.');
            if($dot !== false){
                $_GPC['r'] = substr($_GPC['r'], 0, $dot);
            }
            $rs = explode('/', $_GPC['r']);

            if(isset($rs[0])) $_GPC['m'] = $rs[0];
            if(isset($rs[1])) $_GPC['c'] = $rs[1];
            if(isset($rs[2])) $_GPC['a'] = $rs[2];
            unset($_GPC['r']);
        }
        
        return $_GPC;
    }
}