<?php
namespace app\plugin\cn_dreamn_plugin_captcha_digital\core;
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
        parent::__construct("cn_dreamn_plugin_captcha_digital");
    }
    //前端获取验证码请求
    public function hookDo($data){
        $c=new Captcha();
        $c->Create();
        return [];
    }
    //验证码进行验证
    public function hookVerity($data){

        if(!isset($data['code'])) return [false];

        $c=new Captcha();
        return [$c->Verity($data['code'])];
    }
    //文章密码部分的验证码
    public function hookArticlePassword($data){
        $data['tpl']=$this->display('article_tpl');
        $data['script']=$this->display('article_script');
        return $data;
    }
    //评论提交部分的验证码
    public function hookCommentCode(){
        $data['tpl']=$this->display('comment_tpl');
        $data['script']=$this->display('comment_script');
        return $data;
    }
    //登录验证码
    public function hookLoginPassword($data){
        $data['tpl']=$this->display('login_tpl');
        $data['script']=$this->display('login_script');
        return $data;
    }
}//必须继承PluginController.