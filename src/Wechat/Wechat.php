<?php
namespace Whilegit\Wechat;

use Whilegit\Utils\Comm;
use Whilegit\Utils\Misc;

class Wechat{
    
    protected $appId;
    protected $appSecret;
    protected $accessToken = null;
    protected $accessTokenExpire;
    
    /**
      * 缓存层回调。用于获取缓存了的access_token
      * @var callback 原型 function($appId){} 返回 null 或者  array('token'=>'xxxxxxx', 'expire'=>'xxxxxxx');
      */
    protected $getCacheAccessTokenFunc;
    /**
     * 缓存层回调。用于缓存access_token
     * @var 原型 function($appId, $record){}  $record = array('token'=>'xxxxx', 'expire'=>'xxxxx')
     */
    protected $setCacheAccessTokenFunc;
    
    /**
     * 构造函数
     * @param string $appId
     * @param string $appSecret
     * @param string $getCacheAccessTokenFunc 可选，用于从缓存层获取 access_token
     * @param string $setCacheAccessTokenFunc 可选，用于缓存access_token至缓存层
     */
    public function __construct($appId, $appSecret, $getCacheAccessTokenFunc = null, $setCacheAccessTokenFunc = null){
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        
        $this->getCacheAccessTokenFunc = $getCacheAccessTokenFunc;
        $this->setCacheAccessTokenFunc = $setCacheAccessTokenFunc;
    }
    
    /**
     * 获取一个有效的access_token，
     * @param callable $getCacheAccessTokenFunc 可选，用于从缓存层获取 access_token
     * @param callable $setCacheAccessTokenFunc 可选，用于缓存access_token至缓存层
     * @example 若构造函数提供了这两个回调，则调用本函数时不需要提供参数。
     * @example 若构造函数没有提供这两个回调，调用本函数时也没有提供这两个参数，则直接从远程微信服务器拉取access_token(注意access_token混乱的问题)
     * @return string|null
     */
    public function getAccessToken($getCacheAccessTokenFunc = null, $setCacheAccessTokenFunc = null) {
        $curtime = time();
        if(!empty($this->accessToken) && $curtime < $this->accessTokenExpire) return $this->accessToken;
        if(!empty($getCacheAccessTokenFunc)) $this->getCacheAccessTokenFunc = $getCacheAccessTokenFunc;
        if(!empty($setCacheAccessTokenFunc)) $this->setCacheAccessTokenFunc = $setCacheAccessTokenFunc;
        if(!empty($this->getCacheAccessTokenFunc)){
            $cache = call_user_func($this->getCacheAccessTokenFunc, $this->appId);
            if (!empty($cache) && !empty($cache['token']) && $cache['expire'] > $curtime) {
                $this->accessToken = $cache['token'];
                $this->accessTokenExpire = $cache['expire'];
                return $this->accessToken;
            }
        }
        
        $record = $this->getAccessTokenReal();
        if(empty($record) ) return null;
        
        $this->accessToken = $record['token'];
        $this->accessTokenExpire = $record['expire'];
        if(!empty($this->setCacheAccessTokenFunc)){
            call_user_func($this->setCacheAccessTokenFunc, $this->appId, $record);
        }
        return $this->accessToken;
    }
    
    /**
     * 从远程微信服务器获取access_token
     * @return false|array  出错false, 正确返回 array('token'=>'xxxx', 'expire'=>'xxxxxxx')
     */
    protected function getAccessTokenReal(){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->appSecret}";
        $content = Comm::get($url);
        if(Comm::is_error($content)) return null;

        $token = @json_decode($content['content'], true);
        if(empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['expires_in'])) {
            return false;
        }
        $record = array();
        $record['token'] = $token['access_token'];
        $record['expire'] = time() + $token['expires_in'] - 200;
        return $record;
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
        return strtoupper(md5($tmpStr));
    }
    
    public function getAppId(){return $this->appId;}
    public function getAppSecret(){return $this->appSecret;}
}