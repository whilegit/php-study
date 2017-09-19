<?php
namespace Whilegit\Utils\Image;
//use Whilegit\Utils\Trace;
use Whilegit\Utils\Misc;

/**
 * ImageMagick-7.0.7-0-Q8-x64-dll.exe的包装库
 * @author Linzhongren
 * @method Magick gamma($gamma)
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
		//if(empty($this->inputs)) throw new \Exception('无输入参数');
		if(empty($output)) throw new \Exception('无输出参数');

		$inputs = is_array($this->inputs) ? implode(' ', $this->inputs) : $this->inputs;
		$commands = !empty($this->commands) ? implode(' ', $this->commands) : '';
		
		//区分Linux和Windows的命令行差别，特别是Windows需要将UTF-8转成GBK，否则命令行中的中文不被识别
		if(Misc::is_windows()){
			$cmd = "magick {$inputs} {$commands} {$output}";
			$cmd = mb_convert_encoding ( $cmd , "gbk");
		} else {
			$cmd = "convert {$inputs} {$commands} {$output}";
		}
		//\Whilegit\Utils\Trace::out($cmd);
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
	public function setting_gamma($gamma){
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
	 * 横轴镜像
	 * @return Magick
	 */
	public function flip(){
		$this->commands[] = "-flip";
		return $this;
	}
	
	/**
	 * 纵轴镜像
	 * @return Magick
	 */
	public function flop(){
		$this->commands[] = "-flop";
		return $this;
	}

	/**
	 * 油画效果
	 * @param string $radius 半径
	 * @return Magick
	 * @desc 每个点都被其半径范围内的最高频的颜色取代
	 */
	public function paint($radius){
		$this->commands[] = "-paint {$radius}";
		return $this;
	}
	
	/**
	 * 黑白画效果
	 * @return Magick
	 */
	public function monochrome(){
		$this->commands[] = "-monochrome";
		return $this;
	}
	
	/**
	 * 木炭画效果
	 * @param int $factor 因子  越大越浓
	 * @return \Whilegit\Utils\Image\Magick
	 */
	public function charcoal($factor){
		$this->commands[] = "-charcoal {$factor}";
		return $this;
	}
	
	/**
	 * 毛玻璃效果 <br />
	 * @desc 可能算法为每个点随机选择周边某个点的颜色作为本点的最终颜色
	 * @param int $amount 越大效果越重
	 * @return Magick
	 */
	public function spread($amount){
		$this->commands[] = "-spread {$amount}";
		return $this;
	}
	
	/**
	 * 中心漩涡效果
	 * @param int $degree  角度
	 * @return Magick
	 */
	public function swirl($degree){
		$this->commands[] = "-swirl {$degree}";
		return $this;
	}
	
	/**
	 * 凸起或凹陷边框，使图片具有3D效果(看起来凸起和凹陷之间看起来区别不大)
	 * @param unknown $thickness  凸起或凹陷的距离
	 * @param boolean $flag       true为凸起，false为凹陷
	 * @return Magick
	 */
	public function raise($thickness, $flag = true){
		$this->commands[] = ($flag ? '-' : '+') . "raise {$thickness}";
		return $this;
	}
	
	/**
	 * 一维高斯模糊, 比Horizontal再Vertical，比二维高斯模糊gaussion_blur()快很多 <br />
	 * @desc 高斯模糊采用正态分布的概率密度分布函数，按照远近，确定本点的加权平均值(卷积 convolve)
	 * @param int $radius   半径 (半径越大计算量越大，应至少是$sigma的两倍，建议3倍)
	 * @param int|string $sigma 标准差
	 * @return Magick
	 */
	public function blur($radius, $sigma = ''){
		if(!empty($sigma)) $sigma = 'x' . $sigma;
		$this->commands[] = "-blur {$radius}{$sigma}";
		return $this;
	}
	
	/**
	 * 二维高斯模糊, 比一维高斯模糊blur()要慢很多 <br />
	 * @desc 高斯模糊采用正态分布的概率密度分布函数，按照远近，确定本点的加权平均值(卷积 convolve)
	 * @param unknown $radius   半径 (半径越大计算量越大，应至少是$sigma的两倍，建议3倍)
	 * @param int|string $sigma 标准差
	 * @return Magick
	 */
	public function gaussion_blur($radius, $sigma = ''){
		if(!empty($sigma)) $sigma = 'x' . $sigma;
		$this->commands[] = "-gaussian-blur {$radius}{$sigma}";
		return $this;
	}
	
	/**
	 * 加边框
	 * @param $geometry      几何，可以是5(表示上下左右都是5个像素)或者5x10(表示左右是5个像素，上下是10个像素)
	 * @param string $color  颜色
	 * @return Magick
	 */
	public function frame($geometry, $color = '#BDBDBD'){
		$this->commands[] = "-mattecolor {$color} -frame {$geometry}";
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
	 * 裁剪图片(；若提供了$x和$y，则只裁剪出一张图片) <br />
	 * @desc 若不提供$x和$y，则均匀切割成多张小图片(从左到右，从上到下)，每张小图片的文件名为output文件名中加-n
	 * @param int $width
	 * @param int $height
	 * @param int|null $x
	 * @param int|null $y
	 * @return Magick
	 */
	public function crop($width, $height, $x = null, $y = null){
		$xy = '';
		if($x !== null && $y !== null){
			$xy .= sprintf('%+d%+d', $x, $y);
		}
		$this->commands[] = "-crop {$width}x{$height}{$xy}";
		return $this;
	}
	
	/**
	 * 画文字
	 * @desc 调用前建议设置一下font字体和pointsize字体大小
	 * @param int    $x    文字的左上角位置  left-upper corner
	 * @param int    $y    
	 * @param string $str  文字(支持中文)
	 * @return \Whilegit\Utils\Image\Magick
	 */
	public function draw_text($x, $y, $str){
		$this->commands[] = "-draw \"text {$x},{$y} '{$str}'\"";
		return $this;
	}
	
	/**
	 * 设置图片标签(输出图像的格式可以是tiff/png/miff等)
	 * @desc <br /> 如写入文字有中文时，请指定字体文件。windows平台请转成gbk码
	 * @param String $str
	 * @return Magick
	 * @example magick -background none -fill white -pointsize 72 -font msyh.ttf label:你好  label_Hello.png
	 */
	public function setting_label($text){
		$this->commands[] = "label:$text";
		return $this;
	}
	
	
	
	/**
	 * 设置图片合成的方法
	 * @param string $method  支持的方法如下：src(覆盖物overlay), dest(底图，最后保留的图片)<pre>
	 *  Src_Over : src置于dest之上
	 *  Dst_Over : dest置于src之上
	 *  Src	     : 清空dest的内容(保留长宽等meta-data)，src的内容替换进去(如dest尺寸较大，还可以设置生成图片的alpha通道,即dest未被覆盖部分设为透明，命令：-alpha set output_file.png) ??????????????????????????
	 *  </pre>
	 * @return Magick
	 */
	public function compose($method){
		$this->commands[] = "-compose {$method}";
		return $this;
	}
	
	/**
	 * 混合图片(这是一个独立程序，应紧邻magick之后)
	 * @desc <br /> 接受-compose设置的混合方法(见compose)，接受一个-geometry设置，两个输入图片(前一个是src,后一个是dest)，最后一个是输出图片
	 * @example magick composite -compose Dst_Over -geometry +5+5 label_A_black.png label_A_white.png label_A.png
	 * @return Magick
	 */
	public function composite(){
		$this->commands[] = "composite";
		return $this;
	}
	
	/**
	 * 绘制矩形  
	 * @desc <br />填充色可由setting_fill($color)设定
	 * @desc <br/> setting_stroke($color) 指定边框线的颜色
	 * @desc <br/> setting_strokewidth($width) 指定边框线的宽度，注：若没有调用setting_stroke设置线色，貌似线宽设置无效
	 * @param int $x0    左上角x轴坐标
	 * @param int $y0
	 * @param int $x1   右下角y轴坐标
	 * @param int $y1
	 * @return Magick
	 */
	public function draw_rectangle($x0, $y0, $x1, $y1){
		$this->commands[] = "-draw \"rectangle {$x0},{$y0} {$x1},{$y1}\"";
		return $this;
	}
	
	/**
	 * 绘制圆角矩形 
	 * @desc <br />填充色可由setting_fill($color)设定
	 * @desc <br/> setting_stroke($color) 指定边框线的颜色
	 * @param int $x0 左上角x轴坐标
	 * @param int $y0
	 * @param int $x1 右下角y轴坐标
	 * @param int $y1
	 * @param int $wc 水平方向切掉的宽度
	 * @param int $hc 垂直方向切掉的高度
	 * @return Magick
	 */
	public function draw_roundRectangle($x0, $y0, $x1, $y1, $wc, $hc){
		$this->commands[] = "-draw \"roundRectangle {$x0},{$y0} {$x1},{$y1} {$wc},{$hc}\"";
		return $this;
	}
	
	/**
	 * 绘制圆
	 * @desc <br />填充色可由setting_fill($color)设定
	 * @desc <br/> setting_stroke($color) 指定边框线的颜色
	 * @param int $x0           圆心x坐标
	 * @param int $y0           圆心y坐标
	 * @param int $x1           半径或圆周上任一点的x坐标
	 * @param int $y1           若提供，则与$x1一起构成圆周上的一点坐标；null时，$x1将被解析成半径r
	 * @return Magick
	 */
	public function draw_circle($x0, $y0, $x1, $y1 = null){
		if(empty($y1)){
			$x1 = $x0 + $x1;
			$y1 = $y0;
		}
		$this->commands[] = "-draw \"circle {$x0},{$y0} {$x1},{$y1}\"";
		return $this;
	}
	
	/**
	 * 绘制全部或部分椭圆 ellipse
	 * @desc <br />填充色可由setting_fill($color)设定
	 * @desc <br/> setting_stroke($color) 指定边框线的颜色
	 * @param int $x0 
	 * @param int $y0
	 * @param int $rx x方向的半长轴
	 * @param int $ry y方向的半长轴
	 * @param int $a0  起点角度  (注意: gravity设为NorthWest的情况下, 第1象限在左下方)
	 * @param int $a1  终点角度
	 * @return Magick
	 */
	public function draw_ellipse($x0, $y0, $rx, $ry, $a0 = 0, $a1 = 360){
		$this->commands[] = "-draw \"ellipse {$x0},{$y0} {$rx},{$ry} {$a0},{$a1}\"";
		return $this;
	}
	
	/**
	 * 绘制矩形框内接的全部或部分椭圆
	 * @desc <br/> 与draw_ellipse()的区别是，draw_arc由外接矩形确定椭圆的位置
	 * @desc <br/> 填充色可由setting_fill($color)设定
	 * @desc <br/> setting_stroke($color) 指定边框线的颜色
	 * @param int $x0   矩形左上角x坐标
	 * @param int $y0
	 * @param int $x1   矩形右下角x坐标
	 * @param int $y1
	 * @param int $a0   内置椭圆的起始角度
	 * @param int $a1   内置椭圆的终点角度
	 * @return Magick
	 */
	public function draw_arc($x0, $y0, $x1, $y1, $a0 = 0, $a1 = 360){
		$this->commands[] = "-draw \"arc {$x0},{$y0} {$x1},{$y1} {$a0},{$a1}\"";
		return $this;
	}
	
	/**
	 * 画点
	 * @desc 颜色由setting_fill()指定?，不受setting_stroke($color)和setting_strokewidth($width)影响
	 * @param int $x
	 * @param int $y
	 * @return Magick
	 */
	public function draw_point($x, $y){
		$this->commands[] = "-draw \"point {$x},{$y}\"";
		return $this;
	}
	
	/**
	 * 画线 
	 * @desc <br/> setting_stroke($color) 指定线的颜色,若未指定则露出setting_fill($color)设置的背景色
	 * @desc <br/> setting_strokewidth($width) 指定线的宽度，注：若没有调用setting_stroke设置线色，貌似线宽设置无效
	 * @param int $x0  起点x坐标
	 * @param int $y0
	 * @param int $x1  终点y坐标
	 * @param int $y1
	 * @return Magick
	 */
	public function draw_line($x0, $y0, $x1, $y1){
		$this->commands[] = "-draw \"line {$x0},{$y0} {$x1},{$y1}\"";
		return $this;
	}
	
	/**
	 * 绘制不自动封闭的多边形
	 * @desc <br/> 参见自动封闭 draw_polygon()
	 * @param array(array) $pnts   点集
	 * @return Magick
	 */
	public function draw_polyline($pnts){
		if(!empty($pnts)){
			$pnts_str = array_reduce($pnts, function($carry, $item){
				$x = isset($item[0]) ? $item[0] : $item['x'];
				$y = isset($item[1]) ? $item[1] : $item['y'];
				return "{$carry} {$x},{$y}";
			}, '');
			$this->commands[] = "-draw \"polyline " . trim($pnts_str) . "\"";
		}
		return $this;
	}
	
	
	/**
	 * 边缘检测
	 * @param int $radius
	 * @return Magick
	 */
	public function edge($radius){
		$this->commands[] = "-edge {$radius}";
		return $this;
	}
	
	/**
	 * canny边缘检测
	 * @param int $radius    高斯模糊的半径
	 * @param int $sigma     高斯模糊的标准差
	 * @param int $lower     低值%
	 * @param int $upper     高值%，越大边缘的细节越小
	 * @return Magick
	 */
	public function canny($radius, $sigma, $lower, $upper){
		$range = sprintf('%+d%%%+d%%', $lower, $upper);
		$this->commands[] = "-canny {$radius}x{$sigma}{$range}";
		return $this;
	}
	
	/**
	 * 蒙太奇拼接图片(这是一个独立程序，拥有其独立的参数表)
	 * @return Magick
	 * @example magick montage -label %f -frame 5 -background '#336699' -geometry +4+4 rose.jpg red-ball.png frame.jpg
	 */
	public function montage(){
		$this->commands[] = "montage";
		return $this;
	}
	
	/**
	 * 交换、提出或复制一个或多个图片通道(RGB通道/Alpha通道) 
	 * @desc <br /> 不能理解 -channel-fx "red;green;blue" 代表什么含义
	 * @param string $exp
	 * @return Magick
	 * @desc <br />实例中，真彩色有red,green,blue三个通道,png格式的图片可能还有alpha通道
	 * @example -channel-fx "red=0" //图像通道red全部置为某一个灰度值(0~255)，也可以是red=50%（等效于red=128）
	 * @example -channel-fx "red"   //提取red通道，如想单独保存此通道，可以保存为.pgm(portable gray map)格式的文件名
	 * @example -channel-fx "red,blue,green"  //按照一定的通道顺序输出，输入(r100,g120,b140) => (r100,g140,b120)
	 * @example -channel-fx "red=>green"      //red通道的值替换green通道的值 (r100,g120,b140) => (r100,g100,b140)
	 * @example -channel-fx "red=>alpha"      //alpha通道的值由red替换，没有红色的区域将变成透明
	 * @example -channel-fx "red<=>green"     //red通道和green通道互换 (r100,g120,b140) => (r120,g100,b140)
	 * @example magick logo: mask.pgm -channel-fx "| gray=>alpha" logo-mask.png  //logo-mask.png的alpha通道为mask.pgm这个灰度文件
	 */
	public function channel_fx($exp){
		$this->commands[] = "-channel-fx \"{$exp}\"";
		return $this;
	}
	
	
	/**
	 * 拼接图片
	 * @desc <br /> 拼接前可设置setting_background(...)和setting_gravity(...)
	 * @param string $align   vertical | horizontal
	 * @return Magick
	 */
	public function append($align='vertical'){
		$sign = $align == 'vertical' ? '-' : '+';
		$this->commands[] = "{$sign}append";
		return $this;
	}
	
	/**
	 * 增加或减少颜色数量 
	 * @desc <br /> 对于索引图像，文件大小可能会显著改变。
	 * @param int $count 希望的颜色数(最终生成的图像颜色数不超过此值)
	 * @return Magick
	 */
	public function colors($count){
		$this->commands[] = "-colors {$count}";
		return $this;
	}
	
	/**
	 * 绘制自动封闭的多边形
	 * @desc <br/> 参见不自动封闭
	 * @param array(array) $pnts  点集
	 * @return Magick
	 */
	public function draw_polygon($pnts){
		if(!empty($pnts)){
			$pnts_str = array_reduce($pnts, function($carry, $item){
				$x = isset($item[0]) ? $item[0] : $item['x'];
				$y = isset($item[1]) ? $item[1] : $item['y'];
				return "{$carry} {$x},{$y}";
			}, '');
				$this->commands[] = "-draw \"polygon " . trim($pnts_str) . "\"";
		}
		return $this;
	}
	
	/**
	 * 设置线的颜色 <br/>在调用draw_line之前调用本方法
	 * @param Color $color
	 * @return Magick
	 */
	public function setting_stroke($color){
		$this->commands[] = "-stroke {$color}";
		return $this;
	}
	
	/**
	 * 设置线的宽度 <br/>在调用draw_line之前调用本方法
	 * @param int $width
	 * @return Magick
	 */
	public function setting_strokewidth($width){
		$this->commands[] = "-strokewidth {$width}";
		return $this;
	}
	
	/**
	 * 设置字体文件
	 * @desc  draw_text命令可能会用到
	 * @param string $font  字体文件的绝对路径
	 * @return agick
	 */
	public function setting_font($font){
		$this->commands[] = "-font {$font}";
		return $this;
	}
	
	/**
	 * 设置字体的大小(像素)
	 * @desc 可能draw_text画文字时会使用到
	 * @param int $size
	 * @return Magick
	 */
	public function setting_pointsize($size){
		$this->commands[] = "-pointsize {$size}";
		return $this;
	}
	
	/**
	 * 设置背景色
	 * @param string $color
	 * @return Magick
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
	 * 设置填充色， 可应用于draw_recktangle()函数
	 * @param Color $color 同setting_background($color)的参数格式或none
	 * @return Magick
	 */
	public function setting_fill($color){
		$this->commands[] = "-fill {$color}";
		return $this;
	}
	
	/**
	 * 指定一块区域，供其它指令使用
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 * @return Magick
	 * @example region(10,20,100,200)->negate()   起点为(10,20)，宽高为100*200的矩形进行反显
	 * @desc 如指定的区域超过图像尺寸，则超出部分无效
	 */
	public function setting_region($x, $y, $width, $height){
		//xy非负时，必须提供+号
		$x = $x >= 0 ? '+'.$x : $x;
		$y = $y >= 0 ? '+'.$y : $y;
		$this->commands[] = "-region {$width}x{$height}{$x}{$y}";
		return $this;
	}
	
	/**
	 * 设置几何尺寸
	 * @param string $geometry  参数含义参照resize()
	 * @return Magick
	 */
	public function setting_geometry($geometry){
		$this->commands[] = "-geometry {$geometry}";
		return $this;
	}
	
	/**
	 * 设置Raw图片的尺寸
	 * @param string $geometry  w x h + offset  如果raw数据中有头部，可以用offset跳过
	 * @example magick -size 200x200 plasma:tomato-dodgerblue tomato.png
	 * @return Magick
	 */
	public function setting_size($geometry){
		$this->commands[] = "-size {$geometry}";
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
	
	public function __call($name, $arguments){
		$cmd = "-{$name}";
		if(!empty($arguments)){
			$cmd .= ' ' . implode(' ', $arguments);
		}
		$this->commands[] = $cmd;
		return $this;
	}
	
	/**
	 * 添加文件项，参数可以是一个或多个，参数类型可以是string或array
	 * @return Magick
	 */
	public function addImages(){
		$arr = array();
		$num = func_num_args();
		for($i = 0; $i<$num; $i++){
			$arg = func_get_arg($i);
			if(is_array($arg)){
				foreach($arg as $a) array_push($arr, $a);
			} else {
				array_push($arr, $arg);
			}
		}
		if(!empty($arr)){
			$this->commands[] = implode(' ', $arr);
		}
		return $this;
	}
	
	/**
	 * 直接添加命令，参数可以是一个或多个，参数类型可以是string或array
	 * @desc <br /> 注意：自备  - 符号。
	 * @return mixed
	 */
	public function addCmd(){
		$argvs = func_get_args();
		call_user_func(array($this, 'addImages'), $argvs);
		return $this;
	}
	
	/**
	 * 子表达式 (imagemagick支持子表达式，可以对输入进行预处理，预处理的结果替代原参数的地位)
	 * @param string $b 可以使用{ }或( )或< >三对符号，最后都转化成( )
	 * @throws \Exception
	 * @return Magick
	 */
	public function stack($b){
		if(!in_array($b, array('{', '(', '}', ')','<', '>'))){
			throw new \Exception("参数错误，只能是{(<>)}");
		}
		$b = ($b == '{' || $b == '(' || $b == '<') ? '(' : ')';
		$this->commands[] = $b;
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