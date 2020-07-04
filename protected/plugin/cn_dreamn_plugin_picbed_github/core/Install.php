<?php
namespace app\plugin\cn_dreamn_plugin_picbed_github\core;
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
        parent::__construct("cn_dreamn_plugin_picbed_github");
    }

    /**
     * 插件安装是进行的操作，如创建修改数据表等操作
     */
   public function hookInstall(){
       $this->setItem('ownerRepo','');
       $this->setItem('key','');
   }
   /*
    * 插件卸载时的操作，清理残余数据等...一般拿来善后，清理数据表等...
    * */
   public function hookUninstall(){}

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