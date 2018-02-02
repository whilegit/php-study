<?php
namespace Whilegit\Cache;

class FileCache extends CacheBase{
    protected $rootCache;
    protected $dirLevel;
    
    /**
     * 构造函数
     * @param string $rootCache    指定文件缓存的根目录
     * @param int $dirlevel        缓存的目录层数
     */
    public function __construct($rootCache, $dirlevel = 3){
        $this->rootCache = str_replace('\\', '/', $rootCache);
        $this->dirLevel = $dirlevel;
    }
    
    /**
     * 写入缓存
     * {@inheritDoc}
     * @see \Whilegit\Cache\CacheBase::setCache()
     */
    public function setCache($key, $value, $timeout = 3600){
        $timeout = $timeout > 0 ? $timeout + time() : 0;
        $content = "<?php exit;$timeout;?>" . serialize($value);
        $key = strtoupper(md5($key));
        $p = '';
        for($i = 0; $i<$this->dirLevel; $i++) $p .= '/'.$key{$i};

        $path = "{$this->rootCache}{$p}";
        if(!file_exists($path)) @mkdir($path, 0777, true);
        file_put_contents($path . "/{$key}.php", $content);
    }
    
    /**
     * 读取缓存
     * {@inheritDoc}
     * @see \Whilegit\Cache\CacheBase::getCache()
     */
    public function getCache($key){
        $key = strtoupper(md5($key));
        $p = '';
        for($i = 0; $i<$this->dirLevel; $i++) $p .= '/'.$key{$i};
        $file = "{$this->rootCache}{$p}/{$key}.php";
        
        if(!file_exists($file)) return null;
        $content = file_get_contents($file);
        if($content == false) return null;
        
        $pos = strpos($content, '>');
        if($pos === false || $pos < strlen('<?php exit;;?>')) return null;
        $pre = substr($content, 0, $pos+1);
        $ary = explode(';', $pre, 3);
        if($ary[1] != 0 && $ary[1] < time()){
            return null;
        }
        
        $main = substr($content, $pos+1);
        return \Whilegit\Utils\Misc::iunserializer($main);
    }
}