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
* $_W['siteroot'] $_W['siteurl'] 请求路径相关 
* $_W['uniacid'] 公众号的uniacid，取自$_GPC['i'], 如无$_GPC['i']取$_GPC['weid']
* $_W['setting'] 存放 core_settings表的内容
* $_W['uniaccount'] 通过$_W['uniacid']获得的公众号帐号信息
* $_W['openid']
* $_W['fans']
* $_W['uid']
* $_W['fans']
* $_W['oauth_account'] = $_W['account']['oauth'] 授权相关

## $_GPC
* $_GPC['c']  $controller
* $_GPC['a']  $action
* $_GPC['do'] $do
* $_GPC['i']  向$_W['uniacid']赋值
* $_GPC['weid'] 如果$_GPC中不存在$_GPC['i'],则以此值给$_W['uniacid']赋值
* $_GPC['state'] 此值通常为we7sid-xxxxxxxx，其中-后面部分构成session_id，如无，则可能使用PHPSESSION
* $_GPC['j'] 公众号相关
* $_GPC['t'] 与表 site_multi 相关
* $_GPC['s'] 与表 site_styles 相关
* $_GPC['eid'] 模块绑定相关
* $_GPC['p'] sz_yi相关

## 常量
* STARTTIME = microtime()
* IA_ROOT 站点根目录
* TIMESTARP = time()
* CLIENT_IP = getip()
* ATTACHMENT_ROOT = IA_ROOT/attachment/
* DEVELOPMENT 定义是否处于开发模式，由 $config['setting']['development'] 是否设为1决定，影响是否输出调试信息。
* 
* 
