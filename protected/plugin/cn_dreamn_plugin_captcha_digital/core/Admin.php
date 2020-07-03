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
class Admin extends Plugin{
    /**
     * Index constructor.
     */
    public function __construct()
    {
        parent::__construct("cn_dreamn_plugin_captcha_digital");
    }

    public function hookCaptchaList($data){
       $data[]=['title'=>"图片验证码","name"=>'cn_dreamn_plugin_captcha_digital'];
       return $data;
    }
}//必须继承Plugin.