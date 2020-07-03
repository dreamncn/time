<?php
namespace app\controller\api;
//登录相关的api


use app\lib\blog\Plugin;
use app\model\Config;

class LoginController extends BaseController {
    public function actionGet(){
        $conf=new Config();
        //注册成为登录插件
        $data=Plugin::hook('LoginList',[],true,[],null,true);  //插件列表
        $config=new Config();
        $login_type=$config->getData('login_type');
        exit(json_encode(array("state"=>true,"data"=>["data"=>$data,'login_type'=>$login_type])));
    }
    public function actionSet(){
        //检查是提交还是查询
        if(!isset($this->arg['login_type']))

            exit(json_encode(array("code"=>-1,"msg"=>"参数错误！")));
        $conf=new Config();
        $conf->setData('login_type',$this->arg['login_type']);
        echo json_encode(array("code"=>0));
    }


}