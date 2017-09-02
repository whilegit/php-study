<?php
namespace Whilegit\Utils;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use Whilegit\Utils\File;
use Whilegit\Utils\Misc;
class Excel{
	
	/**
	 * 该信息为输出文件的属性
	 * @desc 如有必要，可直接修改本数据
	 * @var array
	 */
	public static $stat = array(
			'Creator'=>'Linzhongren',
			'LastModifiedBy' =>  'Linzhongren',
			'Title' => 'Office 2007 XLSX Document',
			'Subject' => 'Office 2007 XLSX Document',
			'Description' => 'Test document for Office 2007 XLSX, generated using PHP classes.',
			'Keywords' => 'office 2007 openxml php',
			'Category' => 'report file'
	);
	
	//Excel列标
	protected static $COLS =  array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ', 'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ', 'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ', 'EA', 'EB', 'EC', 'ED', 'EE', 'EF', 'EG', 'EH', 'EI', 'EJ', 'EK', 'EL', 'EM', 'EN', 'EO', 'EP', 'EQ', 'ER', 'ES', 'ET', 'EU', 'EV', 'EW', 'EX', 'EY', 'EZ');
	
	//获取坐标
	protected static function column($key, $columnnum = 1){
		return self::$COLS[$key] . $columnnum;
	}
	
	/**
	 * 导出excel至浏览器或导出至文件
	 * @param array $list <br><pre>
	 * [
	 * 	 {'field1'=>val1, 'field2'=>val2,...},
	 *   {...},
	 * ]</pre>
	 * @param array $params<br><pre>
	 * {
	 * 		'columns'=>array(
	 * 				array('title'=>'x列名x', 'width'=>int(列宽度), 'field'=>'list数组对应的子数组的键名'), 
	 * 				...
	 * 			),
	 *      'title' => 'sheet页的名称',
	 *       
	 * }</pre>
	 * @param string path 文件路径 或$path为空时，导出至浏览器；否则导出至文件(不要求必须存在)并返回true或出错信息
	 * @example <pre>
	 * $list = array(
	 *    array('a'=>1, 'b'=>2),
	 *    array('a'=>3, 'b'=>4),
     * );
	 * $params = array(
	 *	 'columns'=>array(
	 *		array('field'=>'a', 'width'=>'24', 'title'=>'项目1'),
	 *		array('field'=>'b', 'width'=>'24', 'title'=>'项目2'),
	 *      ),
	 *   'title' => '项目表'
	 * );
     * Excel::export($list, $params);
     * </pre>
	 */
	public static function export($list, $params = array(), $path = ''){

		$excel = new PHPExcel();
		$excel->getProperties()->setCreator(self::$stat['Creator'])
							   ->setLastModifiedBy(self::$stat['LastModifiedBy'])
		                       ->setTitle(self::$stat['Title'])
		                       ->setSubject(self::$stat['Subject'])
		                       ->setDescription(self::$stat['Description'])
		                       ->setKeywords(self::$stat['Keywords'])
		                       ->setCategory(self::$stat['Category']);
		$sheet = $excel->setActiveSheetIndex(0);
		
		//设置第一行的表头
		$rownum = 1;
		foreach ($params['columns'] as $key => $column )
		{
			$sheet->setCellValue(self::column($key, $rownum), $column['title']);
			if (!empty($column['width']))
			{
				$sheet->getColumnDimension(self::$COLS[$key])->setWidth($column['width']);
			}
		}
		//设置数据部分
		++$rownum;
		$len = count($params['columns']);
		foreach ($list as $row )
		{
			$i = 0;
			while ($i < $len)
			{
				$value = ((isset($row[$params['columns'][$i]['field']]) ? $row[$params['columns'][$i]['field']] : ''));
				$sheet->setCellValue(self::column($i, $rownum), $value);
				++$i;
			}
			++$rownum;
		}
		//设置sheet标题
		$excel->getActiveSheet()->setTitle($params['title']);
		$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
		//输出
		if(empty($path)){
			$filename = urlencode($params['title'] . '-' . date('Y-m-d H:i', time()));
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment;filename="' . $filename . '.xls"');   //询问用户的保存位置
			header('Cache-Control: max-age=0');
			$writer->save('php://output');
			exit();
		} else {
			if(!File::mkdirs(dirname($path))){
				return '上传Excel 文件失败, 请重新上传!';
			}
			$writer->save($path);
			return true;
		}
		
	}

	/**
	 * 导入excel文件，并转化成平面数组
	 * @param array $postfile    此处为$_FILES的项
	 * @param string $save_dir  保存何处，空为不保存,此目录不要求已经存在
	 * @return string|array 返回string表示错误信息
	 * @example <pre>
	 * $postfile = $_FILES['excel'];
	 * $ary = Excel::import($postfile);</pre>
	 */
	public static function import($postfile, $save_dir=''){
		$flag = File::upload_checktype($postfile, 'application', array('vnd.ms-excel','/vnd.openxmlformats-officedocument.spreadsheetml.sheet')); //xls或xlsx
		$ext = strtolower(pathinfo($postfile['name'], PATHINFO_EXTENSION));
		if (!$flag || ($ext != 'xls' && $ext != 'xlsx'))
			return '请上传 xls 或 xlsx 格式的Excel文件!';
		
		//检查是否要保存文件
		$uploadfile = '';
		if(!empty($save_dir)){
			$filename = time(). '_' . Misc::random(6)  . $ext;
			$uploadfile =  "{$save_dir}/{$filename}";
			$flag = File::upload_move($postfile, $uploadfile);
			if(is_string($flag) == true){
				return '上传Excel 文件失败, 请重新上传! 原因:' . $flag ;
			}
		} else{
			$uploadfile = $postfile['tmp_name'];
		}
		
		//解析文件
		$reader = PHPExcel_IOFactory::createReader(($ext == 'xls' ? 'Excel5' : 'Excel2007'));
		$excel = $reader->load($uploadfile);
		$sheet = $excel->getActiveSheet();
		$highestRow = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();
		$highestColumnCount = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$values = array();
		$row = 2;
		while ($row <= $highestRow){
			$rowValue = array();
			$col = 0;
			while ($col < $highestColumnCount){
				$rowValue[] = $sheet->getCellByColumnAndRow($col, $row)->getValue();
				++$col;
			}
			$values[] = $rowValue;
			++$row;
		}
		return $values;
	}
	

}