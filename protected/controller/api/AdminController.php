<?php
namespace app\controller\api;
use app\model\Config;

class AdminController extends BaseController{
    private $data=array('author',"qq","github","info" );
    public function actionGet(){
        $conf=new Config();
        $arg=[];
        foreach ($this->data as $v){
            $arg[$v]=$conf->getData($v);
        }
        $this->api(0,$arg,0,'');
    }
    public function actionSet(){
        foreach ($this->data as $v){
            if($this->arg[$v]===null)

                $this->api(-1,null,0,'参数错误');
        }

        $conf=new Config();
        foreach ($this->data as $v){
            $conf->setData($v,$this->arg[$v]);
        }
        $this->api(0,null,0,'保存成功');
    }
    public function actionUpload(){
        
       if(isset($_FILES['file'])){
           file_put_contents(APP_I.'upload/sys/head.jpg',file_get_contents($_FILES['file']['tmp_name']));
           $this->api(0,null,0,'成功');
       }
        $this->api(-1,null,0,'上传失败');
    }
}