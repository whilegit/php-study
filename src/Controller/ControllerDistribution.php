<?php
namespace Whilegit\Controller;

class ControllerDistribution{
    
    //处理句柄，以$GPC的h参数为处理句柄的名称，默认定义了一个 module/controller的默认处理句柄。
    //需要在 src/Init/Bootstrap中定义一个WG_DEFAULT_CONTROLLER_DIR的控制器根目录
    protected $handlers = array();

    public function __construct(){
        $cookie_pre = defined('WG_COOKIE_PRE') ? WG_COOKIE_PRE : '';
        $GLOBALS['_GPC'] = Utils::init_gpc($cookie_pre);
        $this->register_handler('Index', array($this, 'default_handler'));
    }
    
    protected function default_handler(){
        global $_GPC;
        $module = isset($_GPC['m']) ? $_GPC['m'] : 'Index';
        $controller = isset($_GPC['c']) ? $_GPC['c'] : 'Index';
        $action = isset($_GPC['a']) ? $_GPC['a'] : 'Index';
       
        if(!defined('WG_DEFAULT_CONTROLLER_DIR')){
            die("Have not defined 'WG_DEFAULT_CONTROLLER_DIR'");
        }
        $path = WG_DEFAULT_CONTROLLER_DIR . '/' . $module . '/' . $controller . 'Controller.php';
        if(!file_exists($path)){
            die("'{$path}' doesnot exists.");
        }
        require_once($path);
        $cls = "\\Controller\\{$module}\\{$controller}Controller";
        if(!class_exists($cls)){
            die("'{$cls}' not find. PATH={$path}");
        }
        $obj = new $cls;
        $action = 'action'.$action;
        if(!method_exists($obj, $action)){
            die("'{$cls}' method not exists. Method='{$action}'");
        }
        call_user_func(array($obj, $action));
    }
    
    public function register_handler($name, Callable $handler = null){
        if(empty($handler)){
            return !empty($this->handlers[$name]) ? $this->handlers[$name] : null;
        }
        $this->handlers[$name] = $handler;
    }
    
    public function process(){
        global $_GPC;
        $h = isset($_GPC['h']) ? $_GPC['h'] : 'Index';
        if(isset($this->handlers[$h])){
            call_user_func($this->handlers[$h]);
        } else {
            die("Cannot find the controller_handler with name of {$h}");
        }
    }
}