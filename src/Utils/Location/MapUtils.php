<?php
namespace Whilegit\Utils\Location;

class MapUtils{
	/**
	 * 判断一个字符串是否是经纬度
	 * @param unknown $address
	 * @return NULL|array  拆分出lng和lat组成索引数组返回
	 * @example '121.331933,28.579948'  =>   array('121.331933', '28.579948')
	 * @example '浙江省台州市路桥区'      =>   null 
	 */
	public static function isLatLngString($address){
		$loc = explode(',',$address);
		return (count($loc) == 2 && is_numeric($loc[0]) && is_numeric($loc[0])) ? $loc : null;
	}
}
