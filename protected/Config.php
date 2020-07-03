<?php

namespace app\config;
class Config
{
    static public function register()
    {
        $conf = Config::config();
        if (!in_array($_SERVER["HTTP_HOST"], $conf['host'])) {
            exit('域名绑定错误！');
        }
        $GLOBALS = $conf + Config::route();

    }

    static public function config()
    {
        return array( // 调试配置
            'host' => array('abc.com'),//localhost改成自己的域名
            'debug' => 1,//为0不输出调试错误信息
            'mysql' => array(//数据库信息
                'MYSQL_HOST' => '127.0.0.1',
                'MYSQL_PORT' => '3306',
                'MYSQL_USER' => 'abc_com',
                'MYSQL_DB' => 'abc_com',
                'MYSQL_PASS' => 'xDw3HpKze58Pta3i',
                'MYSQL_CHARSET' => 'utf8',
            ),
            "error" => 'theme/cn_dreamn_theme_even/error/404.html'//非调试状态出错显示的信息
        );
    }

    static public function route()
    {
        return array(
            'cache' =>false,//是否进行URL缓存
            'rewrite' => array(
                'posts/<alian>'=>'index/article/get',//文章别名
                'jump/<url>'=>'index/main/jump',//链接跳转
                'tag/<tag>'=>'index/article/tag',//标签
                'category/<sort>'=>'index/article/category',//分类
                'search/<search>'=>'index/main/search',//搜索
                'admin/page/<page>'=>'admin/main/page',//include



                '<m>/<c>/<a>' => '<m>/<c>/<a>',
            
                '/' => 'index/main/index',
                'admin' => 'admin/main/index',

            ),
        );
    }
}