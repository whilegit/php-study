<?php
namespace Whilegit\Utils\Image;
/**
 * 图片验证码封装库
 * @author Linzhongren
 * @example <pre>
 * $captcha = Misc::random(6);
 * //保存验证码
 * $image_captchar = new ImageCaptcha($captcha);
 * $image_captchar->output();
 * exit; </pre>
 */
class ImageCaptcha{
	protected $maxAngle = 15;
	protected $maxOffset = 5;
	
	protected $image = null;
	protected $width = 100;
	protected $height = 60;
	protected $area = 6000;
	protected $captcha = '';
	protected $font_path = "";
	protected $background_color;
	
	/**
	 * 构造函数（也是实际处理过程）
	 * @param string $captcha 验证码
	 * @param number $width   宽度
	 * @param number $height  高度
	 * @param string $font    字体文件(未提供则使用 static/font/captcha.ttf)
	 * @throws \Exception
	 */
	public function __construct($captcha, $width = 200, $height = 80, $font = ""){
		$this->captcha = $captcha;
		$this->width = $width;
		$this->height = $height;
		$this->area = $width * $height;
		//新建一个真彩色图像资源，代表了一幅大小为 x_size 和 y_size 的黑色图像
		$this->image = @imageCreateTrueColor($width, $height);
		if(empty($this->image)) {
			throw new \Exception("未加载gd库");
		}
		//设置字体
		$this->font_path = !empty($font) ? $font : __DIR__ . '/../../static/font/captcha.ttf';
		
		// 创建颜色资源，以备应用于该图像
		// 第一次对 imageColorAllocate() 的调用会给基于调色板的图像填充背景色，即用 imagecreate() 建立的图像。
		$this->background_color = imageColorAllocate($this->image, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
		imagefill($this->image, 0, 0, $this->background_color);
		
		//生成图片
		$this->build();
	}
	
	/**
	 * 返回图片资源，可以在此基础上继续处理
	 * @return image
	 * @desc 请在output()前调用
	 */
	public function image(){
		return $this->$image;
	}
	
	/**
	 * 输出image
	 * @param String|null $to 提供文件地址，若无则直接输入到客户端
	 */
	public function output($to = null) {
		ob_clean();
		header('content-type: image/png');
		//发送到客户端
		imagePng($this->image, $to);
		//销毁资源
		imageDestroy($this->image);
	}
	
	/**
	 * 生成验证码图片
	 */
	protected function build() {
		//画一些干扰直线
		$effects = mt_rand($this->area/666, $this->area/333);
		for ($e = 0; $e < $effects; $e++) {
			$this->drawRandomLine();
		}
	
		//画验证码内容
		$color = $this->writePhrase();
	
		//再画一些干扰直线
		$effects = mt_rand($this->area/9000, $this->area/6000);
		for ($e = 0; $e < $effects; $e++) {
			$this->drawRandomLine($color);
		}
	
		//扭曲
		$this->distort();
	}
	
	/**
	 * 画一条干扰直线
	 * @param resource $image 图片资源
	 * @param int $width
	 * @param int $height
	 * @param resource $tcol  真彩颜色资源
	 */
	protected function drawRandomLine($tcol = null) {
		if (mt_rand(0, 1)) {
			//从左边到右边的思路生成坐标
			$Xa   = mt_rand(0, $this->width/2);
			$Ya   = mt_rand(0, $this->height);
			$Xb   = mt_rand($this->width/2, $this->width);
			$Yb   = mt_rand(0, $this->height);
		} else {
			//从上部到下部画的思路生成坐标
			$Xa   = mt_rand(0, $this->width);
			$Ya   = mt_rand(0, $this->height/2);
			$Xb   = mt_rand(0, $this->width);
			$Yb   = mt_rand($this->height/2, $this->height);
		}
		//如未设置颜色，则新建一种颜色
		if ($tcol === null) {
			$tcol = imageColorAllocate($this->image, mt_rand(100, 255), mt_rand(100, 255), mt_rand(100, 255));
		}
		//设置线宽
		$pixes = mt_rand(1, 3);
		imageSetThickness($this->image, $pixes);
		//从坐标 ($Xa,$Ya) 画至 ($Xb,$Yb)
		imageLine($this->image, $Xa, $Ya, $Xb, $Yb, $tcol);
	}
	
	/**
	 * 画具体的验证码
	 * @return 画验证码的所用颜色
	 */
	protected function writePhrase() {
		$length = strlen($this->captcha);
		//计算字体的大小(In GD 1, this is measured in pixels. In GD 2, this is measured in points.)
		$size = $this->width / $length + mt_rand(-2, 4);
		
		/*  imagettfbbox的返回值array
		 * 下标
		 *  0	lower left corner(左下角), X position
		 *	1	lower left corner, Y position
		 *  2	lower right corner(右下角), X position
		 *  3	lower right corner, Y position
		 *  4	upper right corner(右上角), X position
		 *  5	upper right corner, Y position
		 *  6	upper left corner(左上角), X position
		 *  7	upper left corner, Y position
		 */
		$box = imagettfbbox($size, 0, $this->font_path, $this->captcha);
		$textWidth = $box[2] - $box[0];
		$textHeight = $box[1] - $box[7];
		$x = ($this->width - $textWidth) / 2;            //文字的起点x位置
		$y = ($this->height - $textHeight) / 2 + $size;  //文字的起点y坐标
	
		//文字颜色
		$col = imagecolorallocate($this->image,mt_rand(0, 150), mt_rand(0, 150), mt_rand(0, 150));
	
		for ($i=0; $i<$length; $i++) {
			//画单个字符,返回本次画
			$box = imagettftext($this->image, 
					$size, 	            //字体大小
					mt_rand(-$this->maxAngle, $this->maxAngle),         //角度
					$x,                 //x坐标
					$y + mt_rand(-$this->maxOffset, $this->maxOffset),  //y坐标
					$col,               //颜色
					$this->font_path,        //字体文件
					$this->captcha[$i]); //文字
			//下一个字符的x坐标位置
			$x += ($box[2] - $box[0]) + mt_rand(-5,5);
		}
		return $col;
	}
	
	/**
	 * 扭曲滤镜
	 * @desc 算法未知
	 */
	protected function distort() {
		$contents = imagecreatetruecolor($this->width, $this->height);
		$X = mt_rand(0, $this->width);
		$Y = mt_rand(0, $this->height);
		$phase = mt_rand(0, 10);
		$scale = 1.5 + mt_rand(0, 10000) / 30000;
		for ($x = 0; $x < $this->width; $x++) {
			for ($y = 0; $y < $this->height; $y++) {
				$Vx = $x - $X;
				$Vy = $y - $Y;
				$Vn = sqrt($Vx * $Vx + $Vy * $Vy);
		
				if ($Vn != 0) {
					$Vn2 = $Vn + 4 * sin($Vn / 30);
					$nX  = $X + ($Vx * $Vn2 / $Vn);
					$nY  = $Y + ($Vy * $Vn2 / $Vn);
				} else {
					$nX = $X;
					$nY = $Y;
				}
				$nY = $nY + $scale * sin($phase + $nX * 0.2);
				
				$fnX = floor($nX);
				$cnX = ceil($nX);
				$fnY = floor($nY);
				$cnY = ceil($nY);
				$nw = $this->getCol($fnX, $fnY);
				$ne = $this->getCol($cnX, $fnY);
				$sw = $this->getCol($fnX, $cnY);
				$se = $this->getCol($cnX, $cnY);
				
				$xx = $nX - $fnX;
				$yy = $nY - $fnY;
				list($r0, $g0, $b0) = $nw;
				list($r1, $g1, $b1) = $ne;
				list($r2, $g2, $b2) = $sw;
				list($r3, $g3, $b3) = $se;
				
				$cx = 1.0 - $xx;
				$cy = 1.0 - $yy;
				
				$m0 = $cx * $r0 + $xx * $r1;
				$m1 = $cx * $r2 + $xx * $r3;
				$r  = (int) ($cy * $m0 + $yy * $m1);
				
				$m0 = $cx * $g0 + $xx * $g1;
				$m1 = $cx * $g2 + $xx * $g3;
				$g  = (int) ($cy * $m0 + $yy * $m1);
				
				$m0 = $cx * $b0 + $xx * $b1;
				$m1 = $cx * $b2 + $xx * $b3;
				$b  = (int) ($cy * $m0 + $yy * $m1);
				
				$p = ($r << 16) + ($g << 8) + $b;
				if ($p == 0) {
					$p = $this->background_color;
				}
				imagesetpixel($contents, $x, $y, $p);
			}
		}
		$this->image = $contents;
	}
	
	/**
	 * 取色
	 * @param int $x
	 * @param int $y
	 * @return 返回像素点($x,$y)的整型rgb
	 */
	protected function getCol($x, $y) {
		if ($x < 0 || $x >= $this->width || $y < 0 || $y >= $this->height) {
			$color = $this->background_color;
		} else{
			$color = imagecolorat($this->image, $x, $y);
		}
		return array(
				($color >> 16) & 0xff,
				($color >> 8) & 0xff,
				($color) & 0xff );
	}
}