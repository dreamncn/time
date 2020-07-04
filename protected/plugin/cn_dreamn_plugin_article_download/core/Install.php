<?php
namespace app\plugin\cn_dreamn_plugin_article_download\core;
use app\lib\blog\Plugin;

/**
 * Created by dreamn.
 * Date: 2020-05-08
 * Time: 19:24
 * 关于插件卸载安装部分
 */
class Install extends Plugin{
    /**
     * Install constructor.
     */
    public function __construct()
    {
        parent::__construct("cn_dreamn_plugin_article_download");
    }

    /**
     * 插件安装是进行的操作，如创建修改数据表等操作
     */
   public function hookInstall(){
       // 不建议直接在原有的数据表上做修改，建议直接创建新表处理
         $sql='DROP TABLE IF EXISTS `blog_download`;
CREATE TABLE `blog_download` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `passwd` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `desc` text,
  PRIMARY KEY (`id`),
  KEY `blog_download` (`gid`),
  CONSTRAINT `blog_download` FOREIGN KEY (`gid`) REFERENCES `blog_content` (`gid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
         $this->execute($sql);
   }
   /*
    * 插件卸载时的操作，清理残余数据等...一般拿来善后，清理数据表等...
    * */
   public function hookUninstall(){
       $sql='DROP TABLE IF EXISTS `blog_download`;';
       $this->execute($sql);
   }

    /**
     * 插件启用时的操作
     */
   public function hookEnable(){}
    /**
     * 插件禁用时的操作
     */
   public function hookDisable(){}
    /**
     * 插件被删除时的操作~
     */
   public function hookDel(){}
}//必须继承PluginController.