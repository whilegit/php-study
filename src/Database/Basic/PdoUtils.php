<?php
namespace Whilegit\Database\Basic;
use Whilegit\Utils\IString;

class PdoUtils{
	
	/**
	 * 提取操作符
	 * @param string $field 如 name     age +=      title like
	 * @param string|array  $def_value 字段的值，主要是确认其是否为数组
	 * @param array $allows 允许的操作符种类(其中+=和-=是新扩展的操作符，mysql中没有，需要特别处理)
	 * @return array('field'=>'age', 'operator'=>'+=')
	 */
	protected static function operator($field, $def_value, $allows = array('>', '<', '<>', '!=', '>=', '<=', '+=', '-=', 'LIKE', 'like')){
		$field = trim($field);
		$operator = '';
		if(strpos($field, ' ') !== false){
			list($field, $operator) = explode(' ', $field, 2);
			if(!in_array($operator, $allows)){
				$operator = '';
			}
		}
		
		if($operator == ''){
			$operator = is_array($def_value) ? 'IN' : '=';
		} else if($operator == '+=' || $operator == '-='){
			$ch = $operator{0};
			$operator = " = `$field` {$ch} ";
		}
		return array('field'=>$field, 'operator'=>$operator);
	}
	
	/**
	 * 拼接sql的where子句, update的Set子句
	 * @param array $params  <pre>
	 * array("id"=>'1', 'name like'=>'James', 'age >=' => '30', ...)
	 *        </pre>
	 * @param string $glue 各子句的连接符，可以为, 逗号(适合update的set子句)，And/Or(适合Where子句)
	 * @return array('fields'=>'xxx And xxx', 'params'=>array(...))
	 */
	public static function implode($params, $glue = ',') {
		$result = array('fields' => '', 'params' => array());
		
		$suffix = '';
		$allow_operator = array('>', '<', '<>', '!=', '>=', '<=', '+=', '-=', 'LIKE', 'like');
		if (in_array(strtolower($glue), array('and', 'or'))) {
			$suffix = '__';
		}

		$split = '';
		foreach ($params as $field => $value) {
			$tmp = self::operator($field, $value);
			$field = $tmp['field'];
			$operator = $tmp['operator'];
			
			$sf = ":{$suffix}{$field}";
			if ($operator == 'IN') {
				$insql = array();
				foreach ($value as $k => $v) {
					$bind_name = "{$sf}_{$k}";
					$insql[] = $bind_name;
					$result['params'][$bind_name] = is_null($v) ? '' : $v;
				}
				$result['fields'] .= $split . "$field IN (" . implode(",", $insql) . ")";
				
			} else {
				$result['fields'] .= $split . "$field {$operator} {$sf}";
				$result['params'][$sf] = is_null($value) ? '' : $value;
			}
			$split = ' ' . $glue . ' ';
		}
		return $result;
	}
	
	/**
	 * 表名自动替换
	 * @param string $sql
	 * @param callable $callback
	 * @return string
	 */
	public static function replace_table($sql, $callback){
		return  preg_replace_callback(
					array('/(SELECT\s+[\.\*a-z0-9_,`\(\) ]+\s+FROM\s+[`]?)([a-z][0-9a-z_]*)([`]?\s+)/i',
						  '/(UPDATE\s+[`]?)([a-z][0-9a-z_]*)([`]?\s+SET)/i',
						  '/(INSERT\s+INTO\s+[`]?)([a-z][a-z0-9_]*)([`]?\s+[\(]|[`]?\s+[SET])/i',  //INSERT INTO tablename () Values ()和INSERT INTO tablename SET用法
						  '/(REPLACE\s+INTO\s+[`]?)([a-z][a-z0-9_]*)([`]?\s+[\(]|[`]?\s+[SET])/i',
						  '/(DELETE\s+FROM\s+[`]?)([a-z][0-9a-z_]*)([`]?\s+WHERE)/i',
					),
					function($matches) use ($callback){
						return $matches[1] . $callback($matches[2]) . (!empty($matches[3])?$matches[3]:"");
					}, $sql);
	}
	
	
	public static function order($orderby){
		$orderbysql = "";
		if (!empty($orderby)) {
			if (is_array($orderby)) {
				$orderbysql = 'ORDER BY '. implode(',', $orderby);
			} else {
				$orderbysql = IString::exists(strtoupper($orderby), 'ORDER') ? "$orderby" : "ORDER BY $orderby";
			}
		}
		return $orderbysql;
	}
	
	public static function limit($limit){
		$limitsql = "";
		if (!empty($limit)) {
			if (is_array($limit)) {
				if (count($limit) == 1) {
					$limitsql = " LIMIT " . $limit[0];
				} else {
					$limitsql = " LIMIT " . ($limit[0] - 1) * $limit[1] . ', ' . $limit[1];
				}
			} else {
				$limitsql = IString::exists(strtoupper($limit), 'LIMIT') ? " $limit " : " LIMIT $limit";
			}
		}
		return $limitsql;
	}
	
	public static function fields($fields){
		$select = '*';
		if (!empty($fields)){
			if (is_array($fields)) {
				$select = '`'.implode('`,`', $fields).'`';
			} else {
				$select = $fields;
			}
		}
		return $select;
	}

	public static function params($params, $pk){
		$ret = array();
		if(empty($params)) return $ret;

		if(is_string($params) || is_numeric($params)){
			$params = "$params";
			if(strpos($params, ',') !== false){
				$sp = explode(',', $params);
				$sp_err = array_filter($sp, function($v){
					return !is_numeric(trim($v));
				});
				if(!empty($sp_err)){
					throw new \PDOException("主键条件参数 {$params} 中有非数字");
				}
				$ret = array($pk => $sp);
			} else {
				$ret = array($pk => $params);
			}
		} else {
			$numeric = false;
			$string = false;
			foreach($params as $k=>$v){
				if(is_numeric($k)){
					$numeric = true;
				}else {
					$string = true;
				}
			}
			if($numeric == true && $string == true){
				throw new \PDOException("条件参数 ". var_export($params, true) . " 中既有数字键值也有字符串键值");
			}
			if($string){
				$ret = $params;
			} else {
				$ret = array($pk => $params);
			}
		}
		return $ret;
	}
}