<?php
/**
 * Name       :Blog.php
 * Author     :dreamn
 * Date       :2020/2/11 17:56
 * Description:博客核心框架
 */
namespace app\lib\blog;
class Blog{
    const name='Time';
    const version='2.0.0';
    const author='Dreamn';
    const site='https://www.dreamn.cn';
    public static function start(){
        Plugin::register();//插件注册
        self::defineConst();//定义常量
    }
    public static function defineConst(){
        define('APP_UPLOAD', APP_DIR . DS . 'protected' . DS.'upload'.DS);//定义文件上传位置
        define('APP_UPLOAD_ARTICLE', APP_DIR . DS . 'i' . DS.'upload'.DS.'article'.DS);//定义文件上传位置
        define('APP_UPLOAD_PLUGIN', APP_DIR . DS . 'protected' .DS.'plugin'.DS);//定义文件上传位置
        define('APP_UPLOAD_THEME', APP_DIR . DS . 'protected' . DS.'theme'.DS);//定义文件上传位置


    }

    

}