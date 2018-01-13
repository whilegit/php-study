<?php
require_once __DIR__ . '/inc.php';
use Whilegit\Utils\image\Gd;

$params = array(
    'size'     => 300,  //二维码尺寸
    'logopath' => dirname(__DIR__).'/static/image/logo.png',   //中间logo的图片位置，为空时不输出logo
    'label'    => '微信扫一扫',   //二维码下方的文字，为空时不输出
);

echo Gd::qrcode('You belong with me', $params, true); 
exit;