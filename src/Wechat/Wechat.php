<?php
namespace Whilegit\Wechat;

use Whilegit\Utils\Comm;
use Whilegit\Utils\Misc;

class Wechat{
    
    protected $appId;
    protected $appSecret;
    
    public function __construct($appId, $appSecret){
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }
    
    /**
     * 微信授权登陆逻辑
     * @param string  $type    获取用户信息的类型(base表示只获取openid, userinfo获取基本信息)
     * @param string  $state   透明传输字段     
     * @return void|string|Array    正确时返回string或array, 失败时返回null
     * @desc   <br>将本函数 置于程序流中就可以了。
     */
    public function oauth($type = 'base', $state = 'UNNAMED'){
        if(empty($_GET['code']) || $_GET['state'] != $state){
            //第一次访问重定向至微信授权登陆页
            $redirect_url = urlencode(Misc::fullUrl());
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appId}&redirect_uri={$redirect_url}&response_type=code&scope=snsapi_{$type}&state={$state}#wechat_redirect";
            header("Location: $url");
            exit;
        } else{
            //从微信授权登陆页转至本页
            $openid = null;
            $CODE = $_GET['code'];
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appId}&secret={$this->appSecret}&code={$CODE}&grant_type=authorization_code";
            $result = Comm::get($url);
            if(!empty($result['code']) && $result['code'] == '200' && !empty($result['content']) ){
                $res = json_decode($result['content'], true);
                if(!empty($res) && !empty($res['openid']) ){
                    $openid = $res['openid'];
                    if($type == 'userinfo'){
                        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$res['access_token']}&openid={$openid}&lang=zh_CN";
                        $result = Comm::get($url);
                        return json_decode($result['content'], true);
                    }
                }
            }
            return $openid;
        }
    }
    
    /**
     * 统一签名方式(返回字符串签名)
     * @param array $data   待签名数组
     * @param string  $key  密钥
     * @return string 签名串
     */
    public function sign($data, $key){
        ksort($data);
        $tmpStr = '';
        foreach($data as $k=>$v){
            $tmpStr .= "{$k}={$v}&";
        }
        $tmpStr .="key={$key}"; 
        return strtoupper(md5($tmpStr));;
    }
    
    public function getAppId(){return $this->appId;}
    public function getAppSecret(){return $this->appSecret;}
}