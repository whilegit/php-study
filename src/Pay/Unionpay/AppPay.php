<?php
namespace Whilegit\Pay\Unionpay;
use Whilegit\Utils\Comm;
use Whilegit\Utils\IArray;
use Whilegit\Utils\Openssl;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

const COMPANY = '中国银联股份有限公司';

/**
 * 银联App控件支付
 * @author Linzhongren
 * @example <pre>
 * $unionPay = new AppPay(__DIR__ . '/static/pay/unionpay/pay.ini', function($path){return str_replace('WEBROOT', __DIR__, $path);});
 * 
 * $order_info = array(
 * 'frontUrl' => 'http://localhost:8086/upacp_demo_app/demo/api_05_app/FrontReceive.php', //前台通知地址
 * 'backUrl' => 'http://222.222.222.222:8080/upacp_demo_app/demo/api_05_app/BackReceive.php',  //后台通知地址
 * 'merId' => '777290058110048',    //商户代码，请改自己的测试商户号
 * 'orderId' => '20170909090602',  //商户订单号，8-32位数字字母，不能含“-”或“_”
 * 'txnTime' => '20170909090602',  //订单发送时间，格式时YYYYMMDDhhmmss
 * 'txnAmt' => '1000',   //交易金额，单位分
 * );
 * 
 * $result = $unionPay->comsume($order_info);
 * if($result === false){
 * 	   Trace::out('consume消费业务失败');
 * } else {
 *     if(!is_array($result) || empty($result['tn'])){
 * 	       Trace::out('consume消费业务异常: ' . var_export($result, true));
 *     } else {
 * 	       //业务成功，此TN域返回给前端，供App调起银联模块
 * 	       echo "TN: {$result['tn']}<br />\r\n";
 * 	
 * 	       //查询
 * 		   Trace::out($unionPay->query($order_info));
 *     }
 * } </pre>
 */
class AppPay{
	
	protected $ifValidateCNName;   //是否验证证书的持有人是 COMPANY，测试环境不验证
	
	protected $signCertPath;
	protected $signCertPwd;
	protected $middleCertPath;
	protected $rootCertPath;
	protected $version;      //报文版本，总是5.1.0
	protected $signMethod;   //证书签名方式，总是01
	
	protected $appTransUrl;   //App交易请求地址，用于获取一个TN码
	protected $singleQueryUrl;  //单次查询的url
	
	protected $appVerifySignCertPath;  //前端验签的证书
	
	//日志
	protected $monolog = null;

	/**
	 * 构造函数
	 * @param $config_path    总配置文件的位置
	 * @param $path_callback  配置文件中，涉及到部分证书路径问题，加此回调函数可以解决相对路径和绝对路径的问题。
	 */
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
		
		if(empty($config['acpsdk.appVerifySignCert.path'])) throw new \InvalidArgumentException( '前端验签证书为空');
		$this->appVerifySignCertPath = call_user_func($path_callback, $config['acpsdk.appVerifySignCert.path']);
		
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
		
		if(empty($config['acpsdk.singleQueryUrl'])){
			throw new \InvalidArgumentException( '没有提供App方式交易是否成功的查询地址');
		}
		$this->singleQueryUrl = $config['acpsdk.singleQueryUrl'];
		
		if(!isset($config['acpsdk.ifValidateCNName'])){
			throw new \InvalidArgumentException( '请提供生产环境或测试环境的参数 ifValidateCNName');
		}
		$this->ifValidateCNName = ($config['acpsdk.ifValidateCNName'] != '1');
		
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
	
	/**
	 * 消费，并请求获取TN码
	 * @param array $order_info <pre>
	 * 		$order_info = array(
	 *			'frontUrl' => '', //前台通知地址(非必须)
	 *			'backUrl' => '',  //后台通知地址
	 *			'merId' => '',    //商户代码，请改自己的测试商户号
	 *			'orderId' => '',  //商户订单号，8-32位数字字母，不能含“-”或“_”
	 *			'txnTime' => '',  //订单发送时间，格式时YYYYMMDDhhmmss
	 *			'txnAmt' => '',   //交易金额，单位分
	 * 		);
	 * </pre>
	 * @throws \InvalidArgumentException
	 * @return false|array  请求返回的报文(注意查看有没有tn字段)
	 */
	public function comsume($order_info){
		$params = array('backUrl'=>'', 'merId'=>'', 'orderId'=>'', 'txnTime'=>'', 'txnAmt'=>'');
		foreach($params as $k=>&$v){
			if(empty($order_info[$k])){
				throw new \InvalidArgumentException('public function comsume($info)', '参数不符合要求');
			}
			$v = $order_info[$k];
		}
		unset($v);
		
		if(!empty($order_info['frontUrl'])) $params['frontUrl'] = $order_info['frontUrl'];
		
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
			$this->monolog->error('银联consume交易: http发送前签名失败', $params);
			return false;
		}
		$result = Comm::post($this->appTransUrl, $params);
		if($result['code'] != 200 || $result['status'] != 'OK'){
			$this->monolog->error('银联consume交易: Http请求失败', array($this->appTransUrl, $params, $result));
			return false;
		}
		
		//把返回字符串打散重新拼装
		$result_arr = IArray::parse_str($result['content'], array('signature', 'signPubKeyCert'));
		if(empty($result_arr)){
			$this->monolog->error ( "银联consume交易:Http应答非可解析数据" . var_export($result['content'], true));
			return false;
		}
		//验证对方的签名
		if (!$this->validate ($result_arr) ){
			$this->monolog->error ( "银联consume交易:Http应答验证签名失败" . var_export($result_arr, true));
			return false;
		}
		
		//是否成功获取tn号
		if(empty($result_arr['tn']) || $result_arr['respMsg'] != '成功[0000000]'){
			$this->monolog->error ( "银联consume交易：创建TN失败" . export($result_arr, true));
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
	 * 验签(同步应答或异步应答都适用)
	 * @desc <br /> 如果是异步应答(即后台通知)应传入$_POST验签。通过后，涉及到资金操作时，最好再主动发起一次查询。
	 * @param $params 应答数组
	 * @return boolean 是否成功
	 */
	protected function validate($params) {

		if(empty($params['signMethod']) ||  $params['signMethod'] != '01') return false;
		if(empty($params['version']) || $params['version'] != '5.1.0') return false;
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
		if(strtolower($this->ifValidateCNName) == "true"){
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

	/**
	 * 后台通知的验签
	 * @desc <br />如果涉及资金操作，请获取$_POST[queryId](交易查询流水号)发起一次查询，再次确认交易是否成功。之后，可以提出相关的域更新数据库。
	 * @return boolean 正确true, 错误false
	 */
	public function back_notify_validate(){
		if(empty($_POST) || !is_array($_POST) || empty($_POST['signature'])){
			$this->monolog->error ("参数错误：" . var_export($_POST, true));
			return false;
		}
		$flag = $this->validate($_POST);
		if($flag == false){
			$this->monolog->error ("验签失败：" . var_export($_POST, true));
			return false;
		}
		
		if(empty($_POST['respCode'])){
			$this->monolog->error ("验签成功但缺少respCode参数(银联接口故障)：" . var_export($_POST, true));
			return false;
		}
		
		$respCode = $_POST['respCode'];
		if($respCode != "00"){
			$this->monolog->error ("验签成功但交易失败：respCode=$respCode, \$_POST:" . var_export($_POST, true));
			return false;
		}
		
		//主动发起一次查询，确保交易已成功
		if(empty($_POST ['orderId']) || empty($_POST['merId']) || empty($_POST['txnTime'])){
			$this->monolog->error ("验签成功但数据中缺少orderId或merId或txnTime(通常为银联错误)：respCode=$respCode, \$_POST:" . var_export($_POST, true));
			return false;
		}
		$order_info = array('merId'=>$_POST['merId'], 'orderId'=>$_POST ['orderId'], 'txnTime'=>$_POST['txnTime']);

		$flag = $this->query($order_info);
		if($flag === false){
			$this->monolog->error ("后台通知交易成功但主动查询却显示失败：false, \$_POST:" . var_export($_POST, true));
			return false;
		} else if($flag === true){
			return true;
		} else {
			$this->monolog->error ("后台通知交易成功但主动查询却显示失败：{$flag}, \$_POST:" . var_export($_POST, true));
			return false;
		}
		return false;
	}

	/**
	 * 前台通知的验签
	 * @desc <br />对控件给商户APP返回的应答信息验签，前端请直接把string型的json串post上来
	 */
	public function front_notify_validate(){
		$data = file_get_contents('php://input', 'r');
		if(empty($data)){
			return false;
		}
		
		$data = json_decode($data);
		if(empty($data)) {
			return false;
		}
		
		if(empty($data->sign) || empty($data->data)){
			return false;
		}
		
		$sign = $data->sign;
		$data = $data->data;
		
		//获取前端验签订书
		$public_key = openssl_x509_read(file_get_contents($appVerifySignCertPath));
		$signature = base64_decode ( $sign );
		$params_sha1x16 = sha1 ( $data, FALSE );
		$isSuccess = openssl_verify ( $params_sha1x16, $signature,$public_key, OPENSSL_ALGO_SHA1 );
		return $isSuccess;
	}
	
	/**
	 * 前台类交易，后台主动发起查询
	 * @param array $order_info    交易的关键参数，必须包含merId,orderId,txnTime三个键名
	 * @throws \InvalidArgumentException
	 * @return boolean|string
	 */
	public function query($order_info){
		$params = array('merId'=>'', 'orderId'=>'', 'txnTime'=>'');
		foreach($params as $k=>&$v){
			if(empty($order_info[$k])){
				throw new \InvalidArgumentException('public function comsume($info)', '参数不符合要求');
			}
			$v = $order_info[$k];
		}
		unset($v);
		
		$params_static = array(
				//以下信息非特殊情况不需要改动
				'version' => $this->version,  //版本号
				'encoding' => 'utf-8',		  //编码方式
				'signMethod' => $this->signMethod,		  //签名方法
				'txnType' => '00',		      //交易类型
				'txnSubType' => '00',		  //交易子类
				'bizType' => '000000',		  //业务类型
				'accessType' => '0',		  //接入类型
				'channelType' => '07',		  //渠道类型
		);
		//合并参数
		$params = array_merge($params_static, $params);
		
		//签名并发送请求
		$flag = $this->sign($params, $this->signCertPath, $this->signCertPwd);
		if($flag == false){
			$this->monolog->error('银联Http发送前签名失败', $params);
			return false;
		}
		$result = Comm::post($this->singleQueryUrl, $params);
		if($result['code'] != 200 || $result['status'] != 'OK'){
			$this->monolog->error('银联查询：HTTP请求不成功', array($this->singleQueryUrl, $params, $result));
			return false;
		}
		//把返回字符串打散重新拼装
		$result_arr = IArray::parse_str($result['content'], array('signature', 'signPubKeyCert')); 
		
		//验证对方的签名
		if (!$this->validate ($result_arr) ){
			$this->monolog->error ( "银联查询：Http应答验证签名失败，信息：" . var_export($result_arr, true));				
			return false;
		}
		
		if ($result_arr["respCode"] == "00"){
			if ($result_arr["origRespCode"] == "00"){
				//交易成功
				return true;
			} else if ($result_arr["origRespCode"] == "03"
					|| $result_arr["origRespCode"] == "04"
					|| $result_arr["origRespCode"] == "05"){
						//后续需发起交易状态查询交易确定交易状态
						$this->monolog->info ( "银联查询：交易处理中； 信息: " . var_export($result_arr, true));
						return "交易处理中，请稍候查询";
			} else {
				//其他应答码做以失败处理
				$this->monolog->error ( "银联查询：交易失败； 信息: " . var_export($result_arr, true));
				return "交易失败：" . $result_arr["origRespMsg"];
			}
		} else if ($result_arr["respCode"] == "03"
				|| $result_arr["respCode"] == "04"
				|| $result_arr["respCode"] == "05" ){
					//后续需发起交易状态查询交易确定交易状态
					$this->monolog->info ( "银联查询：处理超时； 信息: " . var_export($result_arr, true));
					return "处理超时，请稍候查询";
		} else {
			//其他应答码做以失败处理
			$this->monolog->error ( "银联查询：交易失败； 信息: " . var_export($result_arr, true));
			return "交易失败：" . $result_arr["respMsg"];
		}
		
		return false;
	}
}