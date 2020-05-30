# junswoole

> 运行环境要求PHP7.1+ linux redis5.0+。

##php扩展添加

> swoole扩展添加

##项目描述
通过php执行junsw文件进行开启对应的swoole服务（例如：websocket，tcp，udp...）  
通过Console文件控制开启对应的swoole服务  
swoole多种服务的扩展在swoole文件夹内生成对应服务的文件进行编写代码

> 配置文件统一放在helper里面

##项目部署注意
1. 项目全局部署后，需要手动进行一下操作
~~~
composer install
composer dump-autoload
composer update
~~~
若是部分部署则不需要上面的操作

