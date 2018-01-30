<?php
namespace Whilegit\Utils;
use PHPMailer;


class Comm{
	
	/**
	 * http连接
	 * @param string $url        链接地址
	 * @param mix|array $post    如果是array的话，使用post方法请求，否则用get方法
	 * @param array $extra       额外的参数，如果健名以CURLOPT_开头，将被认为是curl连接参数，其它的都放入请求首部中
	 * @param number $timeout
	 * @param string $proxy      代理链接  使用一个链接(端口号也要写出来)
	 * @return array             错误返回 return array('success'=>false, 'msg' =>'xxx错误原因xxx');  正确返回array(查看response_parse函数)
	 */
	public static function request($url, $post = '', $extra = array(), $timeout = 60, $proxy = null) {
		$urlset = parse_url($url);
		if (empty($urlset['path'])) $urlset['path'] = '/';                       //路径部分(主机名和参数之间的部分)
		if (!empty($urlset['query'])) $urlset['query'] = "?{$urlset['query']}";  //参数部分
		else $urlset['query'] = '';
		if (empty($urlset['port']))   //目标端口, https使用443 SSL端口，http使用80端口
			$urlset['port'] = $urlset['scheme'] == 'https' ? '443' : '80';
		
		//检查是否已加载openssl扩展
		if (IString::exists($url, 'https://') && !extension_loaded('openssl')) {
			return array('success'=>false, 'msg' =>'请开启您PHP环境的openssl');
		}
		
		//如果curl扩展存在，优先走curl通道
		if (function_exists('curl_init') && function_exists('curl_exec')) {
			$ch = curl_init();
			if (Misc::ver_compare(phpversion(), '5.6') >= 0) {	//关掉文件安全上传模式，本行可能无效，未经验证?
				curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
			}
			
			//如果参数$extra提供了额外的ip字段，则用此ip字段替换参数$url里的域名，原主机名保存为$extra['Host']备用
			if (!empty($extra['ip'])) {
				$extra['Host'] = $urlset['host'];
				$urlset['host'] = $extra['ip'];
				unset($extra['ip']);
			}
			$real_url = $urlset['scheme'] . '://' . $urlset['host'] . ($urlset['port'] == '80' ? '' : ':' . $urlset['port']) . $urlset['path'] . $urlset['query'];
			//Trace::out($real_url);
			curl_setopt($ch, CURLOPT_URL, $real_url);   //设置链接
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);     //返回结果不直接输出至页面，而是保存进变量中
			@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    //跟随重定向
			curl_setopt($ch, CURLOPT_HEADER, 1);			 //返回结果中包含头信息
			@curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);	//使用HTML 1.0协议(短连接)
			if ($post) {
				if (is_array($post)) {
					$filepost = false;
					foreach ($post as $name => $value) {
						//post参数表的键值中有@或者CURLFile对象时，表明本次请求有文件上传
						if ((is_string($value) && substr($value, 0, 1) == '@') || (class_exists('CURLFile') && $value instanceof \CURLFile)) {
							$filepost = true;
							break;
						}
					}
					if (!$filepost) {
						//没有文件上传的话，使用Content_type: application/x-www-form-urlencoded
						$post = http_build_query($post);
					}else{
						//不作任何处理
						//下面代码curl_setopt($ch, CURLOPT_POSTFIELDS, $post);执行时，会将Content_type: multipart/form-data，这种格式是默认方式，有些服务器可能无法识别
					}
				}
				curl_setopt($ch, CURLOPT_POST, 1);           //使用post方法上传
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post); //设置表单域
			}
			
			//使用代理(此部分未经测试)
			if (!empty($proxy)) {
				$urls = parse_url($proxy);
				if (!empty($urls['host'])) {
					curl_setopt($ch, CURLOPT_PROXY, "{$urls['host']}:{$urls['port']}");
					$proxytype = 'CURLPROXY_' . strtoupper($urls['scheme']);
					if (!empty($urls['scheme']) && defined($proxytype)) {
						curl_setopt($ch, CURLOPT_PROXYTYPE, constant($proxytype));
					} else {
						curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);	//ps CURLPROXY_HTTP总有定义而CURLPROXY_HTTPS没有
						curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
					}
					if (!empty($urls['pass'])) {
						curl_setopt($ch, CURLOPT_PROXYUSERPWD, $urls['pass']);
					}
				}
			}
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);    //连接阶段的超时
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);           //传输阶段的超时
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);       //禁止验证对等证书
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);           //禁止验证对方主机和证书
			curl_setopt($ch, CURLOPT_SSLVERSION, 1);			   //SSL版本
			if (defined('CURL_SSLVERSION_TLSv1')) {
				curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
			}
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1');	//伪造浏览器User-Agent
			//处理请求头和额外的CURL选项
			if (!empty($extra) && is_array($extra)) {
				$headers = array();
				foreach ($extra as $opt => $value) {
					if (IString::exists($opt, 'CURLOPT_')) {
						curl_setopt($ch, constant($opt), $value);
					} elseif (is_numeric($opt)) {
						curl_setopt($ch, $opt, $value);
					} else {
						$headers[] = "{$opt}: {$value}";
					}
				}
				if (!empty($headers)) {
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);   //设置头部
				}
			}
			$data = curl_exec($ch);
			$status = curl_getinfo($ch);
			$errno = curl_errno($ch);
			$error = curl_error($ch);
			curl_close($ch);
			if ($errno || empty($data)) {
				return array('success'=>false, 'msg' =>"errno:{$errno} error:{$error}");
			} else {
				return self::response_parse($data);
			}
		}
		//不存在curl扩展，http[s]请求走原生socket通道(实现HTTP/1.0协议)
		$method = empty($post) ? 'GET' : 'POST';
		$fdata = "{$method} {$urlset['path']}{$urlset['query']} HTTP/1.1\r\n";  //请求行
		$fdata .= "Host: {$urlset['host']}\r\n";                                //请求首部：指定主机名
		if (function_exists('gzdecode')) {
			$fdata .= "Accept-Encoding: gzip, deflate\r\n";                     //请求首部：指示客户机接受gzip压缩
		}
		$fdata .= "Connection: close\r\n";                                      //请求首部：指示服务器使用短链接
		if (!empty($extra) && is_array($extra)) {                               //加载$extra参数中的各种请求首部
			foreach ($extra as $opt => $value) {
				if (!IString::exists($opt, 'CURLOPT_')) {
					$fdata .= "{$opt}: {$value}\r\n";
				}
			}
		}
		$body = '';
		if ($post) {
			if (is_array($post)) {
				$body = http_build_query($post);
			} else {
				$body = urlencode($post);
			}
			$fdata .= 'Content-Length: ' . strlen($body) . "\r\n\r\n{$body}";  //实体首部和实体首部的长度
		} else {
			$fdata .= "\r\n";
		}
		//打开socket连接
		if ($urlset['scheme'] == 'https') {
			$fp = fsockopen('ssl://' . $urlset['host'], $urlset['port'], $errno, $error);
		} else {
			$fp = fsockopen($urlset['host'], $urlset['port'], $errno, $error);
		}
		
		if (!$fp) {
			return array('success'=>false, 'msg' =>"errno:{$errno} error:{$error}");
		} else {
			stream_set_blocking($fp, true);      //使用阻塞模式
			stream_set_timeout($fp, $timeout);   //流超时设置
			fwrite($fp, $fdata);
			$content = '';
			while (!feof($fp))
				$content .= fgets($fp, 512);
			fclose($fp);
			return self::response_parse($content, true);
		}
	}
	
	/**
	 * http连接(get)
	 * @param string $url
	 * @param array  $data 参数，最终拼写进$url中
	 * @return 同request的返回值
	 */
	public static function get($url, $data = array()) {
		if(!empty($data)){
			$url .= (strpos($url, '?') !== false) ? '&' : '?';
			$url .= http_build_query($data);
		}
		return self::request($url);
	}
	
	/**
	 * http连接(post)
	 * @param string $url
	 * @param array $data  post参数
	 * @return 同request的返回值
	 */
	public static function post($url, $data) {
		$headers = array('Content-Type' => 'application/x-www-form-urlencoded');
		return self::request($url, $data, $headers);
	}
	
	/**
	 * 发送email<br/>
	 * @param array $config 连接参数
	 * @desc $config结构 
	 * <pre> array(
	 *           'smtp' => array(
	 *			     'type'   => '163',           //qq
	 *			     'server' => 'smtp.163.com',  //ssl://smtp.qq.com
	 *			     'port'   => 25,              //465
	 *			     'authmode' => ''             //非qq或163时，authmode为空时将在server上加ssl://协议头
	 *	          ),
	 *	          'username' => '6215714@163.com',
	 *	          'password' => '317507Ok',
	 *	          'signature' => '林忠仁', //签名(一般附在正文的最后)
	 *	          'sender' => '发送者名称', //不知道干嘛用
	 *	     );</pre>
	 * @param string  $to       //收件人邮箱
	 * @param unknown $subject  //主题
	 * @param unknown $body     //正文
	 * @return int|boolean|boolean[]|string[]
	 */
	public static function email($config, $to, $subject, $body) {
		static $mailer;
		/*
		 * 
		 */
		set_time_limit(0);
	
		if (empty($mailer)) {
			//如果无法自动加载PHPMailer，那么要把文件phpmailer/phpmailer/PHPMailerAutoload.php先加载进来
			$mailer = new PHPMailer();
	
			$config['charset'] = 'utf-8';
			if ($config['smtp']['type'] == '163') {
				$config['smtp']['server'] = 'smtp.163.com';
				$config['smtp']['port'] = 25;
			} elseif ($config['smtp']['type'] == 'qq') {
				$config['smtp']['server'] = 'ssl://smtp.qq.com';
				$config['smtp']['port'] = 465;
			} else {
				if (!empty($config['smtp']['authmode'])) {
					$config['smtp']['server'] = 'ssl://' . $config['smtp']['server'];
				}
			}
	
			if (!empty($config['smtp']['authmode'])) {
				if (!extension_loaded('openssl')) {
					return array('success'=>false, 'msg' =>'请开启您PHP环境的openssl');
				}
			}
			$mailer->signature = $config['signature'];
			$mailer->isSMTP();
			$mailer->CharSet = $config['charset'];
			$mailer->Host = $config['smtp']['server'];
			$mailer->Port = $config['smtp']['port'];
			$mailer->SMTPAuth = true;
			$mailer->Username = $config['username'];
			$mailer->Password = $config['password'];
			!empty($config['smtp']['authmode']) && $mailer->SMTPSecure = 'ssl';
	
			$mailer->From = $config['username'];
			$mailer->FromName = $config['sender'];
			$mailer->isHTML(true);
		}
		if (!empty($mailer->signature)) {
			$body .= htmlspecialchars_decode($mailer->signature);
		}
		$mailer->Subject = $subject;
		$mailer->Body = $body;
		$mailer->addAddress($to);
		if ($mailer->send()) {
			return true;
		} else {
			return array('success'=>false, 'msg' =>$mailer->ErrorInfo);
		}
	}
	
	/**
	 * 解析html请求的返回数据(包含头部+报文体)
	 * @param string $data
	 * @param boolean $chunked <pre>提示本函数是否检测chunked传输。
	 *                          若false，则即使检测到响应头部指明有chunked也不会处理，适合curl(因为curl会自动处理)；
	 *                          若true，则一旦检测响应头部指明有chunked,则会重新组装chunked</pre>
	 * @return array  {'code'=>'xx状态码xx', 'status'=>'xx状态xx', 'responseline':'xx响应行xx', 'headers':array, 'meta'=>'原始数据', 'content':'xxx页面htmlxx'}
	 */
	protected static function response_parse($data, $chunked = false) {
		$rlt = array();
		$headermeta = explode('HTTP/', $data);
		if (count($headermeta) > 2) {
			$data = 'HTTP/' . array_pop($headermeta);
		}
		$pos = strpos($data, "\r\n\r\n");
		$split1[0] = substr($data, 0, $pos);	               //保存首部
		$split1[1] = substr($data, $pos + 4, strlen($data));   //报文体
	
		$split2 = explode("\r\n", $split1[0], 2);     //$split2[0]状态行，$split2[1]其它首部
		preg_match('/^(\S+) (\S+) (\S+)$/', $split2[0], $matches);
		$rlt['code'] = $matches[2];        //状态码, 如200
		$rlt['status'] = $matches[3];      //状态，如OK
		$rlt['responseline'] = $split2[0];
	
		//以下处理首部
		$header = explode("\r\n", $split2[1]);
		$isgzip = false;
		$ischunk = false;
		foreach ($header as $v) {
			$pos = strpos($v, ':');
			$key = substr($v, 0, $pos);           //首部的键名
			$value = trim(substr($v, $pos + 1));  //键值
			
			if(empty($rlt['headers'][$key])) {
				$rlt['headers'][$key] = $value;
			} else if (is_array($rlt['headers'][$key])) {
				$rlt['headers'][$key][] = $value;       //注意：$rlt['headers']['xxxKEYxxx']可能是数组或字符串
			} else {
				$temp = $rlt['headers'][$key];
				unset($rlt['headers'][$key]);
				$rlt['headers'][$key][] = $temp;
				$rlt['headers'][$key][] = $value;
			} 
			if(!$isgzip && strtolower($key) == 'content-encoding' && strtolower($value) == 'gzip') {
				$isgzip = true;  //响应报文开启了gzip压缩
			}
			if(!$ischunk && strtolower($key) == 'transfer-encoding' && strtolower($value) == 'chunked') {
				$ischunk = true; //响应报文开启了chunked分块传输
			}
		}
		if($chunked && $ischunk) {
			$rlt['content'] = self::response_parse_unchunk($split1[1]);  //chunked重新组装
		} else {
			$rlt['content'] = $split1[1];
		}
		if($isgzip && function_exists('gzdecode')) {
			$rlt['content'] = gzdecode($rlt['content']);
		}
	
		$rlt['meta'] = $data;
		if($rlt['code'] == '100') {    //状态码100表示继续测试
			return self::response_parse($rlt['content']);
		}
		return $rlt;
	}
	
	/**
	 * 解析http Chunked传输(响应报文有首部：Transfer-Encoding: Chunked)
	 * @param string $str
	 * @return boolean|string
	 * @desc 原理<br>    ea5\r\n{第一块chunk,3749字节}\r\nea5\r\n{第二块chunk,3749字节}...\r\n0\r\n\r\n<br/>
	 * @desc 其中\r\n0\r\n\r\n是结束标记, ea5十六进制表示，表示3749字节
	 */
	protected static function response_parse_unchunk($str = null) {
		if(!is_string($str) or strlen($str) < 1) {
			return false;
		}
		$eol = "\r\n";
		$add = strlen($eol);
		$tmp = $str;
		$str = '';
		do {
			$tmp = ltrim($tmp);
			$pos = strpos($tmp, $eol);
			if($pos === false) {
				return false;
			}
			$len = hexdec(substr($tmp, 0, $pos));   //取出chunk块的长度，显式十六进制表示
			if(!is_numeric($len) or $len < 0) {
				return false;
			}
			$str .= substr($tmp, ($pos + $add), $len);    //取出本块chunk的数据
			$tmp  = substr($tmp, ($len + $pos + $add));   //设置一下chunk
			$check = trim($tmp);
		} while(!empty($check));
		unset($tmp);
		return $str;
	}
	
	
	/**
	 * 检测request方法、get方法、post方法是否执行错误
	 * @param $request_return request/get/post方法的返回值
	 * @example request/get/post方法的错误时的返回值  return array('success'=>false, 'msg' =>'xxx错误原因xxx');  正确返回array(查看response_parse函数)
	 * @example request/get/post方法正确时的返回值 array  {'code'=>'200', 'status'=>'OK', 'responseline':'xx响应行xx', 'headers':array, 'meta'=>'原始数据', 'content':'xxx页面htmlxx'}
	 * @return true有错误，false没有错误(可以放心使用$request_return['content']的内容)
	 */
	public static function is_error($request_return){
	    if(!is_array($request_return)) return true;
	    if(isset($request_return['success']) && $request_return['success'] == false) return true;
	    if(empty($request_return['code']) || empty($request_return['status'])) return true;
	    if($request_return['code'] != '200' || $request_return['status'] != 'OK') return true;
	    return false;
	}
}
