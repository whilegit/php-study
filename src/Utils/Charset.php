<?php
namespace Utils;

class Charset{

	/**
	 * unicode编码转utf-8字符串
	 * @param int|String  $code            unicode编码值(int)或其十六进制字面表示，string时为'\u4E03'或'\x4E03'或'\u4E03\x03A6'样式
	 * @param boolean     $return_string   是否返回整型
	 * @return string|int 返回utf-8字符或其整型编码表示
	 * @desc <pre>
     *   0000 ~   007F: 0XXXXXXX
     *   0080 ~   07FF: 110XXXXX 10XXXXXX
     *   0800 ~   FFFF: 1110XXXX 10XXXXXX 10XXXXXX 
     *   10000 ~ 1FFFFF: 11110XXX 10XXXXXX 10XXXXXX 10XXXXXX 
	 * @example 
	 *    $str = Charset::utf8('\u4E03\u03A6');  //返回    '七Φ'
	 *    $str = Charset::utf8('\x4E03');        //返回    '七'
	 *    $str = Charset::utf8(0x4E03, false); //返回    14989443(即0xE4B883)  
	 *    //注意：多个unicode转义表达式时返回整型时不确定的。
	 *    4E03(丁)               ==>   E4B883
	 *    0100 1110 0000 0011          (1110)0100 (10)111000 (10)00 0011
	 *    03A6(Φ)                ==>   CEA6
	 *    011 1010 0110                (110)0 1110 (10)10 0110
	 */
	public static function unicode2utf8($code, $return_string = true) {
		$bytes = array();
		if(is_string($code)){
			$ret= preg_replace_callback('/[\\\\][ux]([0-9a-fA-F]{4})/i', function($matches){
				return Charset::unicode2utf8(hexdec($matches[1]), true);    //参数为(int,true)的方式调用本接口
			}, $code);
			return $ret;
		}else{
			//分解unicode编码序号，按utf-8的编码规则装入$bytes中
			if ($code > 0x10000){      //0x10000 ~ 0x1FFFFF, 四字节utf-8
				$bytes[] = 0xF0 | (($code & 0x1C0000) >> 18);
				$bytes[] = 0x80 | (($code & 0x3F000) >> 12);
				$bytes[] = 0x80 | (($code & 0xFC0) >> 6);
				$bytes[] = 0x80 | ($code & 0x3F);
			}else if ($code > 0x800){  //0x0800 ~ 0xFFFF，三字节utf-8
				$bytes[] = 0xE0 | (($code & 0xF000) >> 12);
				$bytes[] = 0x80 | (($code & 0xFC0) >> 6);
				$bytes[] = 0x80 | ($code & 0x3F);
			}else if ($code > 0x80){   //0x0080 ~ 0x07FF,两字节utf-8
				$bytes[] = 0xC0 | (($code & 0x7C0) >> 6);
				$bytes[] = 0x80 | ($code & 0x3F);
			}else{                     // ASCII码，单字节utf-8
				$bytes[] = $code;
			}
		}
		
		$char = '';
		$value = 0;
		foreach($bytes as $b){
			$value = ($value << 8) + $b;   //生成utf-8的编码序号
			$char .= chr($b);              //组装成单字符的string
		}
		if($return_string) return $char;
		else return $value;
	}
}