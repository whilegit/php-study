<?php
namespace Whilegit\Cache;

use Whilegit\Utils\Misc;

class MysqlCache extends CacheBase{
    protected $funcGet;
    protected $funcSet;
    protected $funcDel;
    
    /**
     * 构造函数
     * @param callable $funcGet    mysql获取缓存的真正方法，原型 function($key){....;return array('content'=>xxxx, 'timeout'=>3600);}
     * @param callable $funcSet    mysql设置缓存的真正方法，原型 function($key, $value, $timeout){...;}
     * @param callable $funcDel    mysql删除缓存的真正方法，原型 function($key){}
     */
    public function __construct($funcGet, $funcSet, $funcDel){
        $this->funcGet = $funcGet;
        $this->funcSet = $funcSet;
        $this->funcDel = $funcDel;
    }
    
    public function getCache($key){
        $block = call_user_func($this->funcGet, $key);
        if(empty($block)) return null;
        if($block['timeout'] > 0 && time() > $block['timeout']) return null;
        return Misc::iunserializer($block['content']);
    }

    public function setCache($key, $value, $timeout = 3600){
        call_user_func($this->funcSet, $key, serialize($value), $timeout + time());
    }
    
    public function delCache($key){
        call_user_func($this->funcDel, $key);
    }

}
