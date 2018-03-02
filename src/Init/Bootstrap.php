<?php
namespace Whilegit\Init;

use Whilegit\Database\Model;
use Whilegit\Database\Basic\IPdo;
use Whilegit\Utils\Agent;
use Whilegit\Utils\Misc;

class Bootstrap{
    
    /**
     * 系统初始化函数
     * @param array $defs
     * @param array $dbconfig
     * @param Callable $table_callback 
     * @example <pre>
     *    $dbconfig  = array(
     *       'dbname' => 'php_study',
     *       'host' => '127.0.0.1',
     *       'port' => 3306,
     *       'username' => 'root',
     *       'password' => '317507',
     *       'charset' => 'utf8'); 
     *   </pre>
     * @example $table_callback = function($table){return "ims_{$table}";}
     */
    public static function init($defs, $dbconfig, $table_callback = null){
        //初始化常量
        self::defines($defs);
        
        //初始化数据库连接及模型
        IPdo::instance('master', $dbconfig);
        if(empty($table_callback)) $table_callback = function($table){return $table;};
        IPdo::instance()->table($table_callback);
        Model::model_init(IPdo::instance());
    }
    
    /**
     * 常数定义
     * @param array $defs     'key'=>$val 关联数组形式给出的常数列表，$key前自动加上WG_前缀
     * @param bool  $blocked  true表示允许空缺必须定义的常数项
     * @example 当前可用的常数： WG_IN==true, WG_STARTTIME==microtime(), WG_TIMESTAMP=>time(), WG_VERSION=>WHILEGIT版本号
     * @example WG_CLIENT_IP: 当前客户端的请求IP
     * @example WG_ROOT=>项目根目录, WG_FILECACHE_DIR=>文件缓存根目录, WG_DEVELOPMENT=>是否开发中, WG_COOKIE_PRE=>cookie的前缀
     * @example WG_DEFAULT_CONTROLLER_DIR => 控制器的根目录
     */
    private static function defines($defs, $blocked = true){
        foreach($defs as $k=>$v){
            if(!defined("WG_{$k}")){
                $k = strpos($k, 'WG_') === 0 ? $k : 'WG_'.$k;
                define($k, $v);
            }
        }
        
        define('WG_CLIENT_IP', Agent::real_ip());
        define('WG_IN', true);
        define('WG_STARTTIME', microtime());
        define('WG_TIMESTART', time());
        define('WG_VERSION', '1.0.0');
        define('WG_IS_AJAX', Misc::isajax());
        define('WG_IS_POST', Misc::ispost());
        define('WG_IS_HTTPS', Misc::ishttps());
        
        if(defined('WG_DEVELOPMENT') && WG_DEVELOPMENT) {
            ini_set('display_errors', '1');
            error_reporting(E_ALL ^ E_NOTICE);
        } else {
            error_reporting(0);
        }
        
        // 必须定义项目根目录
        if(!defined('WG_ROOT')){
            if($blocked) die('MUST define WG_ROOT');
        }
        // 必须定义文件缓存目录
        if(!defined('WG_FILECACHE_DIR')){
            if($blocked) die('MUST define WG_FILECACHE_DIR');
        }
    }
}