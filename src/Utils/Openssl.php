<?php
namespace Whilegit\Utils;

class Openssl{
	
	protected static $trusted_509 = array();
	protected static $trusted_pkcs12 = array();
	
	/**
	 * 根据根证书验证传入的公钥证书的合法法，若合法则提取该证书的内容
	 * @param string $certBase64String   待解码的证书
	 * @param array $cainfos             根证书，索引数组
	 * @param int $purpose               证书的用途 如X509_PURPOSE_ANY
	 * @return array|NULL  返回解码后的证书              
	 */
	public static function verifyAndParse($certBase64String, $cainfos, $purpose){
	
		if (array_key_exists($certBase64String, self::$trusted_509)){
			return self::$trusted_509[$certBase64String];
		}
	
		$flag = openssl_x509_read($certBase64String);
		if($flag === false){
			return null;
		}
		$certInfo = openssl_x509_parse($certBase64String);
	
		$now = time();
		if($now < $certInfo ['validFrom_time_t'] || $now > $certInfo ['validTo_time_t']){
			Trace::log("signPubKeyCert has expired");
			return null;
		}
		
		$result = openssl_x509_checkpurpose($certBase64String, $purpose, $cainfos);
		if($result === FALSE){
			//Trace::log("validate signPubKeyCert by rootCert failed");
			return null;
		} else if($result === TRUE){
			self::$trusted_509[$certBase64String] = $certInfo;
			return self::$trusted_509[$certBase64String];
		} else {
			//Trace::log("validate signPubKeyCert by rootCert failed with error");
			return null;
		}
	}
	
	/**
	 * 获取证书的subject部分
	 * @param unknown $certBase64String
	 * @return NULL|mixed
	 */
	public static function getSubject($certBase64String){
		$certInfo = null;
		if (array_key_exists($certBase64String, self::$trusted_509)){
			$certInfo = self::$trusted_509[$certBase64String];
		} else {
			$flag = openssl_x509_read($certBase64String);
			if($flag === false){
				return null;
			}
			$certInfo = openssl_x509_parse($certBase64String);
		}
		if(empty($certInfo)) return null;
		else return $certInfo['subject'];
	}
	
	/**
	 * 解码pkcs12格式的证书
	 * @param unknown $certPath
	 * @param unknown $certPwd
	 * @return boolean
	 */
	public static function parsePkcs12($certPath, $certPwd){
	
		if(!empty(self::$trusted_pkcs12[$certPath])){
			return self::$trusted_pkcs12[$certPath];
		}
		
		//读取证书内容
		$pkcs12certdata = file_get_contents ( $certPath );
		if($pkcs12certdata === false ){
			Trace::log($certPath . "file_get_contents fail。");
			return false;
		}
	
		//使用密码$certPwd解开$pkcs12certdata存储的信息, 解开后返回
		/*
		 * $certs = array('cert'=>'-----BEGIN CERTIFICATE-----\r\nXXXXXXXX\r\n-----END CERTIFICATE-----',  //x509格式的证书(含公钥/版本号/证书序列号等)
		 * 				  'pkey'=>'-----BEGIN PRIVATE KEY-----\r\nXXXXXXXX\r\n-----END PRIVATE KEY-----'), //私钥
		 * 				  'extracerts' => array(
		 * 						'-----BEGIN CERTIFICATE-----\r\nXXXXXXXXX\r\n-----END CERTIFICATE-----',
		 * 						'-----BEGIN CERTIFICATE-----\r\nXXXXXXXXX\r\n-----END CERTIFICATE-----'
		 * 						)
		 */
		$pkcs_parse = null;
		if(openssl_pkcs12_read ( $pkcs12certdata, $pkcs_parse, $certPwd ) == FALSE ){
			Trace::log($certPath . ", pwd[" . $certPwd . "] openssl_pkcs12_read fail。");
			return false;
		}
	
		//解析证书的序号，获得其序列号
		$x509_raw = $pkcs_parse ['cert'];
		if(!openssl_x509_read ( $x509_raw )){
			Trace::log($certPath . " openssl_x509_read fail。");
			return false;
		}
		$x509_parse = openssl_x509_parse ( $x509_raw );

	
		$result = array();
		$result['pkcs_parse'] = $pkcs_parse;
		$result['x509_parse'] = $x509_parse;
		
		self::$trusted_pkcs12[$certPath] = $result;
		return $result;
	}
	
	public static function getPkcs12_PrivateKey($certPath, $certPwd){
		$pkcs = self::parsePkcs12($certPath, $certPwd);
		if(empty($pkcs)){
			return null;
		}
		return $pkcs['pkcs_parse']['pkey'];
	}
	
	public static function getPkcs12_X509Raw($certPath, $certPwd){
		$pkcs = self::parsePkcs12($certPath, $certPwd);
		if(empty($pkcs)){
			return null;
		}
		return $pkcs['pkcs_parse']['cert'];
	}
	
	public static function getPkcs12_SerialNumber($certPath, $certPwd){
		$pkcs = self::parsePkcs12($certPath, $certPwd);
		if(empty($pkcs)){
			return null;
		}
		return $pkcs['x509_parse']['serialNumber'];
	}
}