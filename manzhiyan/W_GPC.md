WE7 x1.5.1 201707240001 关键全局变量和常量
==========================================
> author: Linzhongren

## $_W
* $_W['config'] 存放IA_ROOT/data/config.php定义的变量
  > $config['setting']['development'] 设定是否处于开发模式。  
  > $config['setting']['cache'] 设定缓存类型，可以选的缓存类型为 mysql, memcache, redis，当前选用mysql  
* $_W['timestamp'] 保存初始化时间戳
* $_W['charset'] 保存$config中定义的编码类型
* $_W['clientip'] 保存远程用户的ip地址
* $_W['ishttps'] 是否是https请求
* $_W['isajax'] 是否是ajax请求
* $_W['ispost'] 是否是post请求
* $_W['sitescheme'] 根据$_W['ishttps']不同，可能为https:// 或 http:// 字符串之一
* $_W['script_name'] 请求uri(无参数)
* $_

## 常量
* STARTTIME = microtime()
* IA_ROOT 站点根目录
* TIMESTARP = time()
* CLIENT_IP = getip()
* ATTACHMENT_ROOT = IA_ROOT/attachment/
* DEVELOPMENT 定义是否处于开发模式，由 $config['setting']['development'] 是否设为1决定，影响是否输出调试信息。
* 
* 
