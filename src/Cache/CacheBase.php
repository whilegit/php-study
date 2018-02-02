<?php
namespace Whilegit\Cache;

abstract class CacheBase{
    
    /**
     * 写入缓存
     * @param string $key   键值
     * @param mixed $value  值
     * @param int $timeout  缓存过期时间，单位秒，为0时永不过期
     */
    public abstract function setCache($key, $value, $timeout = 3600);
    
    /**
     * 获取缓存
     * @param string $key 键值
     */
    public abstract function getCache($key);
}