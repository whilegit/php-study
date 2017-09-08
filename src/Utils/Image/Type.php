<?php
namespace Whilegit\Utils\Image;

class Type{
	
	/**
	 * 检测是否是PNG文件
	 * @param string $path
	 * @desc 文件署名域  89 50 4e 47 0d 0a 1a 0a
	 */
	public static function is_png($path){
		$fd = @fopen($path, 'rb');
		if(empty($fd)){
			return false;
		}
		$head = fread($fd, 8);
		fclose($fd);
		return strncmp("\x89\x50\x4e\x47\x0d\x0a\x1a\x0a", $head, 8) == 0;
	}
	
}