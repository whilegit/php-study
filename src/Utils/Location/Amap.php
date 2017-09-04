<?php
namespace Whilegit\Utils\Location;
use Whilegit\Utils\Comm;
/**
 * 高德地图 api
 * @author Linzhongren
 * @desc 高德地图采用火星坐标系(GCJ02,即国测局坐标系)，本类技术资料还在高德地图开放平台。
 */
class Amap{
	
	//服务端的apiKey
	protected static $key = '5aae75dbb502513941b438701d04f5a8';
	
	public static function init($key){
		self::$key = $key;
	}
	
	/**
	 * latlng转地址 或 地址转latlng (经度在前，纬度在后，最多支持10个批量查询)
	 * @param string|array|array[] $address
	 * @param array $params
	 * @throws \Exception
	 * @return json转成的关联数组或者null
	 * @example Amap::geo('浙江省台州市路桥区桐屿街道中国日用品商城')               =>   转 经纬度
	 * @example Amap::geo(array('浙江省台州市路桥区','浙江省温岭市箬横镇浦岙村'))   =>   返回两个地址的经纬度
	 * @example Amap::geo(array('lng'=>'121.457607','lat'=>'28.375191'));      =>   返回此经纬度代表的地址
	 * @example Amap::geo(array(array('lng'=>'121.457607','lat'=>'28.375191'), 
	 *                          array('lng'=>'121.457607','lat'=>'28.375191'))); =>   返回此经纬度代表的地址
	 * @example Amap::geo('121.457607,28.375191');                             =>   返回此经纬度代表的地址
	 * @example Amap::geo(array('121.4576,28.3751', '121.4676,28.3751'));      =>   返回多个经纬度代表的址址
	 */
	public static function geo($address, $params = array()){
		if(empty(self::$key)) throw new \Exception("请通过调用Amap::init(\$key)函数初始化\$key参数");
		//地址转经纬度的接口为geo, 经纬度转地址的接口为regeo
		$api = '';
		//是否为批量转换
		$batch = 'false';
		//字符串拼接的暂存变量
		$_address = '';
		if(is_string($address)){
			//参数为string类型，在判断完geo或者regeo后，直接拼进url中
			$api = empty(MapUtils::isLatLngString($address)) ? 'geo' : 'regeo';
			$_address = trim($address);
		} else {
			//数组的情况下，要好好分析
			if(isset($address['lat']) && isset($address['lng'])){
				//单个经纬度数组，直接拼接
				$api = 'regeo';
				$_address = "{$address['lng']},{$address['lat']}";
			} else {
				//多个地址的情况
				foreach($address as $addr){
					//批量查询
					$batch = 'true';
					if(is_array($addr)){
						//地址单位还是数组，说明参数是array(array('lat'=>'x1', 'lng'=>'y1'), array('lat'=>'x2', 'lng'=>'y2'))
						$api = 'regeo';
						$_address .= "{$addr['lng']},{$addr['lat']}|";
					} else {
						//单个地址是字符串，则判断是否是经纬度字符串还是地址字符串，再拼接进参数表
						$api = empty(MapUtils::isLatLngString($addr)) ? 'geo' : 'regeo';
						$_address .= $addr . '|';
					}
				}
			}
		}
		//特别要去掉最后的|符号
		$_address =trim($_address,"| \t\n\r\0\x0B");
		
		//组装参数，连接请求
		$data = !empty($params) ? $params : array();
		$data['key'] = self::$key;
		$data['output'] = 'json';
		$data['batch'] = $batch;
		if($api == 'geo'){
			$data['address'] = $_address;
		} else {
			$data['location'] = $_address;
			$data['extentions'] = 'base';
		}
		$url = "http://restapi.amap.com/v3/geocode/{$api}";
		$result = Comm::get($url, $data);
		if(isset($result['code']) && $result['code'] == '200'){
			return json_decode($result['content'], true);
		} else{
			return null;
		}
	}
}