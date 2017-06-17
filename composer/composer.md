Composer 基础(PHP的作曲家)
=============
> Composer是php社区的包管理工具,[packagist.org](https://getcomposer.org), [国内镜像](http://www.phpcomposer.com), [源](https://packagist.org)
# 一. 命令
## 1. 版本显示 composer -V或 composer --version
> Window平台直接安装.exe文件,Linux平台直接yum search composer,安装完后用本命令测试是否成功.

## 2. 安装composer install
*  install命令从当前目录读取composer.json文件,下载依赖包,并把其安装到vendor目录下
* 如果当前目录存在composer.lock文件,那么它就不会根据composer.json文件去获取依赖。
* 这确保该库的每个使用者都能得到相同的依赖版本
* 如果composer.lock文件不存在,则下载完依赖包后新建
### 选项
* --prefer-source 安装依赖的版本库source(debug比较有用)
* --prefer-dist   安装依赖的发行版(稳定版的依赖默认下载dist版本)
* --dry-run       试安装,不会真实安装依赖,仅模拟整个过程     
* --ignore-platform-reqs 忽略平台依赖(php,hhvm,lib-*,ext-*的要求,并强制安装)


## 3. 更新composer update 
* update命令等效于删除composer.lock文件,而后运行composer install命令
* update命令读取composer.json文件,解决依赖后重新生成composer.lock
* 也可这样使用 composer update vendor/package1 vendor/package2 直接写上包名
* 也可使用通配符 composer update vendor/*

## 4. 搜索composer search
* 搜索packagist.org上的包,可以简单地输入搜索条件,如 composer search monolog
* 加上参数--only-name或-N 时,搜索时须完全匹配.

## 5. 增加依赖composer require
* 增加新的依赖到composer.json中
* 如: composer require monolog/monolog
* 也可以直接指定依赖包 composer require monolog/monolog:2.*

# 二. 配置文件 composer.json和composer.lock
> composer.json文件

    {
        "name": "lzr/hello-world",
        "repositories": [
            {
                "type":"vcs",
                "url": "https://github.com/NAME/PACKAGE"
            }
        ],
        "require":{
            "monolog/monolog": "1.2.*"
        }
    }

## 1. 给当前project命名
> Every project is a package. 每个工程都是库.在composer.json里,加上

    "name":"lzr/hello-world"
    "version":"1.0.0"

* 即可使当前工程变成一个Library, 上传后即可被其他开发者依赖.
* 如果没有手动指定version,且工程使用了版本控制工具(VCS),composer将自动推断版本号

## 2. 指定repositories源
* composer支持从git/svn等版本库中安装依赖(该版本库中必须包含composer.json文件).
* 在composer.json文件的repositories节点中增加源
>
    {
       "type": "vcs",
       "url": "https://github.com/Seldaek/monolog"
    }

## 3. 发布到packagist.org上
* 在packagist.org上填到vcs版本库的地址,提交即可,然后packagist会爬取版本库的内容.
* 发布到packagist.org上的依赖无须指定repositories源

# 三. 自动加载
> composer安装完成后,在vendor目录下自动生成一个autoload.php文件,只要require该文件后
> 即可使用composer安装的所有库.
> 代码:　

    require __DIR__ . '/vendor/autoload.php';
    $log = new Monolog\Logger('name'); 


















