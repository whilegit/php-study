<?php
namespace Whilegit\Utils;

class Gd{
	
	/**
	 * 颜色转换
	 * @param string|int|array $param
	 * @param boolean $htmlout 为true时输出 #ff0080样式的字符串
	 * @return int|array
	 * @example <pre>
	 *     Gd::rgb(16777088);        //out: 	array (0 => 255,1 => 255, 2 => 128)
	 *     Gd::rgb(16777088, true);  //out: 	#ffff80
	 *     Gd::rgb(array (0 => 255,1 => 255, 2 => 128));		//out: 	16777088
	 *     Gd::rgb(array (0 => 255,1 => 255, 2 => 128), true);  //out: 	#ffff80 </pre>
	 */
	public static function rgb($param, $htmlout = false){
		$ret = null;
		if(is_numeric($param) || is_string($param)){
			if(is_numeric($param)) {
				$color = intval($param);
			} else {
				if($param{0} == '#')   $param = substr($param, 1);
				$color = intval(base_convert($param, 16, 10));
			}
			
			if($htmlout == false){
				$ret =  array(
						($color >> 16) & 0xff,
						($color >> 8) & 0xff,
						($color) & 0xff);
			} else {
				$ret = sprintf("#%6x", $color);
			}
		} else if(is_array($param)){
			$color = ($param[0] << 16) + ($param[1] << 8) + $param[2];
			$ret = $htmlout == false ? $color : sprintf("#%6x", $color);
		}
		return $ret;
	}
}