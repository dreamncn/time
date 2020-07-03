<?php
/*
 * 框架初始页面
 * */
namespace app;

define('APP_DIR', __DIR__);
define('DS', DIRECTORY_SEPARATOR);
define('APP_CORE', APP_DIR . DS . 'protected' . DS . 'lib' . DS . 'speed' . DS);

if (!is_file("./install/lock") && is_file("./install/index.php")) {
    require(APP_DIR . '/install/index.php');
    exit;
}else{
    require APP_CORE.'Base.php';
    Speed::Start();
}

