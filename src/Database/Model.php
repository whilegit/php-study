<?php
namespace Whilegit\Database;
use Whilegit\Database\Basic\IPdo;
use Whilegit\Database\Basic\Pdoutils;

class Model implements \ArrayAccess{
	protected static $iPdo = null;
	
	protected $data = array();
	protected $redirect = array();      //关联数组对象缓存，key是字段名，value是本字段关联着的对象
	protected $changeFields = array();
	protected $class; // 当前类名称
	protected $name;  // 当前模型名称($class的最后部分)
	
	//以下四个变量，必须要在子类的定义中抄一遍，即使不赋值。原因是Model应用静态变量的迟绑定技术(参考self和static关键字的区别)
	protected static $table;          // 数据表名称，子类若不告知，默认为模型名称的小写化
	protected static $pk = "id";      // 主键名称,子类若不告知，查表的主键
	protected static $fields = null;  // 表的字段，子类若不告知，查表
	protected static $redirect_map = array();
	
	/**
	 * Model模型初始化函数，主要是设置 \Database\Basic\IPdo实例。此函数是全部模型存在的数据库基础，应第一时间以Model名义调用。
	 * @param IPdo $iPdo
	 */
	public static final function model_init(IPdo $iPdo){
		self::$iPdo = $iPdo;
		spl_autoload_register('self::autoloader', true, false);
	}
	
	/**
	 * 动态模型定义自动加载器
	 * @param string $class 要加载的完整类名(本函数由系统调用)
	 * @desc <pre> 
	 * 				use Whilegit\Model\Virtual\AddonArticle; <br />
	 * 				AddonArticle::redirectMap('typeid','\Whilegit\Model\Virtual\Mtypes'); <br/>
	 *             $model = AddonArticle::get(1);//使用并未定的模型类后，就像已经定义过一样</pre>
	 */
	public static final function autoloader($class){
		$class_ary = explode('\\', $class, 4);
		if(count($class_ary) == 4 && $class_ary[0] == 'Whilegit' && $class_ary[1] == 'Model' && $class_ary[2] == 'Virtual'){
			$str = "namespace Whilegit\\Model\\Virtual;
					class {$class_ary[3]} extends \Whilegit\Database\Model{ " . '
						protected static $table = "";
						protected static $pk = "";             // 主键名称
						protected static $fields = null;
						protected static $redirect_map = array();
					}';
			eval($str);
		}
	}
	
	
	/**
	 * 构造函数
	 * @param array $data 对象元素的初始化值
	 */
	public function __construct($data = array()){
		static::submodel_init();   //确保已初始化三个静态变量($table,$pk,$fields)
		
		if(!empty($data)) $this->data = $data;
		//初始化$calss和$name成员变量
		$this->class = get_class($this);
		$name = str_replace('\\', '/', $this->class);
		$this->name = basename($name);
	}
	
	/**
	 * 模型子类的初始化函数，目的是初始化三个静态变量($table,$pk,$fields)
	 * @throws \Exception
	 * @desc 从子类调用本类的静态变量，必须要在其实现中先行调用本静态方法，否则三个静态变量可能未初始化。<br/>
	 */
	protected static function submodel_init(){
		if(self::$iPdo == null){
			throw new \Exception('Model::$iPdo not initialized, please invocate Model::model_init($iPdo) first;');
		}
		if(empty(static::$table) || empty(static::$fields) || empty(static::$pk)){
			$class = str_replace('\\', '/', get_called_class());
			static::$table = basename(strtolower($class));
			static::$fields = self::$iPdo->allfields(static::$table); 
			if(empty(static::$fields)){
				throw new \Exception('Fields empty. tablename=' . static::$table);
			}
			//跟据static::$fields推算主键
			foreach(static::$fields as &$f){
				if($f['Key'] == 'PRI') {
					static::$pk = $f['Field'];
					break;
				}
			}
			unset($f);
			if(empty(static::$pk)){
				throw new \Exception('primary field does not exists. ');
			}
		}
		if(static::$redirect_map == null){
			static::$redirect_map = array();
		}
	}
	
	/**
	 * 从数据库中获取一个对象(以子类的名义静态调用)
	 * @param string|int|array $pk 如为string或int, 则是主键条件；如为array，则是查询条件
	 * @return Object|null
	 */
	public static function get($params){
		static::submodel_init();
		$params = PdoUtils::params($params, static::$pk);
		$data = self::$iPdo->get(static::$table, $params);
		if(!empty($data)) {
			$class = get_called_class();
			return new $class($data);
		}
		return null;
	}
	
	/**
	 * 罗列记录
	 * @param array $params     条件
	 * @param string $keyfield  数组的关键字
	 * @return array 子类对象
	 */
	public static function ls($params = array(), $fields = array(), $keyfield = ''){
		static::submodel_init();
		$params = PdoUtils::params($params, static::$pk);
		$list = self::$iPdo->getall(static::$table, $params, $fields, $keyfield);
		$ret = array();
		foreach($list as $k=>&$v){
			$ret[$k] = new static($v);
		}
		return $ret;
	}
	
	/**
	 * 记算总和
	 * @param string $field  需要计算的字段
	 * @param array $params  条件(如非数组，则当成主键条件)
	 * @return number        
	 * @example $list = Contract::ls(array('id >= '=>21), '*','contract_number');
	 */
	public static function sum($field, $params = array()){
		static::submodel_init();
		$params = PdoUtils::params($params, static::$pk);
		$sum = self::$iPdo->getsum(static::$table, $field, $params);
		return !empty($sum) ? floatval($sum) : 0.00;
	}
	
	/**
	 * 统计记录数量
	 * @param array $params
	 * @return number
	 * @example $list = Contract::count(array('id >= '=>21));
	 */
	public static function count($params = array()){
		static::submodel_init();
		$params = PdoUtils::params($params, static::$pk);
		$count = self::$iPdo->getcount(static::$table, $params);
		return intval($count);
	}
	
	/**
	 * 保存或更新数据
	 * @throws \Exception
	 */
	public function save($forceInsert = false){
		$pk = static::$pk;
		//无主键存在，则进行插入操作
		if(empty($this->data[$pk]) || $forceInsert == true){
		    /*
			//将无更新的元素剔除，不写进最终sql语句中
			$data = array_filter($this->data, function($key){return in_array($key, $this->changeFields);}, ARRAY_FILTER_USE_KEY );
			if(empty($data)){
				//无实质性的数据插入，直接返回
				return;
			}*/
			$flag = self::$iPdo->insert(static::$table, $this->data);
			if($flag !== 1){
				//插入失败
				throw new \Exception("Insertion of a record of {$this->class} failed");
			}
			//更新主键
			$this->data[$pk] = self::$iPdo->insertid();
			//清空变量字段列表
			$this->changeFields = array();
		} else {
			$this->update();
		}
	}
	
	/**
	 * 更新记录
	 * @throws \Exception
	 */
	public function update(){
		//无实质性数据更新，直接返回
		if(empty($this->changeFields)) return;
		//将无更新的元素剔除，不写进最终sql语句中
		$data = array_filter($this->data, function($key){return in_array($key, $this->changeFields);}, ARRAY_FILTER_USE_KEY );
		$flag = self::$iPdo->update(static::$table, $data, array(static::$pk => $this->data[static::$pk]));
		if($flag === false){
			//更新失败
			throw new \Exception("Update of a record of {$this->class} failed");
		}
		//清空变量更新列表
		$this->changeFields = array();
	}
	
	/**
	 * 先更新若失败，则改成插入(启用 mysql的replace into语法)
	 * @throws \Exception
	 */
	public function updateOrSave(){
	    self::$iPdo->insert(static::$table, $this->data, true);
	}
	
	/**
	 * 删除记录
	 * @throws \Exception
	 */
	public function delete(){
		//如无主键字段存在，则直接返回，无法删除。
		if(empty($this->data[$pk])) {
			throw new \Exception("Deletion of a record of {$this->class} failed: no pk condition!");
			return;
		}
		$flag = self::$iPdo->delete(static::$table, array(static::$pk => $this->data[static::$pk]));
		if($flag !== 1){
			throw new \Exception("Deletion of a record of {$this->class} failed");
		}
	}
	
	/**
	 * 重新从数据库里拉取全部字段数据，并放弃原来相同的字段
	 * @throws \Exception
	 */
	public function pull(){
		if(empty($this->data[static::$pk])) {
			throw new \Exception("the Object is not saved yet");
		}
		$obj = call_user_func(array($this->class, 'get'), $this->data[static::$pk]);
		if($obj == null){
			throw new \Exception("the Object doesnot exists in db");
		}
		$this->data = array_merge($this->data, $obj->data);
		return $this;
	}
	

	/**
	 * 设置数据字段
	 * @param string $field
	 * @param mixed $value
	 */
	public function setAttr($field, $value){
		if(!empty($this->data[$field]) && $this->data[$field] === $value) return;
		
		$this->data[$field] = $value;
		//增加字段变更记录
		if(key_exists($field, static::$fields) && !in_array($field, $this->changeFields)) {
			$this->changeFields[] = $field;
		}
		$this->removeRedirect($field);
		return $this;
	}
	
	/**
	 * 读取数据字段
	 * @param string  $field
	 * @throws \InvalidArgumentException
	 * @return mixed
	 */
	public function getAttr($field){
		if(isset($this->data[$field]) == false) {
			if(key_exists($field, static::$fields) && !empty($this->data[static::$pk])) {
				//如果此字段存在于数据表字段中，并且设置了主键条件，则重新到数据库中pull一下，并再一次调用本方法
				$this->pull();
				$this->removeRedirect();
				return $this->getAttr($field);
			} else{
				throw new \InvalidArgumentException("key of '$field' not exists");
			}
		}
		return $this->data[$field];
	}
	
	/**
	 * 查看本字段关联着的其他模型
	 * @param string $field
	 * @throws \InvalidArgumentException
	 * @return Model|NULL
	 */
	public function redirect($field){
		//获取本字段关联着的模型
		if(! key_exists($field, static::$redirect_map)){
			//未设置本字段的关联对象，抛出异常
			throw new \InvalidArgumentException("the field {$field} isnot in the redirect_map");
		}
		//已有该字段的关联对象，直接返回
		if(isset($this->redirect[$field])) {
			return $this->redirect[$field];
		}
		//实际查询关联对象
		$class = static::$redirect_map[$field];
		$obj = call_user_func(array($class, 'get'), $this->data[$field]);
		if($obj != null){
			$this->redirect[$field] = $obj;
			return $obj;
		} else {
			return null;
		}
	}
	
	/**
	 * 删除关联着的对象
	 * @param string|null $field  null时全部清空
	 */
	public function removeRedirect($field = null){
		if($field === null){
			$this->redirect = array();
		}else{
			if(isset($this->redirect[$field])){
				unset($this->redirect[$field]);
			}
		}
		return $this;
	}
	
	public static function redirectMap($field, $class){
		static::submodel_init();
		if(!key_exists($field, static::$fields)){
			throw new \InvalidArgumentException("field does not exists");
		}
		if(class_exists($class) == false){
			$class = "\\Whilegit\\Model\\Virtual\\" . $class;
		}
		static::$redirect_map[$field] = $class;
	}
	
	//以下两个魔术方法暴露$data的元素为成员变量
	public function __get($name){	return $this->getAttr($name);	}
	public function __set($name , $value){	$this->setAttr($name, $value);	}
	
	//以下四个方法为\ArrayAccess的接口方法，让类对象数组化的访问
	public function offsetExists ( $offset ){	return !isset($this->data[$offset]);	}
	public function offsetGet ( $offset ){ return $this->getAttr($offset); }
	public function offsetSet ( $offset , $value ){	$this->setAttr($offset, $value);	}
	public function offsetUnset ( $offset ){	unset($this->data[$offset]);	}
}