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


	
	public static function createLinkString($para, $sort, $encode) {
		
		if($para == NULL || !is_array($para))
			return "";
	
			$linkString = "";
			if ($sort) {
				$para = argSort ( $para );
			}
			while ( list ( $key, $value ) = each ( $para ) ) {
				if ($encode) {
					$value = urlencode ( $value );
				}
				$linkString .= $key . "=" . $value . "&";
			}
			// 去掉最后一个&字符
			$linkString = substr ( $linkString, 0, count ( $linkString ) - 2 );
	
			return $linkString;
	}
	
	/**
	 * 将key1=val1&key2=val2...样式的字符串解析成数组，使用了内置函数parse_str()，主要是为解决base64编码中加号(+)的问题。<br>
	 * parse_str将输入字符串的加号解析成空格，使键值中存在的+号解析成空格，损坏base64的完整性。<br>
	 * 本函数特别适合解析银联支付接口的返回
	 * @param string $str            待解析的字符串
	 * @param array  $base64_fields  可能存在base64编码的key
	*/
	public static function parse_str($str, $base64_fields = array()){
		$result = array();
		parse_str($str, $result);
		foreach($base64_fields as $field){
			if(isset($result[$field])){
				$tmpary = explode("\r\n", $result[$field]);
				foreach($tmpary as &$line){
					//过滤认证字符串的头部和尾部
					if($line != '-----BEGIN CERTIFICATE-----' && $line != '-----END CERTIFICATE-----'){
						$line = str_replace(' ', '+', $line);
					}
				}
				unset($line);
				$result[$field] = implode("\r\n", $tmpary);
			}
		}
		return $result;
	}

	/**
	 * 生成一个随机数组
	 * @param int len  长度
	 * @param int $min 最小值
	 * @param int $max 最大值
	 * @return array[int]
	 */
	public static function &rand_matrix($len, $min, $max){
		$matrix = array();
		for($i = 0; $i<$len; $i++){
			$matrix[] = mt_rand($min, $max);
		}
		return $matrix;
	}
}
