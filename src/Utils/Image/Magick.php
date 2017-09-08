<?php
namespace Whilegit\Utils\Image;
use Whilegit\Utils\Trace;

/**
 * ImageMagick-7.0.7-0-Q8-x64-dll.exe的包装库
 * @author Linzhongren
 */
class Magick{
	
	protected $inputs = array();
	protected $commands = array();
	
	/**
	 * 输入文件或参数
	 * @desc 输入文件或参数，可以为多个，用逗号分隔
	 */
	public function input(/*输入参数，可以为多个，用逗号分隔*/){
		$this->inputs = func_get_args();
		return $this;
	}
	
	
	/**
	 * 执行并输出
	 * @param string $output 输出路径
	 * @throws \Exception
	 */
	public function output($output){
		if(empty($this->inputs)) throw new \Exception('无输入参数');
		if(empty($output)) throw new \Exception('无输出参数');

		$inputs = is_array($this->inputs) ? implode(' ', $this->inputs) : $this->inputs;
		$commands = !empty($this->commands) ? implode(' ', $this->commands) : '';
		
		$cmd = "magick {$inputs} {$commands} {$output}";
		Trace::out($cmd);
		$cmd = mb_convert_encoding ( $cmd , "gb2312");
	
		exec ($cmd, $output_info, $return_val);
		$this->inputs = null;
		$this->commands = array();
		if($return_val != 0){
			throw new \Exception("命令执行错误。\r\n命令：{$cmd}\r\n信息：\r\n".var_export($output_info, true));
		}
	}
	
	/**
	 * gamma校正
	 * @param string $input  输入文件的路径(必须存在)
	 * @param string $output 输出文件的路径
	 * @param string $gamma  期望矫正的gamma值
	 * @desc <pre>
	 * 由于显示器对颜色值的非线性显示，致使图片失真。如：8位亮度128时，在显示器中却显示55.7的亮度---【(128/256)^(1/0.45455) * 256，指数非线性】
	 * 因此在送入显示器前应主动调整亮度，以抵消显示器的非线性失真。
	 * gamma值为2.2时，刚好补偿显示器的非线性失真；如果图像已经过gamma校正，给一个1/2.2(即0.45455)的gamma值使图像还原。
	 * gamma校正的公式为：y = x ^ (1/gamma)， 其中x为校正前归一化的亮度值(8位时色值除以256，在0~1.0之间)，y为校正后的归一化亮度值。
	 * gamma小于1时，让图像变暗；gamma大于1时，让图像变亮。
	 * </pre>
	 * @example 
	 *    Magick::gamma('E:/gamma.gif', 'E:/gamma_.gif', 0.8);   // 对应命令行 magick 'E:/gamma.gif' -gamma 0.8 'E:/gamma_.gif'
	 */
	public function gamma($gamma){
		$this->commands[] = "-gamma {$gamma}";
		return $this;
	}
	
	
	/**
	 * 调整图像尺寸
	 * @param string $input   输入文件位置
	 * @param string $output  输出文件位置
	 * @param string $size    新的图像尺寸 <pre>
	 * 		1.  '200%'      图像的width和height都放大2倍
	 *      2.  '200x50%'   图像的width拉长stretch至200%, 而height则squash至50%(可选格式：200x50%, 200%x50, 200%x50%)
	 *      3.  '100x200'   图像保持长宽比的同时，能够完全放进100*200方框内的最大尺寸，最终的尺寸需要计算确定
	 *      4.  '100x200^'  图像保持长度比的同时，图像的width要不小于100，height不小于200，最终的尺寸要计算确定
	 *      5.  '100x200!'  图像的width定为100，height定为200，精确输出想要的尺寸
	 *      6.  '100'       图像的width定为100，height跟据原来的长宽比(aspect ratio) 计算获得
	 *      7.  'x200'      图像的height定为200，width跟据原来的长宽比计算获得
	 *      8.  '100x200>'  如果图像的width大于100或者height大于200，则执行  100x200 的操作(见第3条)，否则忽略本指令
	 *      9.  '100x200<'  如果图像的width小于100或者height小于200，则执行  100x200 的操作(见第3条)，否则忽略本指令
	 *      10. '10000@'    保持长度比的同时，图像的面积不超过10000的最大width和height.
	 *   </pre>
	 */
	public function resize($size){
		$size = str_replace('*','x', $size);
		$this->commands[] = "-resize {$size}";
		return $this;
	}
	
	/**
	 * 反显
	 * @return \Whilegit\Utils\Image\Magick
	 */
	public function negate(){
		$this->commands[] = "-negate";
		return $this;
	}
	

	/**
	 * 旋转图片
	 * @param float $degree  顺时针为正，逆时针为负
	 * @return \Whilegit\Utils\Image\Magick
	 * @example -rotate 45
	 * @example -rotate "45>"   表示当width>height时，执行本次旋转操作
	 * @example -rotate "45<"   表示当width《height时，执行本次旋转操作  
	 */
	public function rotate($degree){
		$this->commands[] = "-rotate {$degree}";
		return $this;
	}
	
	/**
	 * 画文字
	 * @param unknown $x
	 * @param unknown $y
	 * @param unknown $str
	 * @return \Whilegit\Utils\Image\Magick
	 */
	public function draw_text($x, $y, $str){
		$this->commands[] = "-draw \"text {$x},{$y} '{$str}'\"";
		return $this;
	}
	
	public function setting_font($font){
		$this->commands[] = "-font {$font}";
		return $this;
	}
	
	public function setting_pointsize($size){
		$this->commands[] = "-pointsize {$size}";
		return $this;
	}
	
	/**
	 * 设置背景色
	 * @param string $color
	 * @return \Whilegit\Utils\Image\Magick
	 * @desc 设置后，rotate等操作时其设置的背景色就能发挥作用
	 * @example <pre> 
	 * -background rgba(r,g,b,a) 用于设置有透明度的背景色，其中a为0时表示透明
	 * -background blue
	 * -background #f00
	 * -background #ddddff
	 * -background rgb(r,g,b)
	 * </pre>
	 */
	public function setting_background($color){
		$this->commands[] = "-background {$color}";
		return $this;
	}
	
	/**
	 * 指定一块区域，供其它指令使用
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 * @return \Whilegit\Utils\Image\Magick
	 * @example region(10,20,100,200)->negate()   起点为(10,20)，宽高为100*200的矩形进行反显
	 * @desc 如指定的区域超过图像尺寸，则超出部分无效
	 */
	public function region($x, $y, $width, $height){
		//xy非负时，必须提供+号
		$x = $x >= 0 ? '+'.$x : $x;
		$y = $y >= 0 ? '+'.$y : $y;
		$this->commands[] = "-region {$width}x{$height}{$x}{$y}";
		return $this;
	}
	
	/**
	 * 配置: 建议图片的重心位置
	 * @param string $type 可选值为：NorthWest, North, NorthEast, West, Center, East, SouthWest, South, SouthEast
	 * @desc 该配置能够影响后续指令的定位起始点 <pre>
	 *  //该指定首先将logo:图像的中心点作为偏移的起始点，将该起始点偏移(-10,20)得到一个新的点，因为还是受-gravity center影响，此点不再是方框的左上角，而是中心点
	 *  magick logo: -gravity center -region '100x200-10+20' -negate wizNeg3.png
	 * </pre>
	 */
	public function setting_gravity($type){
		$type = ucfirst(strtolower($type));
		$this->commands[] = "-gravity $type";
		return $this;
	}
	
	/**
	 * 测试ImageMagick是否可用，并获取版本信息
	 * @throws \Exception
	 * @return string
	 * @example 对应命令行 magick --version
	 */
	public static function info(){
		exec ('magick --version', $output_info, $return_val);
		if($return_val != 0){
			throw new \Exception('ImageMagick未找到，请到https://www.imagemagick.org下载安装。');
		}
	}
	
}