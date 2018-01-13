<?php
require_once __DIR__ . '/inc.php';
header("Content-type: text/html; charset=utf-8");
use Whilegit\Utils\File;
use Whilegit\Utils\Image\Magick;

error_reporting(E_ALL);
ini_set('dispay_errors', 'On');
/*

$rand_dir = getcwd().'/magick/pgm/rand';
$output_file = \Whilegit\Utils\Image\Formats\Pgm::tg_rand($rand_dir, 16, 32);

$tg_dir = getcwd() . '/magick/pgm/tg';
$output_file = \Whilegit\Utils\Image\Formats\Pgm::tg($tg_dir);

$dest_dir = getcwd() . '/magick/pgm/dest';
if(!file_exists($dest_dir)) @mkdir($dest_dir, 0777, true);
$dest_img = $dest_dir . '/main.png';
$magick = new Magick();
$magick->composite()->compose('src_over')->addImages($rand_dir . '/main.png')->addImages($tg_dir . '/main.png')->setting_alpha('set')->output($dest_img);

$html = \Whilegit\Utils\Image\Formats\Pgm::html($dest_img, $dest_dir, $rand_dir, $tg_dir);
//$html = \Whilegit\Utils\Image\Formats\Pgm::html($tg_dir . '/main.png', $tg_dir);

echo "<html>
<head></head>
<style>
td {border:0px;font-size:12px;}
</style>
<body>
{$html}
</body>
</html>";
exit;
*/
/*
$magick->input('E:/borrow-agreement.png')->setting_font('E:/msyh.ttf')->setting_pointsize(20)
	   ->draw_text(360,350,'SN20170907153000123456')
	   ->draw_text(485,410, '毛泽东')->draw_text(300,467,'331081198407237619') //出错人
	   ->draw_text(485,525, '蒋介石')->draw_text(300,580,'331081198407237619') //借款人
	   ->draw_text(420,5740, '蒋介石')->draw_text(280,5775,'2017')->draw_text(395, 5775, '09')->draw_text(476, 5775, '07') //借款人签名
	   ->draw_text(420,5852, '毛泽东')->draw_text(280,5887,'2018')->draw_text(395, 5887, '08')->draw_text(476, 5887, '08') //出借人签名
	   ->draw_text(280,6000,'2019')->draw_text(400, 6000, '09')->draw_text(480, 6000, '09') //365签名
       ->output('E:/r_.jpg');

$pnts = array(array(40,0), array(40,50), array(50,60), array(100,200), array(300,200), array(300,100), array(200,60));
$output_file = 'E:/r_.png';*/
//$magick->input('logo:')/*->channel_fx('red=>alpha')->channel_fx('red=255')*/->channel_fx('blue=0')->channel_fx('green=0')->output($output_file);

/*
$magick->setting_background('blue')->setting_GRAVITY('center')
	   ->stack('<')->addImages('E:/r_-0.jpg','E:/r.jpg')->append('horizontal')->stack('>')
	   ->stack('{')->addImages('E:/r_-2.jpg','E:/r_-3.jpg')->append('horizontal')->stack('}')
       ->append('vertical')->output($output_file);
File::output($output_file);
*/

?>

