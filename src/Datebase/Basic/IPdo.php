<?php
namespace Datebase\Basic;
class IPdo{
	protected static $_links = null;
	
	
	public static function get($name, $config){
		
	}
	
	/**
	 * 连接数据库
	 * @param string|array  $dsn     连接信息，为string则为应包含dsn,username,password三个信息, 为array时则关联数组
	 * @param array         $options <pre>连接额外附带的信息，如 
	 * 		PDO::ATTR_PERSISTENT => true         //将保持一个持有连接
	 *      PDO::ATTR_EMULATE_PREPARES => false  //关闭模专转义，改由数据库来执行真正的转义
	 *      
	 */
	protected static function connect($dsn, $options = array()){
		if(is_array($dsn)){
			$config = $dsn;
			$dsn = $config['dsn'];
			$username = $config['username'];
			$password = $config['password'];
			$options = $config['options'];
		}
		$options = array(PDO::ATTR_PERSISTENT => $cfg['pconnect']);
		$pdo = new PDO($dsn, $username, $password, $options);
	}
	//( string $dsn [, string $username [, string $password [, array $options ]]] )
	
	public static function test(){
		echo 'IPdo'; exit;
	}
}