Composer 基础
=============
# 命令
## composer install
> install命令从当前目录读取composer.json文件,下载依赖包,并把其安装到vendor目录下.
> 如果当前目录存在composer.lock文件,那么它就不会根据composer.json文件去获取依赖。
> 这确保该库的每个使用者都能得到相同的依赖版本.
