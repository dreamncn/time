<?php
define('APP_NAME','Time 博客');
define('APP_VER','2.0.0');
define('APP_UPDATE','2020.07.03');
define('APP_AUTHOR','Dreamn');
define('APP_TITLE','Time 博客安装向导');
define('APP_URL','https://www.dreamn.cn');

/**
 * sql数据库安装请替换data/mysql.sql
 * Config替换请参考data/Config.php 记得修改数据库部分
 */
$GLOBALS['check']=array(
  'env'=>array(
      'os'=>array('min'=>'不限','good'=>'Linux'),//运行的程序的系统
      'php'=>array('min'=>'7.0','good'=>'7.3'),//php支持版本
      'upload'=>array('min'=>'2M','good'=>'2M'),//上传附件大小
      'disk'=>array('min'=>'12M','good'=>'12M'),//磁盘大小
  ),
    'var'=>array(
        'dirfile'=>array(
            array('type' => 'dir', 'path' => 'protected/stroage'),
            array('type' => 'dir', 'path' => 'install'),
        ),//检查某个目录或者文件是否可写
        'func'=>array(
            array('name' => 'json_decode'),
            array('name' => 'json_encode'),
            array('name' => 'urldecode'),
            array('name' => 'urlencode'),
            array('name' => 'openssl_encrypt'),
            array('name' => 'openssl_decrypt'),
            array('name' => 'file_get_contents'),
            array('name' => 'mb_convert_encoding'),
            array('name' => 'curl_init'),
        ),//检查某个函数是否可用
        'ext'=>array(
            array('name' => 'curl'),
            array('name' => 'openssl'),
            array('name' => 'gd'),
            array('name' => 'json'),
            array('name' => 'session'),
            array('name' => 'PDO'),
            array('name' => 'iconv'),
            array('name' => 'hash'),
            array('name' => 'mysqli')
        )//检查是否加载了对应的php拓展
    )
);
const LICENESE=<<<EOF
<h1 align="center">
                Time —— 基于MVC的博客框架
            </h1>
            <div>
                <hr/>
            </div>
            <p>
                <span style="font-size:16px;">Time是基于ClearPHP + mysql 实现的一个博客框架。</span>
            </p>
           

            <hr/>
            <p>
                <br/>
            </p>
            <p align="center">
                <span style="font-size:16px;">Time遵循 MIT License 开源协议发布，并提供免费使用，请勿用于非法用途。</span>
            </p>
            <p align="center">
                <span style="font-size:16px;">版权所有Copyright &copy; 2020 by Dreamn (</span><a href="https://dreamn.cn"><span
                    style="font-size:16px;">https://dreamn.cn</span></a><span style="font-size:16px;">)</span>
            </p>
            <p align="center">
                <span style="font-size:16px;">All rights reserved</span>
            </p>
            <p>
                <br/>
            </p>
EOF;
$GLOBALS['install']=array(//安装方式写在这里~，
    array('name'=>'完全安装','func'=>''),
   /* array('name'=>'最小安装','func'=>'min'),//进行处理的函数写在func，然后对应的写函数就行*/
);
