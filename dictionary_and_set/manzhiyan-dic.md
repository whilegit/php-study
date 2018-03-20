WE7 x1.5.1 201707240001 数据字典
================================
>author: Linzhongren

## 表core_setting(we7核心设置表)

## 表mc_members (we7用户表)

## 表mc_credits_record
> 记录mc_members表的字段 credit1(积分)和credit2(余额)的变动日志

## 表uni_account 公众号相关

## 表uni_account_user 公众号相关

## 表account 公众号相关

## 表mc_mapping_fans 公众号粉丝相关

## 表uni_settings公众号设置相关

## 表users
> 后台用户表  
* __uid__ _INT(10)_ 主键  
* __groupid__ _INT(10)_ ?  
* __username__ _VARCHAR(30)_ 用户名  
* __password__ _VARCHAR(200)_ 加密密码
  > sha1("{$plain}-{$salt}-{$authkey}")  
  > 其中$authkey 见 /install.php，为安装时生成的随机变量，存放于$_W->config->setting->authkey
* __salt__ _VARCHAR(10)_ 盐值  
* __type__ 
* __status__ _TINYINT(4)_ 状态  0:正常   1:被禁止登录
* __joindate__ ?
* __joinip__
* __lastvisit__ _INT(10)_  最后访问时间
* __lastip__ _VARCHAR(15)_ 最后访问的ip
* __remark__
* __starttime__
* __endtime__ _INT(10)_ 帐号有效期
* __owner_uid__
* __founder_groupid__

## 表users_failed_login
> 后台帐号登录错误次数统计
* __id__
* __ip__
* __username__
* __count__
* __lastupdate__

