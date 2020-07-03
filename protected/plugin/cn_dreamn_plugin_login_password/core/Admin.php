<?php
namespace app\plugin\cn_dreamn_plugin_login_password\core;
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
class Admin extends Plugin{
    /**
     * Index constructor.
     */
    public function __construct()
    {
        parent::__construct("cn_dreamn_plugin_login_password");
    }

    public function hookLoginList($data){
        $data=['title'=>'账号密码登录','login_type'=>'cn_dreamn_plugin_login_password'];
        return $data;
    }

    public function hookinclude_login(){
        $tpl=$this->display('set',['username'=>$this->getItem('username')]);
        $return=['title'=>"账号密码设置",'tpl'=>$tpl,'js'=>$this->display('setScript')];
        return $return;
    }

    public function hookDo($data){
        if(!isset($data['username'])||!isset($data['password1'])||!isset($data['password2'])||!isset($data['password3']))
        return [json_encode(['code'=>-1,'msg'=>'修改失败'])];
        if($data['password2']!=$data['password3'])
            return [json_encode(['code'=>-1,'msg'=>'两次输入的密码不一致'])];
        $user=new User();
        $result=$user->changePassword($data['username'],$data['password1'],$data['password2']);
        if($result)return [json_encode(['code'=>0,'msg'=>'修改成功！请重新登录。'])];
        else return [json_encode(['code'=>-1,'msg'=>'修改失败，密码错误。'])];
    }

}//必须继承Plugin.