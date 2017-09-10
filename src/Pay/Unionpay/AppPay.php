<?php
namespace Whilegit\Pay\Unionpay;
use Whilegit\Utils\Comm;
use Whilegit\Utils\IArray;
use Whilegit\Utils\Openssl;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

const COMPANY = '中国银联股份有限公司';
class AppPay{
	
	protected static $ifValidateCNName = 'false';
	
	protected $signCertPath;
	protected $signCertPwd;
	protected $middleCertPath;
	protected $rootCertPath;
	protected $version;      //报文版本，总是5.1.0
	protected $signMethod;   //证书签名方式，总是01
	protected $appTransUrl;   //App交易请求地址，用于获取一个TN码
	
	
	protected $monolog = null;

	public function __construct($config_path, $path_callback = null){
		if(file_exists($config_path) == false){
			throw new \InvalidArgumentException('未找到配置文件');
		}
		$config = parse_ini_file($config_path, true);
		if(empty($config['acpsdk'])){
			throw new \InvalidArgumentException('配置文件不开确');
		}
		$config = $config['acpsdk'];
		
		if(empty($config['acpsdk.signCert.path']))  throw new \InvalidArgumentException( '签名证书路径为空');
		$this->signCertPath = call_user_func($path_callback, $config['acpsdk.signCert.path']);
		
		if(empty($config['acpsdk.signCert.pwd']))  throw new \InvalidArgumentException( '签名证书的密码为空');
		$this->signCertPwd = $config['acpsdk.signCert.pwd'];
		
		if(empty($config['acpsdk.middleCert.path'])) throw new \InvalidArgumentException( '中级证书为空');
		$this->middleCertPath = call_user_func($path_callback, $config['acpsdk.middleCert.path']);
		
		if(empty($config['acpsdk.rootCert.path'])) throw new \InvalidArgumentException( '根证书为空');
		$this->rootCertPath = call_user_func($path_callback, $config['acpsdk.rootCert.path']);
		
		if(empty($config['acpsdk.version']) || $config['acpsdk.version'] != '5.1.0'){
			throw new \InvalidArgumentException( '报文的版本号不正确，限定为5.1.0');
		}
		$this->version = $config['acpsdk.version'];
		
		if(empty($config['acpsdk.signMethod']) || $config['acpsdk.signMethod'] != '01')  {
			throw new \InvalidArgumentException( '签名方式，证书方式固定01');
		}
		$this->signMethod = $config['acpsdk.signMethod'];
		
		if(empty($config['acpsdk.appTransUrl'])){
			throw new \InvalidArgumentException( '没有提供App方式获取TN码的交易地址');
		}
		$this->appTransUrl = $config['acpsdk.appTransUrl'];
		
		if(empty($config['acpsdk.log.file.path']) || empty($config['acpsdk.log.level'])){
			throw new \InvalidArgumentException( '没有提供日志地址或日志级别');
		}
		$log_path =  call_user_func($path_callback, $config['acpsdk.log.file.path']);
		$level = $config['acpsdk.log.level'];
		$log_path = $log_path . '/' . date('Ym') . '/UnionPay_' . date('d') . '.log';
		
		$this->monolog = new Logger('UnionPay');
		$this->monolog->pushHandler(new StreamHandler($log_path, $level ));
		$this->monolog->info('---------');
	}
	
	
	/*
		$order_info = array(
			'frontUrl' => '', //前台通知地址
			'backUrl' => '',  //后台通知地址
			'merId' => '',    //商户代码，请改自己的测试商户号
			'orderId' => '',  //商户订单号，8-32位数字字母，不能含“-”或“_”
			'txnTime' => '',  //订单发送时间，格式时YYYYMMDDhhmmss
			'txnAmt' => '',   //交易金额，单位分
		);
	 */
	public function comsume($order_info){
		$params = array('frontUrl'=>'', 'backUrl'=>'', 'merId'=>'', 'orderId'=>'', 'txnTime'=>'', 'txnAmt'=>'');
		foreach($params as $k=>&$v){
			if(empty($order_info[$k])){
				throw new \InvalidArgumentException('public function comsume($info)', '参数不符合要求');
			}
			$v = $order_info[$k];
		}
		unset($v);
		
		$params_static = array(
			//以下信息非特殊情况不需要改动
			'version' => $this->version,          //版本号
			'encoding' => 'utf-8',				  //编码方式
			'txnType' => '01',				      //交易类型
			'txnSubType' => '01',				  //交易子类
			'bizType' => '000201',				  //业务类型
			'signMethod' => $this->signMethod,	  //签名方法
			'channelType' => '08',	              //渠道类型，07-PC，08-手机
			'accessType' => '0',		          //接入类型
			'currencyCode' => '156',	          //交易币种，境内商户固定156
	
			//其他特殊用法请查看 pages/api_05_app/special_use_purchase.php
			//请求方保留域，
			//'reqReserved' => base64_encode('任意格式的信息都可以'),
		);
		
		//合并参数
		$params = array_merge($params_static, $params);
		
		//签名并发送请求
		$flag = $this->sign($params, $this->signCertPath, $this->signCertPwd);
		if($flag == false){
			$this->monolog->error('银联Http发送前签名失败', $params);
			return false;
		}
		$result = Comm::post($this->appTransUrl, $params);
		if($result['code'] != 200 || $result['status'] != 'OK'){
			$this->monolog->error('银联Http请求失败', array($this->appTransUrl, $params, $result));
			return false;
		}
		
		//把返回字符串打散重新拼装
		$result_arr = IArray::parse_str($result['content'], array('signature', 'signPubKeyCert'));
		//验证对方的签名
		if (!$this->validate ($result_arr) ){
			$this->monolog->error ( ">>>>>银联Http应答验证签名失败<<<<<<<" . __FILE__ . ' ' .__LINE__);
			return false;
		}
		
		//是否成功获取tn号
		if(empty($result_arr['tn']) || $result_arr['respMsg'] != '成功[0000000]'){
			$this->monolog->error ( ">>>>>银联Http应答业务不成功<<<<<<<" . __FILE__ . ' ' .__LINE__);
			return false;
		}
		
		return $result_arr;
	}
	
	/**
	 * 对自己的报名签名
	 * @param &array $params       未签名前的post参数数组
	 * @param string $cert_path   签名证书的文件路径
	 * @param string $cert_pwd    签名证书的密码
	 * @return boolean  成功返回true（此时$params参数将按升序重排，将增加certId和signature字段），失败返回false
	 */
	protected function sign(&$params, $cert_path, $cert_pwd) {
		$result = false;
		
		if(isset($params['signature'])) unset($params['signature']);
		if($params['signMethod'] != $this->signMethod) {
			$this->monolog->error ( "signMethod不正确");
			return false;
		}
		if($params['version'] != $this->version){
			$this->monolog->error ( "不支持的版本号: " + $params['version'] );
			return false;
		}
		
		//证书ID
		$params['certId'] = Openssl::getPkcs12_SerialNumber($cert_path, $cert_pwd);
		$private_key = Openssl::getPkcs12_PrivateKey($cert_path, $cert_pwd);

		ksort($params);
		$params_str = urldecode(http_build_query($params));
		//sha256签名摘要
		$params_sha256x16 = hash( 'sha256',$params_str);
		// 签名，返回引用参数 $signature，该值为256字节的二进制数据，后续还要base64()
		$result = openssl_sign ( $params_sha256x16, $signature, $private_key, 'sha256');
		if($result == true){
			$params ['signature'] = base64_encode ( $signature );
		}
		return $result;
	}
	
	/**
	 * 验签
	 * @param $params 应答数组
	 * @return 是否成功
	 */
	public function validate($params) {
		if($params['signMethod'] != '01') return false;
		if($params['version'] != '5.1.0') return false;
		$isSuccess = false;
		
		$signature_str = $params ['signature'];
		unset ( $params ['signature'] );
		
		ksort($params);
		$params_str = urldecode(http_build_query($params));
			
		$strCert = $params['signPubKeyCert'];
		$certInfo = Openssl::verifyAndParse($strCert, array($this->rootCertPath, $this->middleCertPath), X509_PURPOSE_ANY);
		if($certInfo == null){
			$this->monolog->error ("validate cert err: " + $params["signPubKeyCert"]);
			$isSuccess = false;
		}
		
		//检查证书的拥有者
		$subject =  Openssl::getSubject($strCert);
		$company = explode('@',$subject['CN']); //041@Z12@00040000:SIGN@00000062
		if(count($company) < 3) {
			$this->monolog->error("证书的拥有者错误：" . $company);
			return false;
		}
		$cn = $company[2];
		if(strtolower(self::$ifValidateCNName) == "true"){
			if (COMPANY != $cn){
				$this->monolog->error("cer owner is not CUP:" . $cn);
				return false;
			}
		} else if (COMPANY != $cn && "00040000:SIGN" != $cn){
			$this->monolog->error("cer owner is not CUP:" . $cn);
			return false;
		}

		//验证签名
		$params_sha256x16 = hash('sha256', $params_str);
		$signature = base64_decode ( $signature_str );
		$isSuccess = openssl_verify ( $params_sha256x16, $signature,$strCert, "sha256" );

		return $isSuccess;
	}
}