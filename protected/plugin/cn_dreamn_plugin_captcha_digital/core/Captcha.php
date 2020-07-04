<?php
namespace app\plugin\cn_dreamn_plugin_captcha_digital\core;


use app\plugin\cn_dreamn_plugin_captcha_digital\core\captcha\CaptchaBuilder;

/**
 * Class Captcha
 * @package includes
 */
class Captcha{

    /**
     * 创建图片验证码
     */
    public function Create(){
        $captcha = new CaptchaBuilder();


        $captcha->initialize([
            'width' => 150,     // 宽度
            'height' => 50,     // 高度
            'line' => false,     // 直线
            'curve' => true,   // 曲线
            'noise' => 1,   // 噪点背景
            'fonts' => []       // 字体
        ]);

        try {
            $re=$captcha->create();
            $_SESSION['code']=$re->getText();
            //logs('[Create]'.$_SESSION['code'],'info','captcha');
            $_SESSION['out_time']=strtotime('+5 Minute');//验证码5分钟有效
            $re->output();
           // $re->save(APP_LOG.'captcha.jpg',3);
        } catch (\Exception $e) {
            //计入小本本
            logs('[captcha]'.$e,'warn','captcha');
        }
    }

    /**
     * 验证码验证
     * @param $code
     * @return bool
     */
    public function Verity($code){
      // logs('[Verity]Session:'.$_SESSION['code'],'info','captcha');
       //logs('[Verity]Code:'.$code,'info','captcha');

        if(isset( $_SESSION['code'])&&isset( $_SESSION['out_time'])&&intval( $_SESSION['out_time'])>intval(time())&& strtolower($_SESSION['code'])===strtolower($code)){
            return true;
        }else{
            $_SESSION['code']=false;
            return false;
        }
    }

}
