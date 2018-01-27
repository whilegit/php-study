<?php
namespace Whilegit\Wechat;

class WechatUtils{
    public static function is_weixin(){
        if (empty($_SERVER['HTTP_USER_AGENT']) || 
            (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'Windows Phone') === false)) {
            return false;
        }
        return true;
    }
}