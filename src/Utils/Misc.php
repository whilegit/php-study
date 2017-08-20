<?php
namespace Utils;

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
}