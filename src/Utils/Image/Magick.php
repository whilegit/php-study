<?php
namespace Whilegit\Utils\Image;

/**
 * ImageMagick-7.0.7-0-Q8-x64-dll.exe的包装库
 * @author Linzhongren
 */
class Magick{
	
	/**
	 * 测试ImageMagick是否可用，并获取版本信息
	 * @throws \Exception
	 * @return string
	 */
	public static function info(){
		exec ('magick --version', $output, $return_val);
		if($return_val != 0){
			throw new \Exception('ImageMagick未找到，请到https://www.imagemagick.org下载安装。');
		}
		return $output;
	}
	
	
	
}