<?php
namespace Whilegit\Utils\Image\Formats;
use Whilegit\Utils\Image\Magick;
use Whilegit\Utils\Trace;
use Whilegit\Utils\Image\Common;
use Whilegit\Utils\IArray;
class Pgm{
	
	public $width;
	public $height;
	public $data;   //int数组
	
	//private $bitmap;
	
	private function __construct(&$data, $width, $height){
		$this->data = &$data;
		$this->width = $width;
		$this->height = $height;
	}
	
	public static function parseFromStr($data, $width, $height){
		if(!is_array($data)){
			$data = str_replace(array("\n","\r"),' ',$data);
			$data = preg_replace("/\\s+/", ' ', $data);
			$data = explode(' ', $data);
			array_walk($data, function(&$val){
				$val = intval($val);
			});
		} else {
			if(!is_int($data[0])){
				array_walk($data, function(&$val){
					$val = intval($val);
				});
			}
		}
		
		return new Pgm($data, $width, $height);
	}
	
	/**
	 * 输出P2或P5文件
	 * @param string $path 要保存的文件路径
	 * @param string $type 类型，可选P2(ascii)或P5(binary)
	 * @return false|int 如写成功则输出写入的字符数，如失败则返回false
	 */
	public function output($path, $type = 'P2'){
		$result = "{$type}\r\n{$this->width}\r\n{$this->height}\r\n255\r\n";
		if($type == 'P2'){
			$lines = array();
			for($i = 0; $i<$this->height; $i++){
				$line = '';
				for($j = 0; $j<$this->width; $j++){
					if(!isset($this->data[$i * $this->width + $j])) continue;
					$s = sprintf("%d", $this->data[$i * $this->width + $j]);
					$s = str_pad($s, 4, ' ');
					$line .= $s;
					if($j % 18 == 17 && $j < $this->width - 1){
						$line .= "\r\n";
					}
				}
				$lines[] = trim($line);
			}
			$content = $result . implode("\r\n", $lines) . "\r\n";
		} else {  //P5
			$content =  $result . pack('C*', $this->data);
		}
		
		return file_put_contents($path, $content);
	}
	
	/**
	 * 读取pgm格式的数据(支持P2[ascii]和P5[binary])
	 * @param string $path   文件路径
	 * @param &int $width     输出的图片宽度
	 * @param &int $height    输出的图片高度
	 * @return 
	 */
	public static function parseFromFile($path){  //read_pgm
		$content = file_get_contents($path);
		if(!empty($content)){
			$data = unpack('C*', $content);
			$format = self::binary_read_meta($data);
			$width = self::binary_read_meta($data);
			$height = self::binary_read_meta($data);
			$max = self::binary_read_meta($data);
			if($format == 'P2'){
				$data = str_replace(array("\n","\r"),' ',$content);
				$data = preg_replace("/\\s+/", ' ', $data);
				$data = explode(' ', $data);
				array_shift($data);  //P2
				$width = array_shift($data);  //width
				$height = array_shift($data);  //height
				array_shift($data);  //max
				array_walk($data, function(&$val){
					$val = intval($val);
				});
			}
		}
		$pgm = new Pgm($data, $width, $height);
		return $pgm;
	}
	
	/**
	 * 生成一副测试用的图片(生成一副16x32的带alpha通道的图像)
	 */
	public static function tg($dest_dir){
		$width = 16;
		$height = 32;
		if(!file_exists($dest_dir)){
			@mkdir($dest_dir, 0777, true);
		}
		$output_file = $dest_dir . '/main.png';
		$output_red_file = $dest_dir . '/red.pgm';
		$output_green_file = $dest_dir . '/green.pgm';
		$output_blue_file = $dest_dir . '/blue.pgm';
		$output_alpha_file = $dest_dir . '/alpha.pgm';
		
		$red = '255 0   0   255 255 0   0   255 0   64  128 160 192 224 240 248
		  255 0   0   255 255 0   0   255 0   64  128 160 192 224 240 248
		  255 0   0   255 255 0   0   255 0   64  128 160 192 224 240 248
		  255 0   0   255 255 0   0   255 0   64  128 160 192 224 240 248
		  255 0   0   255 255 0   0   255 0   64  128 160 192 224 240 248
		  255 0   0   255 255 0   0   255 0   64  128 160 192 224 240 248
		  255 0   0   255 255 0   0   255 0   64  128 160 192 224 240 248
		  255 0   0   255 255 0   0   255 0   64  128 160 192 224 240 248
		  248 224 192 160 128 64  32  16  0   0   0   0   0   0   0   0
		  248 224 192 160 128 64  32  16  0   0   0   0   0   0   0   0
		  248 224 192 160 128 64  32  16  0   0   0   0   0   0   0   0
		  248 224 192 160 128 64  32  16  0   0   0   0   0   0   0   0
		  248 224 192 160 128 64  32  16  0   0   0   0   0   0   0   0
		  248 224 192 160 128 64  32  16  0   0   0   0   0   0   0   0
		  248 224 192 160 128 64  32  16  0   0   0   0   0   0   0   0
		  248 224 192 160 128 64  32  16  0   0   0   0   0   0   0   0
		  0   0   0   0   0   0   0   0	  248 224 192 160 128 64  32  16
		  0   0   0   0   0   0   0   0	  248 224 192 160 128 64  32  16
		  0   0   0   0   0   0   0   0	  248 224 192 160 128 64  32  16
		  0   0   0   0   0   0   0   0	  248 224 192 160 128 64  32  16
		  0   0   0   0   0   0   0   0	  248 224 192 160 128 64  32  16
		  0   0   0   0   0   0   0   0	  248 224 192 160 128 64  32  16
		  0   0   0   0   0   0   0   0	  248 224 192 160 128 64  32  16
		  0   0   0   0   0   0   0   0	  248 224 192 160 128 64  32  16
		  248 224 192 160 128 64  32  16  0   0   0   0   0   0   0   0
		  248 224 192 160 128 64  32  16  0   0   0   0   0   0   0   0
		  248 224 192 160 128 64  32  16  0   0   0   0   0   0   0   0
		  248 224 192 160 128 64  32  16  0   0   0   0   0   0   0   0
		  248 224 192 160 128 64  32  16  0   0   0   0   0   0   0   0
		  248 224 192 160 128 64  32  16  0   0   0   0   0   0   0   0
		  248 224 192 160 128 64  32  16  0   0   0   0   0   0   0   0
		  248 224 192 160 128 64  32  16  0   0   0   0   0   0   0   0
		';
		$pgm = Pgm::parseFromStr($red,$width, $height);
		$pgm->output($output_red_file);
		
		$green = '0   255 0   0   255 255 0   255 0   64  128 160 192 224 240 248
		  0   255 0   0   255 255 0   255 0   64  128 160 192 224 240 248
		  0   255 0   0   255 255 0   255 0   64  128 160 192 224 240 248
		  0   255 0   0   255 255 0   255 0   64  128 160 192 224 240 248
		  0   255 0   0   255 255 0   255 0   64  128 160 192 224 240 248
		  0   255 0   0   255 255 0   255 0   64  128 160 192 224 240 248
		  0   255 0   0   255 255 0   255 0   64  128 160 192 224 240 248
		  0   255 0   0   255 255 0   255 0   64  128 160 192 224 240 248
		  0   0   0   0   0   0   0   0   248 224 192 160 128 64  32  16
		  0   0   0   0   0   0   0   0   248 224 192 160 128 64  32  16
		  0   0   0   0   0   0   0   0   248 224 192 160 128 64  32  16
		  0   0   0   0   0   0   0   0   248 224 192 160 128 64  32  16
		  0   0   0   0   0   0   0   0   248 224 192 160 128 64  32  16
		  0   0   0   0   0   0   0   0   248 224 192 160 128 64  32  16
		  0   0   0   0   0   0   0   0   248 224 192 160 128 64  32  16
		  0   0   0   0   0   0   0   0   248 224 192 160 128 64  32  16
		  0   0   0   0   0   0   0   0	  0   0   0   0   0   0   0   0
		  0   0   0   0   0   0   0   0	  0   0   0   0   0   0   0   0
		  0   0   0   0   0   0   0   0	  0   0   0   0   0   0   0   0
		  0   0   0   0   0   0   0   0	  0   0   0   0   0   0   0   0
		  0   0   0   0   0   0   0   0	  0   0   0   0   0   0   0   0
		  0   0   0   0   0   0   0   0	  0   0   0   0   0   0   0   0
		  0   0   0   0   0   0   0   0	  0   0   0   0   0   0   0   0
		  0   0   0   0   0   0   0   0	  0   0   0   0   0   0   0   0
		  248 224 192 160 128 64  32  16  248 224 192 160 128 64  32  16
		  248 224 192 160 128 64  32  16  248 224 192 160 128 64  32  16
		  248 224 192 160 128 64  32  16  248 224 192 160 128 64  32  16
		  248 224 192 160 128 64  32  16  248 224 192 160 128 64  32  16
		  248 224 192 160 128 64  32  16  248 224 192 160 128 64  32  16
		  248 224 192 160 128 64  32  16  248 224 192 160 128 64  32  16
		  248 224 192 160 128 64  32  16  248 224 192 160 128 64  32  16
		  248 224 192 160 128 64  32  16  248 224 192 160 128 64  32  16
		';
		$pgm =  Pgm::parseFromStr($green,$width, $height);
		$pgm->output($output_green_file);
		
		$blue = '0   0   255 255 0   255 0   255 0   64  128 160 192 224 240 248
		 0   0   255 255 0   255 0   255 0   64  128 160 192 224 240 248
		 0   0   255 255 0   255 0   255 0   64  128 160 192 224 240 248
		 0   0   255 255 0   255 0   255 0   64  128 160 192 224 240 248
		 0   0   255 255 0   255 0   255 0   64  128 160 192 224 240 248
		 0   0   255 255 0   255 0   255 0   64  128 160 192 224 240 248
		 0   0   255 255 0   255 0   255 0   64  128 160 192 224 240 248
		 0   0   255 255 0   255 0   255 0   64  128 160 192 224 240 248
		 0   0   0   0   0   0   0   0   0   0   0   0   0   0   0   0
		 0   0   0   0   0   0   0   0   0   0   0   0   0   0   0   0
		 0   0   0   0   0   0   0   0   0   0   0   0   0   0   0   0
		 0   0   0   0   0   0   0   0   0   0   0   0   0   0   0   0
		 0   0   0   0   0   0   0   0   0   0   0   0   0   0   0   0
		 0   0   0   0   0   0   0   0   0   0   0   0   0   0   0   0
		 0   0   0   0   0   0   0   0   0   0   0   0   0   0   0   0
		 0   0   0   0   0   0   0   0   0   0   0   0   0   0   0   0
		 248 224 192 160 128 64  32  16	 248 224 192 160 128 64  32  16
		 248 224 192 160 128 64  32  16	 248 224 192 160 128 64  32  16
		 248 224 192 160 128 64  32  16	 248 224 192 160 128 64  32  16
		 248 224 192 160 128 64  32  16	 248 224 192 160 128 64  32  16
		 248 224 192 160 128 64  32  16	 248 224 192 160 128 64  32  16
		 248 224 192 160 128 64  32  16	 248 224 192 160 128 64  32  16
		 248 224 192 160 128 64  32  16	 248 224 192 160 128 64  32  16
		 248 224 192 160 128 64  32  16	 248 224 192 160 128 64  32  16
		 0   0   0   0   0   0   0   0   248 224 192 160 128 64  32  16
		 0   0   0   0   0   0   0   0   248 224 192 160 128 64  32  16
		 0   0   0   0   0   0   0   0   248 224 192 160 128 64  32  16
		 0   0   0   0   0   0   0   0   248 224 192 160 128 64  32  16
		 0   0   0   0   0   0   0   0   248 224 192 160 128 64  32  16
		 0   0   0   0   0   0   0   0   248 224 192 160 128 64  32  16
		 0   0   0   0   0   0   0   0   248 224 192 160 128 64  32  16
		 0   0   0   0   0   0   0   0   248 224 192 160 128 64  32  16
		';
		$pgm =  Pgm::parseFromStr($blue,$width, $height);
		$pgm->output($output_blue_file);
		
		$alpha = '255 255 255 255 255 255 255 255 255 255 255 255 255 255 255 255
		  160 160 160 160 160 160 160 160 160 160 160 160 160 160 160 160
		  128 128 128 128 128 128 128 128 128 128 128 128 128 128 128 128
		  96  96  96  96  96  96  96  96  96  96  96  96  96  96  96  96
		  64  64  64  64  64  64  64  64  64  64  64  64  64  64  64  64
		  32  32  32  32  32  32  32  32  32  32  32  32  32  32  32  32
		  16  16  16  16  16  16  16  16  16  16  16  16  16  16  16  16
		  0   0   0   0   0   0   0   0   0   0   0   0   0   0   0   0
		  255 255 255 255 255 255 255 255 255 255 255 255 255 255 255 255
		  160 160 160 160 160 160 160 160 160 160 160 160 160 160 160 160
		  128 128 128 128 128 128 128 128 128 128 128 128 128 128 128 128
		  96  96  96  96  96  96  96  96  96  96  96  96  96  96  96  96
		  64  64  64  64  64  64  64  64  64  64  64  64  64  64  64  64
		  32  32  32  32  32  32  32  32  32  32  32  32  32  32  32  32
		  16  16  16  16  16  16  16  16  16  16  16  16  16  16  16  16
		  0   0   0   0   0   0   0   0   0   0   0   0   0   0   0   0
		  255 255 255 255 255 255 255 255 255 255 255 255 255 255 255 255
		  160 160 160 160 160 160 160 160 160 160 160 160 160 160 160 160
		  128 128 128 128 128 128 128 128 128 128 128 128 128 128 128 128
		  96  96  96  96  96  96  96  96  96  96  96  96  96  96  96  96
		  64  64  64  64  64  64  64  64  64  64  64  64  64  64  64  64
		  32  32  32  32  32  32  32  32  32  32  32  32  32  32  32  32
		  16  16  16  16  16  16  16  16  16  16  16  16  16  16  16  16
		  0   0   0   0   0   0   0   0   0   0   0   0   0   0   0   0
		  255 255 255 255 255 255 255 255 255 255 255 255 255 255 255 255
		  160 160 160 160 160 160 160 160 160 160 160 160 160 160 160 160
		  128 128 128 128 128 128 128 128 128 128 128 128 128 128 128 128
		  96  96  96  96  96  96  96  96  96  96  96  96  96  96  96  96
		  64  64  64  64  64  64  64  64  64  64  64  64  64  64  64  64
		  32  32  32  32  32  32  32  32  32  32  32  32  32  32  32  32
		  16  16  16  16  16  16  16  16  16  16  16  16  16  16  16  16
		  0   0   0   0   0   0   0   0   0   0   0   0   0   0   0   0
		';
		$pgm =  Pgm::parseFromStr($alpha,$width, $height);
		$pgm->output($output_alpha_file);
		
		$magick = new Magick();
		$magick->setting_size($width.'x'.$height)->addCmd('xc:none')
			->addImages($output_red_file)->channel_fx('| gray=>red')
			->addImages($output_green_file)->channel_fx('| gray=>green')
			->addImages($output_blue_file)->channel_fx('| gray=>blue')
			->addImages($output_alpha_file)->channel_fx('| gray=>alpha')
			->output($output_file);
		return $output_file;
	}
	
	/**
	 * 生成一幅测试用的随机图片
	 * @param string $dest_dir
	 * @param int $width
	 * @param int $height
	 * @return string
	 */
	public static function tg_rand($dest_dir, $width, $height){
		$area = $width*$height;
		$red = &IArray::rand_matrix($area, 0, 255);
		$green = &IArray::rand_matrix($area, 0, 255);
		$blue = &IArray::rand_matrix($area, 0, 255);
		$alpha = &IArray::rand_matrix($area, 0, 255);
		
		
		if(!file_exists($dest_dir)){
			@mkdir($dest_dir, 0777, true);
		}
		$output_file = $dest_dir . '/main.png';
		$output_red_file = $dest_dir . '/red.pgm';
		$output_green_file = $dest_dir . '/green.pgm';
		$output_blue_file = $dest_dir . '/blue.pgm';
		$output_alpha_file = $dest_dir . '/alpha.pgm';
		
		$pgm = new Pgm($red,$width, $height);
		$pgm->output($output_red_file);
		$pgm = new Pgm($green,$width, $height);
		$pgm->output($output_green_file);
		$pgm = new Pgm($blue,$width, $height);
		$pgm->output($output_blue_file);
		$pgm = new Pgm($alpha,$width, $height);
		$pgm->output($output_alpha_file);
		
		$magick = new Magick();
		$magick->setting_size($width.'x'.$height)->addCmd('xc:none')
			->addImages($output_red_file)->channel_fx('| gray=>red')
			->addImages($output_green_file)->channel_fx('| gray=>green')
			->addImages($output_blue_file)->channel_fx('| gray=>blue')
			->addImages($output_alpha_file)->channel_fx('| gray=>alpha')
			->output($output_file);
		return $output_file;
	}
	
	
	/**
	 * 打开P5格式的文件时，读取元数据用
	 * @param array $ary
	 * @return mixed
	 */
	private static function binary_read_meta(&$ary){
		$sep = array(' ', "\n", "\r");
		$str = '';
		do{
			$ch = chr(array_shift($ary));
			if($ch == "\r" && $ary[0] == "\n") continue;
			$str .= $ch;
		} while(!in_array($ch, $sep));
		$str = str_replace($sep, '', $str);
		return $str;
	}
	

	/**
	 * 以html单元格替换成像素点，输出一张图片
	 * @param string $output_img   图片的位置
	 * @param string $dest_dir     图片所在的目录
	 * @param string $src1_dir     参考图片1的目录(可选)
	 * @param string $src2_dir     参考图片2的目录(可选)
	 * @return string
	 */
	public static function html($output_img, $dest_dir, $src1_dir = '', $src2_dir = ''){
		$magick = new Magick();
		$output_red_path = $dest_dir . '/red.pgm';
		$output_green_path = $dest_dir . '/green.pgm';
		$output_blue_path = $dest_dir . '/blue.pgm';
		$output_alpha_path = $dest_dir . '/alpha.pgm';
		
		$magick->input($output_img)->channel_fx('red')->output($output_red_path);
		$magick->input($output_img)->channel_fx('green')->output($output_green_path);
		$magick->input($output_img)->channel_fx('blue')->output($output_blue_path);
		$magick->input($output_img)->channel_fx('alpha')->output($output_alpha_path);
		
		$output_red = self::parseFromFile($output_red_path);
		$output_green = self::parseFromFile($output_green_path);
		$output_blue = self::parseFromFile($output_blue_path);
		$output_alpha = self::parseFromFile($output_alpha_path);
		$output_width = $output_red->width;
		$output_height = $output_red->height;
		
		if(!empty($src2_dir)){
			$src2_red_path = $src2_dir . '/red.pgm';
			$src2_green_path = $src2_dir . '/green.pgm';
			$src2_blue_path = $src2_dir . '/blue.pgm';
			$src2_alpha_path = $src2_dir . '/alpha.pgm';
			$src2_red = self::parseFromFile($src2_red_path);
			//Trace::out($src2_red);
			$src2_green = self::parseFromFile($src2_green_path);
			$src2_blue = self::parseFromFile($src2_blue_path);
			$src2_alpha = self::parseFromFile($src2_alpha_path);
			$src2_size = $src2_red->width * $src2_red->height;
		}
		
		if(!empty($src1_dir)){
			$src1_red_path = $src1_dir . '/red.pgm';
			$src1_green_path = $src1_dir . '/green.pgm';
			$src1_blue_path = $src1_dir . '/blue.pgm';
			$src1_alpha_path = $src1_dir . '/alpha.pgm';
			$src1_red = self::parseFromFile($src1_red_path);
			$src1_green = self::parseFromFile($src1_green_path);
			$src1_blue = self::parseFromFile($src1_blue_path);
			$src1_alpha = self::parseFromFile($src1_alpha_path);
			$src1_size = $src1_red->width * $src1_red->height;
		}
		
		$html = array();
		$html[] = '<table width="200%" border="0" cellspacing="0" style="font-size:14px;">';
		for($i = 0; $i<$output_height; $i++){
			$html[] = '<tr>';
			for($j = 0; $j<$output_width; $j++){
				$output_cursor = $i * $output_width + $j;
				$outred = $output_red->data[$output_cursor];
				$outgreen = $output_green->data[$output_cursor];
				$outblue = $output_blue->data[$output_cursor];
				$outalpha = $output_alpha->data[$output_cursor];
				
				if(!empty($src1_dir)){
					$src1red = '-'; $src1green = '-'; $src1blue = '-'; $src1alpha = '-';
					if($output_cursor < $src1_size){
						$src1red = $src1_red->data[$output_cursor];
						$src1green = $src1_green->data[$output_cursor];
						$src1blue = $src1_blue->data[$output_cursor];
						$src1alpha = $src1_alpha->data[$output_cursor];
					}
				}
				if(!empty($src2_dir)){
					$src2red = '-'; $src2green = '-'; $src2blue = '-'; $src2alpha = '-';
					if($output_cursor < $src2_size){
						$src2red = $src2_red->data[$output_cursor];
						$src2green = $src2_green->data[$output_cursor];
						$src2blue = $src2_blue->data[$output_cursor];
						$src2alpha = $src2_alpha->data[$output_cursor];
					}
				}
				$outcolor = Common::rgb(array($outred, $outgreen, $outblue), true);
				$html[] = '<td style="background-color:'.$outcolor.';opacity:'.($outalpha/255.0).';">';
				if(!empty($src1_dir)){
					$html[] = 'src1: r('.$src1red.') g('.$src1green.') b('.$src1blue.') a('.$src1alpha.')<br/>';
				}
				if(!empty($src2_dir)){
					$html[] = 'src2: r('.$src2red.') g('.$src2green.') b('.$src2blue.') a('.$src2alpha.')<br/>';
				}
				$html[] = 'out: r('.$outred.') g('.$outgreen.') b('.$outblue.') a('.$outalpha.')<br/>';
				$html[] = '</td>';
			}
			$html[]= '</tr>';
		}
		$html[] = '</table>';
		return implode('',$html);
	}
}
