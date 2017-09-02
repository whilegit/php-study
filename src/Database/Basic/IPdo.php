<?php
namespace Whilegit\Database\Basic;
use Whilegit\Utils\IArray;

/**
 * 基础封装 PDO类，单例模式
 * @author Linzhongren
 */
class IPdo{
	protected static $instance = null;   //本类单例
	protected $pdo = null;               //真正的pdo对象
	protected $pdo_config = null;        //连接的配置参数
	protected $pdo_name = "";            //连接的别名
	protected $table_callback;           //表名回调(用于一些带前缀的情形)
	
	/**
	 * 获取实例化了的本类对象
	 * @param string $name   连接的别名
	 * @param array $config  连接的配置
	 * @param string $relink 是否强制pdo重连
	 * @return IPdo
	 */
	public static function instance($name = 'master', $config = array(), $relink = false){
		if($relink == false && self::$instance != null && self::$instance->pdo_name === $name){
			return self::$instance;
		}
		self::$instance = new IPdo($name, $config, $relink);
		return self::$instance;
	}
	
	/**
	 * 构造函数
	 * @param string $name    连接的别名
	 * @param array $config   连接参数
	 * @param string $relink  如果已存在此连接，是否重连
	 */
	protected function __construct($name = 'master', $config = array(), $relink = false){
		$pdo = PdoConnection::get($name, $config, $relink);
		$this->pdo = $pdo;
		$this->pdo_name = $name;
		$this->pdo_config = PdoConnection::config($name);
		$this->table(function($table){return $table;});
	}
	
	/**
	 * 返回真正的pdo对象
	 * @return PDO
	 */
	public function getPdo(){
		return $this->pdo;
	}
	
	/**
	 * 设置/返回/调用 表名回调
	 * @param  string|null|callable $param
	 * @return string|callable|IPdo 
	 */
	public function table($param = null){
		if($param == null) {
			return $this->table_callback;
		} else if (is_string($param)){
			return call_user_func($this->table_callback, $param);
		} else if(is_callable($param)){
			$this->table_callback = $param;
			return $this;
		}
	}
	
	/**
	 * 编译sql
	 * @param string $sql
	 * @return \PDOStatement
	 */
	public function prepare($sql) { 
		$sql = PdoUtils::replace_table($sql, $this->table_callback);
		//if($sql != 'DESCRIBE jpress_user' || $sql != 'SELECT * FROM jpress_user   ') \Whilegit\Utils\Trace::out($sql);
		$statement = $this->pdo->prepare($sql);
		return $statement;
	}
	
	
	/**
	 * 执行一个sql(select/insert/update/delete)
	 * @param string $sql
	 * @param array $params
	 * @return int|boolean  返回受影响的行数或查询到的记录数
	 */
	public function query($sql, $params = array()) {
		$statement = $this->prepare($sql);
		$result = $statement->execute($params);
		if ($result === false) {
			throw new \PDOException('statement fail');
		} else {
			return $statement->rowCount();
		}
	}
	
	/**
	 * 更新
	 * @param string $table  表名，不要加前缀
	 * @param array  $data   更新的关联数组
	 * @param array  $params 查询条件
	 * @param string $glue   条件之间的组合关系
	 * @return int 成功则返回受影晌的行数，失败则返回false. 注意区分：0和false的区别
	 */
	public function update($table, $data = array(), $params = array(), $glue = 'AND') {
		//组装Set子句部分
		$fields = PdoUtils::implode($data, ',');
		//组装Where子名部分
		$condition = PdoUtils::implode($params, $glue);
		//合并参数表
		$params = array_merge($fields['params'], $condition['params']);
		//拼接查询语句
		$sql = "UPDATE `{$table}` SET {$fields['fields']}";
		$sql .= $condition['fields'] ? ' WHERE '.$condition['fields'] : '';
		//执行
		return $this->query($sql, $params);
	}
	
	/**
	 * 插入
	 * @param string $table    表名(不要加前缀)
	 * @param array $data      插入的记录字段关联数组
	 * @param bool $replace    是否启用replace into
	 * @return 成功则返回受影晌的行数，失败则返回false. 注意区分：0和false的区别
	 */
	public function insert($table, $data = array(), $replace = FALSE) {
		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
		$condition = PdoUtils::implode($data, ',');
		return $this->query("$cmd `{$table}` SET {$condition['fields']}", $condition['params']);
	}
	
	/**
	 * 返回最后一个插入的记录id
	 * @return int
	 */
	public function insertid() {
		return $this->pdo->lastInsertId();
	}
	
	/**
	 * 删除记录
	 * @param String $table  表名(不要加前缀)
	 * @param array $params  条件参数表
	 * @param string $glue   组合逻辑
	 * @return int 受影响的行数
	 */
	public function delete($table, $condition = array(), $glue = 'AND') {
		$condition = PdoUtils::implode($condition, $glue);
		$sql = "DELETE FROM `{$table}`";
		$sql .= $condition['fields'] ? ' WHERE '.$condition['fields'] : '';
		return $this->query($sql, $condition['params']);
	}
	
	//////////////////////////////////////////////////////////////////////////////////////
	
	/**
	 * 获取第一行记录的第$column列的内容
	 * @param unknown $sql  查询语句
	 * @param array $params 条件
	 * @param int $column   返回的某一个列
	 * @return boolean|string 失败返回false，成功返回该列的内容
	 */
	public function fetchcolumn($sql, $params = array(), $column = 0) {
		$statement = $this->prepare($sql);
		$result = $statement->execute($params);
		if (!$result) {
			throw new \PDOException(var_export($this->pdo->errorInfo(), true));
		} else {
			return $statement->fetchColumn($column);
		}
	}
	
	/**
	 * 获取第一条记录
	 * @param string $sql
	 * @param array $params
	 * @return boolean|array  失败返回false, 成功返回第一条记录
	 */
	public function fetch($sql, $params = array()) {
		$statement = $this->prepare($sql);
		$result = $statement->execute($params);
		if (!$result) {
			return false;
		} else {
			return $statement->fetch(\PDO::FETCH_ASSOC);
		}
	}
	

	/**
	 * 返回全部记录
	 * @param string $sql
	 * @param array $params
	 * @param string $keyfield  关键字段（若存在，则返回数组中的键名将是此字段）
	 * @return boolean|array    失败返回false, 成功返回所有记录
	 */
	public function fetchall($sql, $params = array(), $keyfield = '') {
		$statement = $this->prepare($sql);
		$result = $statement->execute($params);
		if (!$result) {
			return false;
		} else {
			$raw = $statement->fetchAll(\PDO::FETCH_ASSOC);
			if (empty($keyfield)) return $raw;

			$rs = array();
			if (!empty($raw)) {
				$rs = IArray::rekey($raw, $keyfield);
			}
			return $rs;
		}
	}
	
	/**
	 * 获取表中的一条记录
	 * @param string $tablename 表名
	 * @param array $condition  条件
	 * @param array $fields     获取的字段
	 * @return false|array
	 */
	public function get($tablename, $condition = array(), $fields = array()) {
		$select = PdoUtils::fields($fields);
		$condition = PdoUtils::implode($condition, 'AND');
		$sql = "SELECT {$select} FROM {$tablename} " . (!empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . " LIMIT 1";
		return $this->fetch($sql, $condition['params']);
	}
	
	/**
	 * 单表查询
	 * @param string $tablename     表名
	 * @param array $condition      条件
	 * @param array|string $fields  要列出的字段
	 * @param string $keyfield      返回值中作为键名的字段
	 * @param array|String $orderby 排序, String时，直接拼进sql中，有无order by关键字无所谓; array时直接并接order by xxx，排序默认
	 * @param array|String $limit   个数限字，String时直接拼进sql中，有无limit关键字无所谓；array时拼成 limit a,b字样，可以实现分页
	 * @return array
	 */
	public function getall($tablename, $condition = array(), $fields = array(), $keyfield = '', $orderby = array(), $limit = array()) {
		$select = PdoUtils::fields($fields);
		$condition = PdoUtils::implode($condition, 'AND');
		$condition['fields'] = !empty($condition['fields']) ? " WHERE {$condition['fields']}" : '';
		$orderbysql = PdoUtils::order($orderby);
		$limitsql = PdoUtils::limit($limit);
		$sql = "SELECT {$select} FROM {$tablename} {$condition['fields']} {$orderbysql} {$limitsql}";
		return $this->fetchall($sql, $condition['params'], $keyfield);
	}
	
	public function getcount($tablename, $condition = array()){
		$condition = PdoUtils::implode($condition, 'AND');
		$condition['fields'] = !empty($condition['fields']) ? " WHERE {$condition['fields']}" : '';
		$sql = "SELECT count(*)  FROM {$tablename} {$condition['fields']}";
		//\WhileGit\Utils\Trace::out($sql);
		return $this->fetchcolumn($sql, $condition['params']);
	}
	
	public function getsum($tablename, $field, $condition = array()){
		$condition = PdoUtils::implode($condition, 'AND');
		$condition['fields'] = !empty($condition['fields']) ? " WHERE {$condition['fields']}" : '';
		$sql = "SELECT sum({$field})  FROM {$tablename} {$condition['fields']}";
		return $this->fetchcolumn($sql, $condition['params']);
	}
	
	/**
	 * 单表查询
	 * @param string $tablename     表名
	 * @param array $condition      条件
	 * @param array|string $fields  要列出的字段
	 * @param string $keyfield      返回值中作为键名的字段
	 * @param array|String $orderby 排序, String时，直接拼进sql中，有无order by关键字无所谓; array时直接并接order by xxx，排序默认
	 * @param array|String $limit   个数限字，String时直接拼进sql中，有无limit关键字无所谓；array时拼成 limit a,b字样，可以实现分页
	 * @return array
	 */
	public function getslice($tablename, $condition = array(), $fields = array(), $keyfield = '', $orderby = array(), $limit = array(), &$total=null) {
		$select = PdoUtils::fields($fields);
		$condition = PdoUtils::implode($condition, 'AND');
		$condition['fields'] = !empty($condition['fields']) ? " WHERE {$condition['fields']}" : '';
		$orderbysql = PdoUtils::order($orderby);
		$limitsql = PdoUtils::limit($limit);
		$sql = "SELECT {$select} FROM {$tablename} " . "{$condition['fields']} {$orderbysql} {$limitsql}";
		$total = $this->fetchcolumn("SELECT COUNT(*) FROM {$tablename} {$condition['fields']}", $condition['params']);
		return $this->fetchall($sql, $condition['params'], $keyfield);
	}
	
	/**
	 * 获取单表的第一条记录的某一字段
	 * @param unknown $tablename
	 * @param array $params
	 * @param unknown $field
	 * @return unknown|boolean
	 */
	public function getcolumn($tablename, $params = array(), $field) {
		$result = $this->get($tablename, $params, array($field));
		if (!empty($result)) {
			return $result[$field];
		} else {
			return false;
		}
	}
	
	///////////////////////////////////////////////////////////////////////////////
	
	/*
	 * 事务三函数
	 */
	public function begin() { $this->pdo->beginTransaction(); }
	public function commit() { $this->pdo->commit(); }
	public function rollback() { $this->pdo->rollBack(); }
	
	/**
	 * 测试某字段是否存在
	 * @param string $tablename
	 * @param string $fieldname
	 * @return boolean
	 */
	public function fieldexists($tablename, $fieldname) {
		$tablename = $this->table($tablename);
		$isexists = $this->fetch("DESCRIBE  `$tablename` `{$fieldname}`");
		return !empty($isexists) ? true : false;
	}
	
	/**
	 * 测试表中某字段是否存在索引
	 * @param unknown $tablename
	 * @param unknown $indexname
	 * @return boolean
	 */
	public function indexexists($tablename, $indexname) {
		if (!empty($indexname)) {
			$tablename = $this->table($tablename);
			$indexs = $this->fetchall("SHOW INDEX FROM {$tablename}", array('Column_name'), 'Column_name');
			return key_exists($indexname, $indexs);
		}
		return false;
	}
	
	public function pk($tablename){
		if (!empty($tablename)) {
			$tablename = $this->table($tablename);
			$indexs = $this->fetchall("SHOW INDEX FROM {$tablename}", array('Key_name', 'Column_name'), 'Key_name');
			if(!empty($indexs['PRIMARY'])){
				return $indexs['PRIMARY']['Column_name'];
			}else {
				throw new \InvalidArgumentException("$tablename has no primary field.");
			}
		}
	}
	
	/**
	 * 查看表是否存在
	 * @param string $table
	 * @return boolean
	 */
	public function tableexists($table) {
		if(empty($table)) return false;
		$table = $this->table($table);
		$data = $this->fetch("SHOW TABLES LIKE '{$table}'");
		if(empty($data)) return false;
		$data = array_values($data);
		return in_array($table, $data);
	}
	
	/**
	 * 获取表的全部字段名
	 * @param string $tablename
	 * @return array
	 */
	public function allfields($tablename = null){
		static $fields_map = array();
		if(empty($tablename)) return $fields_map;
		
		$tablename = $this->table($tablename);
		if(!empty($fields_map[$tablename])){
			return $fields_map[$tablename];
		}
		
		$fields = $this->fetchall("DESCRIBE {$tablename}", array(), 'Field');
		//$fields = array_keys($fields);
		$fields_map[$tablename] = $fields;
		return $fields;
	}
}