<?php
namespace app\plugin\cn_dreamn_plugin_login_password\core;
use app\lib\blog\Plugin;
use app\model\Config;

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
        parent::__construct("cn_dreamn_plugin_login_password");
    }
    //前端验证请求
    public function hookDo($data){
        $user=new User();
       switch ($data['type']){
           case 'key':
               return [$user->getKey()];
               break;
           case 'login':
               /*判断验证码*/
               $config=new Config();
               $return['captcha_is_open']=intval($config->getData("need_captcha_login"))&&intval($config->getData("captcha_is_open"));
               if($return['captcha_is_open']) {
                   $return['captcha_type'] = $config->getData("captcha_type");
                   $result = Plugin::hook('Verity', arg(), true, [false], $return['captcha_type']);
                   if(!isset($result[0])||!$result[0])return [json_encode(['state'=>false,'msg'=>'验证码错误'])];
               }
               if(!isset($data['username'])||!isset($data['password']))return [json_encode(['state'=>false,'msg'=>''])];
               if($user->login($data['username'],$data['password'])){
                   return [json_encode(['state'=>true])];
               }
               return [json_encode(['state'=>false,'msg'=>'账号或密码错误！'])];
               break;
       }
    }

    public function hookisLogin($data){
        $user=new User();
        return $user->isLogin(arg('token'));
    }

    public function hookLogin($data){
        $config=new Config();
        $data['captcha_is_open']=intval($config->getData("need_captcha_login"))&&intval($config->getData("captcha_is_open"));
        //验证码类型
        if($data['captcha_is_open']) {
            $data['captcha_type'] = $config->getData("captcha_type");
            $result=Plugin::hook('LoginPassword',arg(),true,[],$data['captcha_type']);
            if(isset($result['tpl'])&&isset($result['script'])){
                $data['tpl']=$result['tpl'];
                $data['script']=$result['script'];
            }

        }

        $return['tpl'] = $this->display('tpl',$data);
        $return['script'] = $this->display('script',$data);
        return $return;
    }
   
}//必须继承PluginController.