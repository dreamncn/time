<?php
namespace app\controller\api;


use app\lib\blog\Plugin;
use app\model\Config;

class CaptchaController extends BaseController{
    private $data=array('captcha_is_open',"need_captcha_passwd","need_captcha_login","need_captcha_comment","captcha_type");
    public function actionGetList(){
        //注册成为验证码插件
       $data=Plugin::hook('CaptchaList',[],true,[],null,true);  //插件列表
       $setData=Plugin::hook('CaptchaSet',[],true,[],null,true);  //插件设置列表
       $config=new Config();
       $captcha_type=$config->getData('captcha_type');
        $arg=[];
        foreach ($this->data as $v){
            $arg[$v]=$config->GetData($v);
        }
       exit(json_encode(array("state"=>true,"data"=>["data"=>$data,'captcha_type'=>$captcha_type,'setData'=>$setData,'other'=>$arg])));
    }
    public function actionSetList(){

        foreach ($this->data as $v) {
            if (!isset($this->arg[$v]))
                exit(json_encode(array("code" => -1, "msg" => "参数错误！")));
        }
        $conf = new Config();
        //更新选项
        foreach ($this->data  as $v)
            $conf->setData($v, $this->arg[$v] );
        echo json_encode(array("code" => 0));
    }
}