<?php
namespace Whilegit\Utils;
use SimpleXMLElement;

class IArray{
	
	/**
	 * 简单关联数组转xml (递归)
	 * @param array  $arr 
	 * @param number $level 递归级别， 为1时头尾加xml头部   
	 * @return string
	 * @example <pre>
	 * 	$params = array(
	 *     'div' => '项目表',
	 *     'p'=>array(
	 *          array('tag'=>'span', 'width'=>'24', 'title'=>'项目1'),   //tag转化成<tag>...</tag>
	 *		    array('tag'=>'span', 'width'=>'24', 'title'=>'项目2'),
	 *      )
     *  );
	 * </pre>
	 */
	public static function toXml($arr, $level = 1) {
		$xml = '';
		foreach ($arr as $tag => $val) {
			if(!is_string($tag) && is_array($val) && key_exists('tag', $val)){
				$tag = $val['tag'];
				unset($val['tag']);
			}
			$pad = str_pad('', $level * 4,' ');
			if (!is_array($val)) {
				$xml .= "{$pad}<{$tag}>" . (!is_numeric($val) ? '<![CDATA[' : '') . $val . (!is_numeric($val) ? ']]>' : '') . "</{$tag}>\r\n";
			} else {
				$xml .= "{$pad}<{$tag}>\r\n" . self::toxml($val, $level + 1) . "{$pad}</{$tag}>\r\n";
			}
		}
		$xml = preg_replace("/[\x01-\x08\x0b-\x0c\x0e-\x1f]/", ' ', $xml);
		
		return $level == 1 ? "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n<ary>\r\n{$xml}</ary>\r\n" : $xml;
	}
	
	
	/**
	 * 简单xml转关联数组(使用SimpleXMLElement类)
	 * @param string $xml
	 * @return array
	 */
	public static function parseXml($xml) {
		$ary = array();
		if (empty($xml))  return $ret;
		if (preg_match('/(\<\!DOCTYPE|\<\!ENTITY)/i', $xml)) {
			return $ret;
		}
		libxml_disable_entity_loader(true);
		$obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		if($obj instanceof SimpleXMLElement) {
			$ary = json_decode(json_encode($obj), true);
		}
		return $ary;
	}


	/**
	 * 对数组重新命名键名
	 * @param array $raw
	 * @param string $keyfield  键名
	 * @return array
	 */
	public static function rekey(&$raw, $keyfield){
		$rs = array();
		foreach ($raw as $key => &$row) {
			if (isset($row[$keyfield])) {
				$rs[$row[$keyfield]] = $row;
			} else {
				$rs[] = $row;
			}
		}
		return $rs;
	}
}