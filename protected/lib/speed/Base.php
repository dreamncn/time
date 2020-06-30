<?php

namespace app;
use app\config\Config;

date_default_timezone_set('PRC');
define('FARME_VERSION', '4.2');

define('APP_STORAGE', APP_DIR . DS . 'protected' . DS . 'stroage' . DS);
define('APP_TMP', APP_STORAGE.'view'.DS);//渲染完成的视图文件
define('APP_CACHE', APP_STORAGE.'cache'.DS);//缓存文件
define('APP_ROUTE', APP_STORAGE.'route'.DS);//路由缓存文件
define('APP_LOG', APP_STORAGE.'logs'.DS);//日志文件
define('APP_TRASH', APP_STORAGE.'trash'.DS);//垃圾文件

define('APP_LIB', APP_DIR . DS . 'protected' . DS . 'lib' . DS);
define('APP_VIEW', APP_DIR . DS . 'protected' . DS . 'view' . DS);
define('APP_I', APP_DIR . DS . 'i' . DS);

//载入内置全局函数
require APP_CORE . "Function.php";
// 载入Loader类
require APP_CORE . "Loader.php";
// 注册自动加载
Loader::register();
// 加载配置文件
Config::register();
// 注册错误和异常处理机制
Error::register();
if(isDebug()){
    $GLOBALS['start']=microtime(true);
    logs('----------------------------------------------------------------------------------------------','info');
    logs('Basic loading completed,Framework startup.','info');
}



