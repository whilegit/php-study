<?php
use Whilegit\Pay\Unionpay\AppPay;
use Whilegit\Utils\Trace;
use Whilegit\Utils\IArray;

require_once "vendor/autoload.php";

/*
$str = 'accessType=0&bizType=000201&encoding=utf-8&merId=777290058110048&orderId=20170909090602&respCode=00&respMsg=成功[0000000]&signMethod=01&tn=611854042060710157601&txnSubType=01&txnTime=20170909090602&txnType=01&version=5.1.0&signPubKeyCert=-----BEGIN CERTIFICATE-----\r\nMIIEOjCCAyKgAwIBAgIFEAJkAUkwDQYJKoZIhvcNAQEFBQAwWDELMAkGA1UEBhMC\r\nQ04xMDAuBgNVBAoTJ0NoaW5hIEZpbmFuY2lhbCBDZXJ0aWZpY2F0aW9uIEF1dGhv\r\ncml0eTEXMBUGA1UEAxMOQ0ZDQSBURVNUIE9DQTEwHhcNMTUxMjA0MDMyNTIxWhcN\r\nMTcxMjA0MDMyNTIxWjB5MQswCQYDVQQGEwJjbjEXMBUGA1UEChMOQ0ZDQSBURVNU\r\nIE9DQTExEjAQBgNVBAsTCUNGQ0EgVEVTVDEUMBIGA1UECxMLRW50ZXJwcmlzZXMx\r\nJzAlBgNVBAMUHjA0MUBaMTJAMDAwNDAwMDA6U0lHTkAwMDAwMDA2MjCCASIwDQYJ\r\nKoZIhvcNAQEBBQADggEPADCCAQoCggEBAMUDYYCLYvv3c911zhRDrSWCedAYDJQe\r\nfJUjZKI2avFtB2/bbSmKQd0NVvh+zXtehCYLxKOltO6DDTRHwH9xfhRY3CBMmcOv\r\nd2xQQvMJcV9XwoqtCKqhzguoDxJfYeGuit7DpuRsDGI0+yKgc1RY28v1VtuXG845\r\nfTP7PRtJrareQYlQXghMgHFAZ/vRdqlLpVoNma5C56cJk5bfr2ngDlXbUqPXLi1j\r\niXAFb/y4b8eGEIl1LmKp3aPMDPK7eshc7fLONEp1oQ5Jd1nE/GZj+lC345aNWmLs\r\nl/09uAvo4Lu+pQsmGyfLbUGR51KbmHajF4Mrr6uSqiU21Ctr1uQGkccCAwEAAaOB\r\n6TCB5jAfBgNVHSMEGDAWgBTPcJ1h6518Lrj3ywJA9wmd/jN0gDBIBgNVHSAEQTA/\r\nMD0GCGCBHIbvKgEBMDEwLwYIKwYBBQUHAgEWI2h0dHA6Ly93d3cuY2ZjYS5jb20u\r\nY24vdXMvdXMtMTQuaHRtMDgGA1UdHwQxMC8wLaAroCmGJ2h0dHA6Ly91Y3JsLmNm\r\nY2EuY29tLmNuL1JTQS9jcmw0NDkxLmNybDALBgNVHQ8EBAMCA+gwHQYDVR0OBBYE\r\nFAFmIOdt15XLqqz13uPbGQwtj4PAMBMGA1UdJQQMMAoGCCsGAQUFBwMCMA0GCSqG\r\nSIb3DQEBBQUAA4IBAQB8YuMQWDH/Ze+e+2pr/914cBt94FQpYqZOmrBIQ8kq7vVm\r\nTTy94q9UL0pMMHDuFJV6Wxng4Me/cfVvWmjgLg/t7bdz0n6UNj4StJP17pkg68WG\r\nzMlcjuI7/baxtDrD+O8dKpHoHezqhx7dfh1QWq8jnqd3DFzfkhEpuIt6QEaUqoWn\r\nt5FxSUiykTfjnaNEEGcn3/n2LpwrQ+upes12/B778MQETOsVv4WX8oE1Qsv1XLRW\r\ni0DQetTU2RXTrynv+l4kMy0h9b/Hdlbuh2s0QZqlUMXx2biy0GvpF2pR8f+OaLuT\r\nAtaKdU4T2+jO44+vWNNN2VoAaw0xY6IZ3/A1GL0x\r\n-----END CERTIFICATE-----&signature=FaYi92y52WywjdP7J/VrEI6zqWfCFDYg/lDNHIYM0xH3nOktwvI8WKKOoD/P582ynhy805VxupYUWaJO+1VAJzufhm6paSWlqNU/Nb/H/vjnG1zQEd1Z+uCdRIuHjLK22EuNKZVkEv9kexpVuO01Ne6N8IBpPvTRonuRhnggGSIlLDiZohvxZeSZvvclWz9V/IvndT5S6hAay+FPq2ea3g/DgAV2p4OpmK4F6pM/lOZ0lbjwhNwIAoK3NlsGY4MIHTMUSCdZluY644ZeNY6PCp5Hj0TUbRQtiRfmwaql2eDmh1EQkeyupuHasNPIUjgSGKRUap9KiS0wjZINCNROBw==';
$ary =  array();
parse_str($str, $ary);
Trace::out(array('parse_str'=>$ary, 'xxx'=>IArray::parseQString($str)));
*/

$unionPay = new AppPay(__DIR__ . '/static/pay/unionpay/pay.ini', function($path){return str_replace('WEBROOT', __DIR__, $path);});

$order_info = array(
	'frontUrl' => 'http://localhost:8086/upacp_demo_app/demo/api_05_app/FrontReceive.php', //前台通知地址
	'backUrl' => 'http://222.222.222.222:8080/upacp_demo_app/demo/api_05_app/BackReceive.php',  //后台通知地址
	'merId' => '777290058110048',    //商户代码，请改自己的测试商户号
	'orderId' => '20170915090602',  //商户订单号，8-32位数字字母，不能含“-”或“_”
	'txnTime' => '20170915090602',  //订单发送时间，格式时YYYYMMDDhhmmss
	'txnAmt' => '1000',   //交易金额，单位分
);

$result = $unionPay->comsume($order_info);
if($result === false){
	Trace::out('consume消费业务失败');
} else {
	if(!is_array($result) || empty($result['tn'])){
		Trace::out('consume消费业务异常: ' . var_export($result, true));
	} else {
		//业务成功，此TN域返回给前端，供App调起银联模块
		echo "TN: {$result['tn']}<br />\r\n";
		
		//查询
		Trace::out($unionPay->query($order_info));
	}
}

