<?php
namespace Utils;

class IString{
	/**
	 * 是否存在子字符串
	 * @param string $string
	 * @param string $find
	 * @return boolean
	 */
	public static function strexists($string, $find) {
		return !(strpos($string, $find) === FALSE);
	}

	/**
	 * 计算字符串的字节数，如装有扩展mb_string，将使用mb_strlen替换
	 * @param string $string    要测量长度的字符串
	 * @param string $charset   可以是utf8或者gbk
	 * @return false|number     基本不会返回false
	 */
	public static function istrlen($string, $charset = 'utf8') {
		if (strtolower($charset) == 'gbk') {
			$charset = 'gbk';
		} else {
			$charset = 'utf8';
		}
		if (function_exists('mb_strlen')) {
			return mb_strlen($string, $charset);
		} else {
			$n = $noc = 0;
			$strlen = strlen($string);
			if ($charset == 'utf8') {
				while ($n < $strlen) {
					$t = ord($string[$n]);
					if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
						$n++;
						$noc++;
					} 
					elseif (194 <= $t && $t <= 223)  { $n += 2;	$noc++;}
					elseif (224 <= $t && $t <= 239)  { $n += 3;	$noc++;}
					elseif (240 <= $t && $t <= 247)  { $n += 4;	$noc++;}
					elseif (248 <= $t && $t <= 251)  { $n += 5;	$noc++;}
					elseif ($t == 252 || $t == 253)  { $n += 6;	$noc++;}
					else { $n++;}
				}
			} else {
				while ($n < $strlen) {
					$t = ord($string[$n]);
					if ($t > 127) { $n += 2;	$noc++; }
					else          {	$n++;		$noc++;	}
				}
	
			}
			return $noc;
		}
	}
}