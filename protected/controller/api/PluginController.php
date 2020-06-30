<?php
/**
 * PluginController.php
 * Created By Dreamn.
 * Date : 2020/5/8
 * Time : 10:31 下午
 * Description :
 */
namespace app\controller\api;
use app\includes\FileUpload;
use app\lib\blog\Plugin;

class PluginController extends BaseController{
    public function actionGet(){
        $plugin=new Plugin();
        $res=$plugin->getAll();
        $this->api(0,$res,sizeof($res),'暂无数据');
    }
    public function actionInstall(){

        $plugin=new Plugin($this->arg['p']);
        if($plugin->install()){
            $this->api(0,null,0,'安装成功');
        }else{
            $this->api(-1,null,0,$plugin->getErr());
        }

    }
    public function actionUninstall(){
        $plugin=new Plugin($this->arg['p']);
        if($plugin->uninstall()){
            $this->api(0,null,0,'卸载成功');
        }else{
            $this->api(-1,null,0,$plugin->getErr());
        }
    }
    public function actionDisable(){
        $plugin=new Plugin($this->arg['p']);
        if($plugin->disable()){
            $this->api(0,null,0,'禁用成功');
        }else{
            $this->api(-1,null,0,$plugin->getErr());
        }
    }
    public function actionEnable(){
        $plugin=new Plugin($this->arg['p']);
        if($plugin->enable()){
            $this->api(0,null,0,'启用成功');
        }else{
            $this->api(-1,null,0,$plugin->getErr());
        }
    }
    public function actionDel(){
        $plugin=new Plugin($this->arg['p']);
        if($plugin->del()){
            $this->api(0,null,0,'删除成功');
        }else{
            $this->api(-1,null,0,$plugin->getErr());
        }
    }
    public function actionUpload(){
        $upload=new FileUpload('theme');
        $upload->set("allowtype",array("zip"));
        $upload->set("picBed",null);
        $upload->set("maxsize",10485760);
        $res=$upload->upload("file");
        if($res){
            $url=$upload->getFilePath();
            $pluginName=$upload->getOriginName();
            $plugin=new Plugin($pluginName);
            if(!$plugin->upload($url,$pluginName))
                $this->api(-1,null,0,$this->getErr().",插件上传失败！");

            else  $this->api(0,null,0,'');
        }else{
            $this->api(-1,null,0,$upload->getErrorMsg());
        }

    }
    public function actionCache(){
        $plugin=new Plugin($this->arg['p']);
        $plugin->clearCache();
        $this->api(0,null,0,'');
    }

}