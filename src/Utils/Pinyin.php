<?php
namespace Whilegit\Utils;
/**
 * 拼音类
 * @author Administrator
 * @desc <pre>
 * 		gb2312双字节编码方案： 起始A1A2~F7FE，低字节总是小不于A0，高字节总不小于A1。从B0A1~F7FE为汉字编码，拼音相同的汉字总是分在同一分区，因些只要构造拼音分区的边界表，即能汉字转拼音了。
 *      gbk编码兼容gb2312，起始0x8140~0xFEFF,低字节总是不小于0x40，高字节不小于0x81
 *      
 *      示例： Pinyin::get('中华人民共和国', true); 
 * </pre>
 */
class Pinyin{
	
	//gb2312编码区的一级汉字区(第16~55区，共3755个汉字)，按拼音排序。gb2312的低数总是比0xA0大，高位范围为[0xB0,0xD7]
	protected static $gb2312_class1_boundary = null;
	//gb2312编码区的二级汉字节(第56~87区，共3008个汉字)，按部首/笔画排序
	protected static $gb2312_class2_map = null;
	//非gb2312的gbk汉字
	protected static $gbk_map_without_gb2312 = null;
	
	/**
	 * 汉字转拼音
	 * @param string $str      待转换的汉字
	 * @param string $whole    是否全拼
	 * @param string $charset  待转换汉字的编码方式
	 * @return mixed
	 */
	public static function get($str, $whole = true, $charset = 'utf-8'){
		$charset = strtoupper($charset);
		//如果是utf-8，则转成gb2312编码
		if ($charset != 'GBK') {
			$str = mb_convert_encoding($str, 'GBK', $charset);
		}
		$result = '';
		$len = strlen($str);
		$i = 0;
		while ($i < $len){
			$code = ord($str{$i++});
			if ($code >= 0x81 || $code == 0x00){             //扩展到0x81以上是为了扩展到gbk，0x00表示ASCII码
				$q = ord($str{$i++});
				$code = ($code * 256) + $q;
			}
			$m = self::match($code);
			if(empty($m)) continue;
			$result .= $whole ? $m : $m{0};
		}
		return preg_replace('/[^a-z0-9]/', '', strtolower($result));
	}
	
	
	/**
	 * 编码匹配拼音
	 * @param int $code  范围[B0A1,F7FE]
	 * @return string
	 */
	protected static function match($code){
		$ret = '';
		if ((0 < $code) && ($code < 80)){                   //普通ascii码
			$ret = chr($code);
		} else {
			$h = $code >> 8;
			$l = $code & 0x00FF;
			if($h >= 0xB0 && $h <= 0xD7 && $l >= 0xA0 ){          //gb2312的一区汉字
				if(self::$gb2312_class1_boundary == null){
					self::$gb2312_class1_boundary = require(__DIR__ . '/../../static/pinyin/gb2312_class1_boundary.dat');
				}
				//此算法比常见算法性能要好
				for($i = $code; $i>=45217; $i--){
					if(isset(self::$gb2312_class1_boundary[$i])){
						$ret = self::$gb2312_class1_boundary[$i];
						break;
					}
				}
			} else if($h >= 0xD8 && $h <= 0xF7 && $l >= 0xA0){  //gb2312的二区汉字
				if(self::$gb2312_class2_map == null){
					self::$gb2312_class2_map = require(__DIR__ . '/../../static/pinyin/gb2312_class2_map.dat');
				}
				if(isset(self::$gb2312_class2_map[$code])) {
					$ret = $ret = self::$gb2312_class2_map[$code];
				}
			} else if($h >= 0x81) { 							//非gb2312的gbk汉字
				if(self::$gbk_map_without_gb2312 == null){
					self::$gbk_map_without_gb2312 = require(__DIR__ . '/../../static/pinyin/gbk_map_without_gb2312.dat');
				}
				if(isset(self::$gbk_map_without_gb2312[$code])){
					$ret = self::$gbk_map_without_gb2312[$code];
				}
			} else {											
				//无法识别
			}
		}
		return $ret;
	}
}