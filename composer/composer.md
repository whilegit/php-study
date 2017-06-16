Composer 基础
=============
# 一. 命令
## 1. 安装composer install
*  install命令从当前目录读取composer.json文件,下载依赖包,并把其安装到vendor目录下.
* 如果当前目录存在composer.lock文件,那么它就不会根据composer.json文件去获取依赖。
* 这确保该库的每个使用者都能得到相同的依赖版本.
* 如果composer.lock文件不存在,则下载完依赖包后新建.
## 2. 更新composer update 
* update命令等效于删除composer.lock文件,而后运行composer install命令.
* update命令读取composer.json文件,解决依赖后生成composer.lock.
* 也可这样使用 composer update vendor/package1 vendor/package2 直接写上包名
* 也可使用通配符 composer update vendor/*
