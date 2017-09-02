<?php
namespace Whilegit\Utils;

class IXml{
	function xml2array($xml) {
		if (empty($xml)) {
			return array();
		}
		$result = array();
		$xmlobj = isimplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		if($xmlobj instanceof SimpleXMLElement) {
			$result = json_decode(json_encode($xmlobj), true);
			if (is_array($result)) {
				return $result;
			} else {
				return '';
			}
		} else {
			return $result;
		}
	}
}