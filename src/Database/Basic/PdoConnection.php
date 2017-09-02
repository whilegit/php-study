<?php
namespace Whilegit\Database\Basic;
use PDO;
class PdoConnection{
	
	/**
	 * 连接持有数组
	 * @desc array<pre>
	 * (
	 * 	  'master'=>array(
	 * 		  'config'=>array(...), 
	 * 		  'pdo'=>pdo_object
	 * 	   ), 
	 *     ...
	 * )
	 * </pre>
	 * @var array
	 */
	protected static $_Links = array();
	
	/**
	 * 设置数据库连接参数
	 * @param string $name
	 * @param string $config
	 */
	public static function config($param1, $param2 = 'master'){
		if(empty($param1) || is_string($param1)){
			$name = $param1;
			return isset(self::$_Links[$name]['config']) ? self::$_Links[$name]['config'] : null;
		}else if(is_array($param1)){
			$name = $param2;
			if(empty(self::$_Links[$name])) self::$_Links[$name] = array();
			self::$_Links[$name]['config'] = $param1;
		}
	}
	
	/**
	 * 获取实际pdo连接
	 * @param array $config   连接参数，如空将使用self::$_Links里的同名配置
	 * @param string $name    给连接起的外号，方便重复使用。如已有同名连接，则直接返回该连接。
	 * @param string $relink  是否强制重连
	 * @throws \PDOException  
	 * @return \PDO
	 */
	public static function get($name = 'master', $config = array(), $relink = false){
		if($relink == false && isset(self::$_Links[$name]['pdo'])) {
			return self::$_Links[$name]['pdo'];
		}
		if(empty($config)){
			$config = self::config($name);
			if($config == null){
				throw new \PDOException('no link params found');
			}
		}
		$options = array();
		foreach($config as $key=>$value){
			if(is_int($key)){
				$options[$key] = $value;
			}
		}
		$pdo = self::real_connect($config, $options);
		self::$_Links[$name]['pdo'] = $pdo;
		self::$_Links[$name]['config'] = $config;
		return $pdo;
	}
	
	/**
	 * 连接数据库
	 * @param string|array  $dsn     连接信息，关联数组<pre>
	 * array(
	 * 	  'dbname' => 'db',
	 *    'host' => 'host',
	 *    'port' => 3306,
	 *    'username' => 'root',
	 *    'password' => '123456',
	 *    'charset' => 'utf-8'
	 *  ) </pre>
	 * @param array         $options <pre>连接额外附带的信息，如 
	 * 		PDO::ATTR_PERSISTENT => true         //将保持一个持有连接
	 *      PDO::ATTR_EMULATE_PREPARES => false  //关闭模专转义，改由数据库来执行真正的转义
	 */
	protected static function real_connect($config, $options = array()){

		$username = $config['username'];
		$password = $config['password'];
		$dsn = "mysql:dbname={$config['dbname']};host={$config['host']};port={$config['port']}";

		$options_local = array( PDO::ATTR_PERSISTENT => true,			//持久化连接
								PDO::ATTR_EMULATE_PREPARES => false);   //不使用模拟Prepare
		$options = array_merge($options_local, $options);
		$pdo = new PDO($dsn, $username, $password, $options);
		$sql = "SET NAMES '{$config['charset']}';";
		$pdo->exec($sql);
		$pdo->exec("SET sql_mode='';");
		return $pdo;
	}

}