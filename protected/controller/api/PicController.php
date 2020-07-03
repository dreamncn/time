<?php
namespace app\controller\api;


use app\lib\blog\Plugin;
use app\model\Config;

class PicController extends BaseController{
    public function actionGetList(){
        //注册成为图床插件
       $data=Plugin::hook('PicList',[],true,[],null,true);  //插件列表
           $config=new Config();
       $picBed=$config->getData('pic_bed');
       exit(json_encode(array("state"=>true,"data"=>["data"=>$data,'picbed'=>$picBed])));
    }
    public function actionSetList(){
        if(!isset($this->arg['PicBed']))
            exit(json_encode(array("code"=>-1,"msg"=>"参数错误！",$this->arg)));

        $conf=new Config();
        $conf->setData('picbed',$this->arg['PicBed']);
        echo json_encode(array("code"=>0));
    }
}