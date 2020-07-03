<?php
namespace app\controller\api;
/*
 * 主题设置相关的api
 * */

use app\includes\File;
use app\includes\FileUpload;
use app\model\Config;
use app\model\SideBar;

class ThemeController extends BaseController {
    public function actionGetAll(){
        $result=$this->getThemes();
        if(empty($result)){
            $this->api(-1,null,0,'主题数据异常，请尝试重新安装主题以解决此问题');
        }else{
            $this->api(0,$result,sizeof($result),'');
        }
    }//取得所有主题列表
    public function actionEnable(){
        $t=$this->arg["t"];
        if($this->isInstall($t)){
            $c=new Config();
            $c->setData("theme",$t);
            $configData=file_get_contents(APP_DIR.DS.'protected'.DS.'Config.php');
            $configData=str_replace($GLOBALS['error'],'theme/'.$t.'/error/404.html',$configData);
            file_put_contents(APP_DIR.DS.'protected'.DS.'Config.php',$configData);
            $sidebar=new SideBar();
            $sidebar->delTheme();
            $this->api(0,null,0,'');
        }else{
            $this->api(-1,null,0,$this->getErr());
        }
    }//修改主题
    public function actionDel(){
        $t=$this->arg["t"];
        if($this->del($t)){
            $this->api(0,null,0,'');
        }else{
            $this->api(-1,null,0,$this->getErr());
        }
    }//删除主题与卸载
    public function actionUninstall(){
        $t=$this->arg["t"];
        if($this->uninstall($t)){
            $this->api(0,null,0,'');
        }else{
            $this->api(-1,null,0,$this->getErr());
        }
    }
    public function actionInstall(){
        $t=$this->arg["t"];
        if($this->install($t)){
            $this->api(0,null,0,'');
        }else{
            $this->api(-1,null,0,$this->getErr());
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
            $themeName=$upload->getOriginName();
            if(!$this->upload($url,$themeName))
                $this->api(-1,null,0,$this->getErr().",主题上传失败！");

            else  $this->api(0,null,0,'');
        }else{
            $this->api(-1,null,0,$upload->getErrorMsg());
        }

    }//主题上传与安装
    public function actionCache(){
        $arr=scandir(APP_TMP);
        foreach($arr as $val){
            if(is_file(APP_TMP.$val))
                unlink(APP_TMP.$val);
        }
        $this->api(0,null,0,'');
    }
}