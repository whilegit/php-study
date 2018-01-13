<?php
namespace Whilegit\Utils;

class File{
	/**
	 * 获取所有的文件上传列表
	 * @return string|array[][] 其中field表示表单域   name终端文件名   tmp_name服务器路径  error错误代号  size文件大小  type文件类型(如image/jpeg)
	 * @desc 转移文件move_uploaded_file时，切勿直接使用$_FILES[xxx]['name']拼接路径，应在套一层basename($_FILES[xxx]['name'])，或完全弃用name字段
	 */
	public static function upload_getfiles(){
		if(empty($_FILES)) return array();
		$files = array();
		foreach($_FILES as $key=>$file){
			if(is_array($file['tmp_name'])){
				for($i = 0; $i<count($file['tmp_name']); $i++){
					$files[] = array('field'=>$key, 'name'=>$file['name'][$i],'tmp_name'=>$file['tmp_name'][$i], 'error'=>$file['error'][$i], 'size'=>$file['size'][$i], 'type'=>$file['type'][$i]);
				}
			}else{
				$files[] = array('field'=>$key, 'name'=>$file['name'], 'tmp_name'=>$file['tmp_name'], 'error'=>$file['error'], 'size'=>$file['size'], 'type'=>$file['type']);
			}
		}
		foreach($files as $file){
			if($file["error"] != UPLOAD_ERR_OK){
				return '上传时文件 \''. $file['name'].'\' 出现错误';
			}
		}
		return $files;
	}
	
	/**
	 * 判定上传文件的类型是否符合
	 * @param  array[][]|array $files 由File::upload_getfiles()返回或直接是$_FILES的子项，必须还type字段
	 * @return string|true
	 */
	public static function upload_checktype(&$files, $type1 = 'image', $type2 = null){
		if(empty($type2)){
			if($types == 'image'){
				$types = array('gif', 'png', 'jpeg', 'jpg');
			}
		}
		if(empty($files) && is_array($files[0])){
			//多个文件
			foreach($files as &$file){
				$type = $file['type'];
				$typeAry = explode('/',$type, 2);
			
				if($typeAry[0] != $type1) return '上传文件'.$file['name'].'，不支持的文件类型'.$typeAry[0];
				if($type2 != null && !in_array($typeAry[1], $type2)) return '上传文件'.$file['name'].'，不支持的文件格式'.$typeAry[1];
				$file['type1'] = $type1;
				$file['type2'] = $typeAry[1];   //type2字段可用作为新文件的后缀名
			}
			unset($file);
		} else {
			//单个文件
			$type = $files['type'];
			$typeAry = explode('/',$type, 2);
				
			if($typeAry[0] != $type1) return '上传文件'.$files['name'].'，不支持的文件类型'.$typeAry[0];
			if($type2 != null && !in_array($typeAry[1], $type2)) return '上传文件'.$files['name'].'，不支持的文件格式'.$typeAry[1];
			$files['type1'] = $type1;
			$files['type2'] = $typeAry[1];   //type2字段可用作为新文件的后缀名
		}
		return true;
	}
	
	
	/**
	 * 判定上传文件大小是否符合要求
	 * @param $files array[][] 由File::upload_getfiles()返回
	 * @param number $maxSize 最大允许值
	 * @param number $minSize  最小允许值
	 * @return string|boolean
	 */
	public static function upload_checksize(&$files, $maxSize, $minSize = 0){
		foreach($files as &$file){
			if($file['size'] > $maxSize || $file['size'] < $minSize)
				return '上传文件'.$file['name'].'，文件不超过 ' .($maxSize / 1024) . 'KB ';
		}
		unset($file);
		return true;
	}

	/**
	 * 递归新建目录
	 * @param string $path
	 * @return boolean
	 */
	public static function mkdirs($path){
		$ret = true;
		if (!is_dir($path)){
			$ret = @mkdir($path, '0777', true);
		}
		return $ret;
	}

	/**
	 * 移动上传文件至常规位置
	 * @param array $postfile  $_FILES的子数组
	 * @param string $dest_file 目标文件路径(目录不要求必须存在)
	 * @return string|boolean
	 */
	public static function upload_move($postfile, $dest_file){
		if(!File::mkdirs(dirname($dest_file))){
			return '目录不可用';
		}
		$result = move_uploaded_file($postfile['tmp_name'], $dest_file);
		if (!$result){
			return '上传Excel 文件失败, 请重新上传!';
		}
		return true;
	}
	
	/**
	 * 获取文件的扩展名
	 * @param string $filename
	 * @return string
	 */
	public static function suffix($filename){
		return pathinfo($filename, PATHINFO_EXTENSION);
	}
	
	public static function output($file, $mime = null){
		ob_clean();
		if($mime == null){
			$mime = self::suffix($file);
		}
		header('content-type: image/' . $mime);
		echo file_get_contents($file);
		exit;
	}
}
