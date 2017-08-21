<?php
namespace Utils;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
define('WHILEGIT_UTILS_TRACE_ROOT_PATH', str_ireplace('\\', '/', getcwd()));

class Trace{
	/**
	 * 使用log功能时，必须先设用Trace::monolog()设置本静态变量
	 * @var unknown
	 */
	public static $monologInstance = null;
	
	/**
	 * 直接输出，并立即输出
	 * @param unknown $val
	 * @param string $level
	 */
	public static function out($val=null, $level = PHP_INT_MAX){
		$GLOBALS['trace_marker_level'] = $level;
		include (__DIR__  . '/../../static/utils_trace.phtml');
		exit;
	}
	
	/**
	 * 获取callable的原型(包括参数及默认参数)
	 * @param unknown $class 可以为空或者类名
	 * @param unknown $type 可以为空或::或->
	 * @param unknown $function 可以是函数名或方法名
	 * @return string|array
	 */
	public static function get_prototype($class, $type, $function){
		$info = array('success'=>false, 'msg'=>'');
		$callable = $class.$type.$function;
		if(!empty($class)){
			try{
				$mr = new \ReflectionMethod($class, $function);
				$doc = $mr->getDocComment();
				$modifiers = implode(' ',\Reflection::getModifierNames($mr->getModifiers()));
		
				$params = self::get_reflect_params($mr);
				$info = $modifiers . ' ' . $function . ' ( '.$params.' )';
			}catch(Exception $e){
				$info = array('success'=>false, 'msg'=>$callable . '()方法不存在，请检查__call()魔术方法');
			}
		}else{
			if(!in_array($function, array('require','require_once','include','include_once', 'eval', ''))){
				$mf = new \ReflectionFunction($function);
				$params = self::get_reflect_params($mf);
				$info = $function . ' ( '.$params.' )';
			}
		}
		return $info;
	}
	
	/**
	 * 函数反射方法或函数的参数表(包括默认参数)
	 * @param ReflectionFunctionAbstract $mr 实现类可能是ReflectionMethod或ReflectionFunction
	 * @return string 返回参数表
	 */
	public static function get_reflect_params($mr){
		//获取反射参数 ReflectionParameter 类型的数组
		$params = $mr->getParameters();
		$params_str = array();
		if(!empty($params)){
			foreach($params as $p){
				$name = $p->getName();
				$default_value_str = '';
				$optional = $p->isOptional();
				$ref = $p->isPassedByReference() ? '&' : '';

				//探查是否有默认参数
				if($p->isDefaultValueAvailable()){	
					if(method_exists($p, 'isDefaultValueConstant') && $p->isDefaultValueConstant ()){
						//PHP 5 >= 5.4.6, PHP 7支持获取constant参数
						$default_value_str .= ' = ' . basename($p->getDefaultValueConstantName());
					}else{
						//非constant类型的默认参数，取其值
						$default_value = $p->getDefaultValue();
						$t = gettype($default_value);
						switch($t){
							case 'string': $default_value = "'$default_value'";break;
							case 'integer':
							case 'double': $default_value = ($default_value == PHP_INT_MAX) ? 'PHP_INT_MAX' : $default_value; break;
							case 'array': $default_value=empty($default_value) ? '[]':'[ARRAY]';break;
							case 'object': $default_value='[OBJECT]';break;
							case 'NULL': $default_value='NULL';break;
							case 'boolean': $default_value=$default_value?'true':'false';break;
							case 'resource':  $default_value='[RESOURCE]';break;
							default: $default_value='[UNKONW TYPE]';break;
						}
						$default_value_str .= ' = ' . $default_value;
					}
				}
				$params_str[] = $ref .'$' . $name . $default_value_str;		
			}
		}
		return implode(', ',$params_str);
	}

	/**
	 * 获取简易明了的文件名(除去目录基部)
	 * @param string $path
	 * @return string
	 */
	protected static function simple_filepath($path){
		return str_ireplace(WHILEGIT_UTILS_TRACE_ROOT_PATH, '', str_replace('\\', '/', $path));
	}
	
	/**
	 * 获取当前访问全路径(包括query_string)
	 * @return string
	 */
	protected static function current_url(){
		$url = $_SERVER['SCRIPT_NAME'];
		if(!empty($_SERVER['QUERY_STRING'])) $url .= '?' . $_SERVER['QUERY_STRING'];
		return $url;
	}
	
	/**
	 * 记录当前变量(无栈信息)
	 * @param mixed $val
	 * @param Logger|string|null $monolog 如为null则使用静态monolog;如为string则当作文件名; 为Logger时则直接使用它
	 */
	public static function log($val = null, $monolog = null){
		$monolog = self::monolog($monolog);
		if(! $monolog instanceof Logger) return;
		
		$stack = debug_backtrace();
		$file = $file_full = 'System Call';
		$line = '';
		
		if(!empty($stack[0]['file'])){
			$file = self::simple_filepath($stack[0]['file']);
			$line = ':'.$stack[0]['line'] . 'L';
		}
		$monolog->addInfo('trace', array('mt'=>microtime(true),"data"=>$val, 'url'=>self::current_url(),"stack"=>$file.$line ));
	}
	
	/**
	 * 记录当前变量（包含栈信息）
	 * @param mixed $val
	 * @param Logger|string|null $monolog 如为null则使用静态monolog;如为string则当作文件名; 为Logger时则直接使用它
	 */
	public static function log_stacks($val = null, $monolog = null){

		$monolog = self::monolog($monolog);
		if(! $monolog instanceof Logger) return;
		
		$stack = debug_backtrace();
		$trace = "";
		for($i = 0; $i<count($stack); $i++){
			$file = $file_full = 'System Call';
			$line = '';
			if(!empty($stack[$i]['file'])){
				$file = self::simple_filepath($stack[$i]['file']);
				$line = ':'.$stack[$i]['line'] . 'L';
			}
			$trace .= $file.$line. ";\r\n";
		}
		$monolog->addInfo('trace', array('mt'=>microtime(true),'url'=>self::current_url(),"stacks"=>$trace, "data"=>$val));
	}
	
	/**
	 * 获取monolog实例或设置静态monolog实例
	 * @param Logger|String $monolog 如为null则返回静态monolog;如为string则当作文件名生成一个Logger; Logger时设置静态monolog<br>
	 *                               若在调用本函数前，静态monolog为null, 则一旦有可用的$monologo,则立即设置静态monolog
	 * @param int $level 如参数$monolog为string, 则本参数有意义
	 * @return null|Logger
	 */
	public static function monolog($monolog = null, $level = Logger::DEBUG){
		if($monolog instanceof Logger){
			self::$monologInstance = $monolog;
		}else if(is_string($monolog)){
			$path = $monolog;
			if(!file_exists(dirname($path)))	{
				if(! @mkdir(dirname($path), 0777, true) ){
					return false;
				}
			}
			if(!file_exists($path)) {
				file_put_contents($path, "", LOCK_EX);
			}
			
			$monolog = new Logger('Trace');
			$monolog->pushHandler(new StreamHandler($path, $level ));
			if(self::$monologInstance == null ){
				self::$monologInstance = $monolog;
			}
			return $monolog;
		}else if($monolog == null){
			return self::$monologInstance;
		}
	}
}