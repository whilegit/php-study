<?php
namespace Utils;
defined('NOW_TIME') or define('NOW_TIME', time());
class Misc{
	
	/**
	 * 获得用户的真实IP地址
	 *
	 * @access  public
	 * @return  string
	 */
	public static function real_ip(){
		static $realip = NULL;
		if ($realip !== NULL){
			return $realip;
		}
	
		if (isset($_SERVER)){
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
				$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				/* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
				foreach ($arr AS $ip){
					$ip = trim($ip);
					if ($ip != 'unknown'){
						$realip = $ip;
						break;
					}
				}
			} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
				$realip = $_SERVER['HTTP_CLIENT_IP'];
			} else {
				if (isset($_SERVER['REMOTE_ADDR'])){
					$realip = $_SERVER['REMOTE_ADDR'];
				} else {
					$realip = '0.0.0.0';
				}
			}
		} else {
			if (getenv('HTTP_X_FORWARDED_FOR')){
				$realip = getenv('HTTP_X_FORWARDED_FOR');
			} elseif (getenv('HTTP_CLIENT_IP')) {
				$realip = getenv('HTTP_CLIENT_IP');
			} else {
				$realip = getenv('REMOTE_ADDR');
			}
		}
	
		preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
		$realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
	
		return $realip;
	}
	
	/**
	 * 获取随机字符串(或纯数字字符串)
	 * @param number $length
	 * @param boolean $numeric true为数字，false为字符串
	 * @param number $is10x  如果$numeric是true, $is10x为true时随机数样式为100000~999999，$is10x为false时为000000~999999
	 * @return string
	 */
	public static function random($length = 6, $numeric = false, $is10x = false) {
		if ($numeric) {
			$start = $is10x ? pow(10, $length - 1) : 0;
			$hash = sprintf('%0' . $length . 'd', mt_rand($start, pow(10, $length) - 1));
		} else {
			$hash = '';
			$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
			$max = strlen($chars) - 1;
			for ($i = 0; $i < $length; $i++) {
				$hash .= $chars[mt_rand(0, $max)];
			}
		}
		return $hash;
	}
	
	/**
	 * 检测手机号码格式
	 * @param string $mobile
	 * @return boolean
	 */
	public static function mobile_check($mobile){
		return (!empty($mobile) && strlen($mobile) == 11 && preg_match('/^1[345789][0-9]{9}$/', $mobile));
	}
	
	/**
	 * 隐藏手机号码的中间部分 18912345678 --> 189****5678
	 * @param string $mobile
	 * @return string
	 */
	public static function mobile_hide($mobile){
		if(!empty($mobile) && self::check_mobile($mobile)){
			$mobile{3} = $mobile{4} = $mobile{5} = $mobile{6} = '*';
		}
		return $mobile;
	}
	
	/**
	 * 解序列化(自定义)
	 * @param string $value 序列化字符串
	 * @return null|string|mixed|false
	 * @desc 参数为空时返回空字符串， 参数非序列化字符串时原样返回， 参数正常时返回解序列化结果或者false(失败)
	 */
	public static function iunserializer($value) {
		if (empty($value)) {
			return '';
		}
		if (!self::is_serialized($value)) {
			return $value;
		}
		$result = unserialize($value);
		if ($result === false) {
			//第一次解序列化失败，尝试将s:2:"abc";字样的长度修正后再次尝试
			$temp = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $value);
			return unserialize($temp);
		}
		return $result;
	}
	
	/**
	 * 判定是否是序列化字符串
	 * @param string $data
	 * @param boolean $strict
	 * @return boolean
	 */
	protected static function is_serialized($data, $strict = true) {
		if (!is_string($data)) {
			return false;
		}
		$data = trim($data);
		if ('N;' == $data) return true;       // N;是null的序列化字符串
		if (strlen($data) < 4) return false;  // 不小于4个字节
		if (':' !== $data[1]) return false;   // $data[1]必须是:
		if ($strict) {
			$lastc = substr($data, -1);
			if (';' !== $lastc && '}' !== $lastc) return false;	 //最后一个字节必须是;或}
		} else {
			$semicolon = strpos($data, ';');
			$brace = strpos($data, '}');
			if (false === $semicolon && false === $brace)	return false;  // 字符串中不包含;和}的都不是有效的序列化字符串
			if (false !== $semicolon && $semicolon < 3)     return false;  // 有分号，但分号的位置必须是3及3以上(c风格)
			if (false !== $brace && $brace < 4)             return false;  // 有}，但位置必须是4及4以上(c风格)
		}
		//分析字符串的第一部分，格式是否ok，其它部分不再分析，都交给内置函数unserialize()
		$token = $data[0];
		switch ($token) {
			case 's' :
				if ($strict) {
					if ('"' !== substr($data, -2, 1)) {
						return false;
					}
				} elseif (false === strpos($data, '"')) {
					return false;
				}
			case 'a' :
			case 'O' :
				return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
			case 'b' :
			case 'i' :
			case 'd' :
				$end = $strict ? '$' : '';
				return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
		}
		return false;
	}

	/**
	 * 判定版本大小(自定义)，部分调用内置函数 version_compare()
	 * @param string $version1
	 * @param string $version2
	 * @return int(1/0/-1)  $version1>$version2返回1，相等返回0，-1和1相反
	 */
	public static function ver_compare($version1, $version2) {
		$version1 = str_replace('.', '', $version1);
		$version2 = str_replace('.', '', $version2);
		$oldLength = strlen($version1);
		$newLength = strlen($version2);
		if(is_numeric($version1) && is_numeric($version2)) {
			if ($oldLength > $newLength) {
				$version2 .= str_repeat('0', $oldLength - $newLength);
			}
			if ($newLength > $oldLength) {
				$version1 .= str_repeat('0', $newLength - $oldLength);
			}
			$version1 = intval($version1);
			$version2 = intval($version2);
		}
		return version_compare($version1, $version2);
	}

	
	/**
	 * 发送cookie | 返回cookie参数 | 设置cookie参数
	 * @param null|array|string $config_or_key 如null则返回self::$COOKIE_CONFIG，如array则设置self::$COOKIE_CONFIG，如string则准备写cookie
	 * @param string $value
	 * @param number $expire     //过期秒数,如0则在浏览器关闭后清除
	 * @param string $httponly   //为true时，此cookie对前端js不可见，仅用于http协议
	 * @return array|boolean
	 */
	public static function cookie($config_or_key = null, $value, $expire = 0, $httponly = false){
		static $COOKIE_CONFIG = array(
				'pre' => '',    //key的前缀
				'path' => '/',  //cookie路径
				'domain' => ''  //域
		);
		if($config_or_key == null) {
			//返回cookie参数
			return $COOKIE_CONFIG;
		} else if(is_array($config_or_key)) {
			//设置cookie参数
			foreach($config_or_key as $k=>$v){
				$COOKIE_CONFIG[$k] = $v;
			}
			return $COOKIE_CONFIG;
		} else {
			//发送cookie
			if(empty($COOKIE_CONFIG['domain'])){
				$COOKIE_CONFIG['domain'] = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
			}
			$expire = $expire != 0 ? (NOW_TIME + $expire) : 0;
			$secure = self::ishttps() ? 1 : 0;
			return setcookie($COOKIE_CONFIG['pre'] . $key, $value, $expire,
					$COOKIE_CONFIG['path'], $COOKIE_CONFIG['domain'], $secure, $httponly);
		}
	}
	
	/**
	 * 获取本次请求的来源(如从外链进来，转成本站首页进来)
	 * @param string $siteroot 本站的入口，如 http://localhost/
	 * @return string
	 */
	public static function referer($siteroot = '') {
		static $referer = ''; 
		static $_siteroot = '';
		if(!empty($siteroot)) $_siteroot = $siteroot;
		if(!empty($referer)) return $referer;
		if(empty($_SERVER['HTTP_REFERER']))  $_SERVER['HTTP_REFERER'] = '';
		$referer = !empty($_REQUEST['referer']) ? $_REQUEST['referer'] : $_SERVER['HTTP_REFERER'];
		$referer = rtrim($referer, '?');
	
		$referer = str_replace('&amp;', '&', $referer);
		$reurl = parse_url($referer);
	
		//外站链进来时，referer设为本站的主页
		if (!empty($reurl['host']) && 
			!in_array($reurl['host'], array($_SERVER['HTTP_HOST'], 'www.' . $_SERVER['HTTP_HOST'])) && 
			!in_array($_SERVER['HTTP_HOST'], array($reurl['host'], 'www.' . $reurl['host']))) {
			$referer = $_siteroot;
		} elseif (empty($reurl['host'])) {
			$referer = $_siteroot . './' . $referer;
		}
		$referer = strip_tags($referer);
		return $referer;
	}
	
	/**
	 * 是否是ajax请求
	 * @return boolean
	 */
	public static function isajax(){
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
					 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}
	
	/**
	 * 是否是ssl连接
	 * @return boolean
	 */
	public static function ishttps(){
		return  $_SERVER['SERVER_PORT'] == 443 || (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ? true : false;
	}
	
	/**
	 * 是否是post请求
	 * @return boolean
	 */
	public static function ispost(){
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	}
	
	
	/**
	 * 生成token或检验token，以防止表单重复提交(请用本函数前，请确保$_SESSION可以正常使用)
	 * @param string|null $field 如为string时表示表单域，用于检验是否重复上传；null表示生成一个表单唯一码
	 * @param string      $value 仅当$field为string时有意义，表示该表单域的值；如传空，默认从$_POST[$field]中取
	 * @return string|true  当为了获取表单唯一码时返回string; 当检验时，返回boolean
	 */
	public static function token($field = null, $value = null ) {
		if(empty($field)){
			if(!empty($_SESSION['token'])) {
				$count  = count($_SESSION['token']) - 5;   //最多维持5个token
				asort($_SESSION['token']);                 //升序，最早的token失被unset
				foreach($_SESSION['token'] as $k => $v) {
					if(NOW_TIME - $v > 300 || $count > 0) {  //如果过时或过多则删除
						unset($_SESSION['token'][$k]);
						$count--;
					}
				}
			}
			$key = self::random(8);
			$_SESSION['token'][$key] = NOW_TIME;
			return $key;
		} else {
			if (empty($value) && empty($_POST[$field])) return false;
			$value = !empty($value) ? $value : $_POST[$field];
			if(self::isajax() && empty($_SESSION['token'][$value])) {  //表单唯一码错误
				return false;  
			} else {
				unset($_SESSION['token'][$value]);
				return true;
			}
		}
	}
	
	/**
	 * 增加composer的psr4命名空间
	 * @param string $prefix   命名空间
	 * @param string $path     vendor下的子目录或者其它目录(绝对路径或相对路径)
	 * @param bool $subvendor  是否是vendor目录的子目录
	 * @desc 调用本函数前先须要在vendor/autoload.php中增加下面一行，否则不能获取到Composer的自动加载器<br/>
	 *       defined('COMPOSER_VENDOR_DIR') or define('COMPOSER_VENDOR_DIR', __DIR__);<br/>
	 */
	public static function composer_addPsr4($prefix, $path, $subvendor = true){
		$WHILEGIT_UTILS_COMPOSER_VENDOR_DIR = __DIR__.'/../../vendor/';
		static $composer_autoloader = null;
		if($composer_autoloader == null) {
			$composer_autoloader = require("$WHILEGIT_UTILS_COMPOSER_VENDOR_DIR/autoload.php");
		}
		
		$composer_autoloader->addPsr4($prefix, $subvendor ? $WHILEGIT_UTILS_COMPOSER_VENDOR_DIR ."/$path" : $path);
	}
}