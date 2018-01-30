<?php
namespace Whilegit\Utils\image;
use Endroid\QrCode\QrCode;
//use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use AliyunMNS\Exception\InvalidArgumentException;

class Gd{
	
	/**
	 * 生成二维码
	 * @param unknown $str
	 * @param array $params 可选参数
	 * @param boolean $html_out  true时直接输出到客户端， false返回图片的二进制string
	 * @return exit|string       若返回string, 则可以使用imageCreateFromString()还原出image资源，可以进一点操作二给码
	 */
	public static function qrcode($str, $params = array(), $html_out = true ){
		$qrCode = new QrCode($str);
		$params = array_merge(array(
				'size'     => 300,  //二维码尺寸
				'margin'   => 15,   //二维码整体的边距
				'logopath' => '',   //中间logo的图片位置，为空时不输出logo
				'label'    => '',   //二维码下方的文字，为空时不输出
				'fontpath' => __DIR__.'/../../../static/font/msyh.ttf', //文字的字体
				'foreground_color' => '#000000', //前景色，一般为黑色
				'background_color' => '#ffffff', //背景色，一般为白色
				'correct_level'    => 'medium',  //容错等级 low/quartile/medium/high，对应类Endroid\QrCode\ErrorCorrectionLevel
		   ),$params);
		// 设置二维码的各项参数
		$qrCode->setSize($params['size'])
		       ->setWriterByName('png')
			   ->setMargin($params['margin'])
			   ->setEncoding('UTF-8')
			   ->setErrorCorrectionLevel($params['correct_level'])
			   ->setForegroundColor(self::rgb($params['foreground_color']))
			   ->setBackgroundColor(self::rgb($params['background_color']))
			   ->setValidateResult(false);
		if(!empty($params['label'])){
			//调整文字的上下边距
			$labelMargin = array ('t' => $params['size']/30 - $params['margin'],'b' => $params['size']/30);
			$qrCode->setLabel($params['label'], $params['size']/15, $params['fontpath'], LabelAlignment::CENTER, $labelMargin);
		}
		if(!empty($params['logopath'])){
		    if(!file_exists($params['logopath'])){
		        die("{$params['logopath']} is not existed.");
		    }
			$qrCode->setLogoPath($params['logopath'])->setLogoWidth($params['size']/4);
		}
		$string = $qrCode->writeString();
		// Directly output the QR code
		if($html_out){
			ob_clean();
			header('Content-Type: '.$qrCode->getContentType());
			echo $string;
			exit;
		} else {
			return $string;
		}
	}
	
	/**
	 * 颜色转换
	 * @param string|int|array $param
	 * @param boolean $htmlout 为true时输出 #ff0080样式的字符串
	 * @return int|array
	 * @example <pre>
	 *     Gd::rgb(16777088);        //out: 	array (0 => 255,1 => 255, 2 => 128)
	 *     Gd::rgb('#6080af');       //out: 	array (0 => 0x60,1 =>0x80, 2 => 0xaf)
	 *     Gd::rgb(16777088, true);  //out: 	#ffff80
	 *     Gd::rgb(array (0 => 255,1 => 255, 2 => 128));		//out: 	16777088
	 *     Gd::rgb(array (0 => 255,1 => 255, 2 => 128), true);  //out: 	#ffff80 </pre>
	 */
	public static function rgb($param, $htmlout = false){
	    return \Whilegit\Utils\Image\Common::rgb($param, $htmlout);
	}

	
	public static function info($param){
		if(is_string($param)){
			//检测图像文件, 当本地文件时才判断如下if语句，否则如果是http外网图片时不判断
			if (substr($param, 0, 4) != 'http' && !is_file($imgname)) {
				throw new \InvalidArgumentException("\$param指定的图像不存在($param)", '参数错误');
			}
			
			//获取图像信息
			$info = getimagesize($param);
			
			//检测图像合法性
			if (false === $info || (IMAGETYPE_GIF === $info[2] && empty($info['bits']))) {
				exit('非法图像文件');
			}
			
			//设置图像信息
			return array(
					'width'  => $info[0],
					'height' => $info[1],
					'type'   => image_type_to_extension($info[2], false),
					'mime'   => $info['mime'],
			);
		} else if(is_resource($param)){
			$width = imagesx($param);
			$height = imagesy($param);
		} else {
			throw new \InvalidArgumentException('$param的类型必须为string或resource', '参数错误');
		}
	}
}