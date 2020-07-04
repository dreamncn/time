<?php
namespace app\plugin\cn_dreamn_plugin_article_download\core;
use app\lib\blog\Plugin;

/**
 * Created by dreamn.
 * Date: 2019-09-08
 * Time: 19:24
 */
/*
 * 绝对绝对不允许自行输出任何信息，所有信息的输出请直接调用内置函数
 * */
/*默认类名，不允许修改*/
class Index extends Plugin{
    /**
     * Index constructor.
     */
    public function __construct()
    {
        parent::__construct("cn_dreamn_plugin_article_download");
    }

    public function hookdisplayArticle($data){
        $gid=$data['article']['gid'];
        $download=new Download();
        $res=$download->getByGid($gid);
       // $res['url']=url('index','main','jump',['url'=>base64_encode($res['url'])]);
        if($res){
            return ['tpl'=>$this->display('index',$res)];
        } else return ['tpl'=>''];

    }



}//必须继承PluginController.