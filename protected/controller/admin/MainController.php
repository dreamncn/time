<?php

namespace app\controller\admin;
use app\Error;
use app\includes\AES;
use app\includes\Captcha;
use app\lib\blog\Plugin;
use app\model\Admin;
use app\model\Config;

class MainController extends BaseController
{

    /**
     * 微信登录接口
     */
    public function actionWechat(){
        $admin=new Admin();
        if($admin->getLoginType()==="wechat"){
            echo json_encode(array(
                'err'=>false,
                'data'=>$admin->getWechatQr()
            ));
        }else{
            echo json_encode(array(
                'err'=>true,
                'msg'=>'登录失败'
            ));
        }
    }

    /**
     * 登录页面显示
     */
    public function actionLogin(){
        $admin=new Admin();
        $config=new Config();
        //是否开启验证码
        $arr['captcha_is_open']=intval($config->getData("need_captcha_passwd"))&&intval($config->getData("captcha_is_open"));
        //验证码类型
        $arr['captcha_type']=intval($config->getData("captcha_type"));//1是图片验证码 0是腾讯验证码

        if($admin->getLoginType()==="wechat"){
            $arr=$arr+$this->hook('displayLogin',true);
            $this->display("loginByWechat",false,$arr);
        }else{
            $arr=$arr+$this->hook('displayLogin');
            $this->display("loginByPasswd",false,$arr);
        }
    }


    /**
     * 从数据库部分进行验证
     */
    public function actionLoginPost(){

        $admin=new Admin();
        //验证码部分等会再写
        if($admin->getLoginType()==="wechat"){
            //data是我自己的微信授权平台
            if(arg("sk")!==null&&arg("user")!==null){
                //标识已经获得登录信息了
                if($admin->loginByWechat(arg("sk"),arg("user"))){
                    echo json_encode(array("login"=>true,"msg"=>"登陆成功"));
                }else{
                    echo json_encode(array("login"=>false,"msg"=>"登录失败"));
                }

            }else echo json_encode(array("login"=>false,"msg"=>"不是绑定的微信账号"));
        }else{
            $config=new Config();
            $captcha_is_open=intval($config->getData("need_captcha_passwd"))&&intval($config->getData("captcha_is_open"));
            //验证码类型
            $captcha_type=intval($config->getData("captcha_type"));//1是图片验证码 0是腾讯验证码
            $captcha=new Captcha($captcha_type);
            if($captcha_is_open&&!$captcha->Verity(arg('verity'),arg('randstr')))
                exit(json_encode(array("login"=>false,"msg"=>"账号或密码错误")));
            if(arg("user")!==null&&arg("password")!==null){

                $aes=new AES();
                $passwd=$aes->decrypt(arg("password"),$_SESSION['key']);
                if($admin->loginByPasswd(arg("user"),$passwd)){
                    echo json_encode(array("login"=>true));
                }else echo json_encode(array("login"=>false,"msg"=>"账号或密码错误"));
            }else echo json_encode(array("login"=>false,"msg"=>"账号或密码错误"));
        }

    }//此处是验证回调

    public function actionWechatPost(){
        $admin=new Admin();
        if($admin->getLoginType()==="wechat"){
            if(isset($_SESSION['sk'])){
                //标识已经获得登录信息了
                if($admin->WechatLogin($_SESSION['sk'])){
                    echo json_encode(array("err"=>false,"login"=>true));
                }else echo json_encode(array("err"=>false,"login"=>false,"msg"=>"尚未登录"));

            }else echo json_encode(array("err"=>true,"login"=>false,"msg"=>"不是绑定的微信账号"));
        }else echo json_encode(array("err"=>true,"msg"=>"微信登录已禁用"));
    }//此处是请前端验证

    /**
     * 获取API通讯加密密钥
     */
    public function actionGetKey(){
        $_SESSION['key']=AES::getRandom(16);
        echo json_encode(array('key'=>$_SESSION['key']));
    }

    /**
     * index
     */
    public function actionIndex(){
        $this->data=array(
            array(
                'name'=>'内容管理',
                'icon'=>'iconziliaoneirongguanli_huaban',
                "url"=>"javascript:;",
                'subMenus'=>array(
                    array("name"=>"所有文章","icon"=>'iconguanli','url'=>url('admin/main','page',array('page'=>'newsList'))),
                    array("name"=>"评论管理","icon"=>'iconpinglun','url'=>url('admin/main','page',array('page'=>'commentList'))),
                    array("name"=>"页面管理","icon"=>'iconziliaoneirongguanli_huaban','url'=>url('admin/main','page',array('page'=>'pageList'))),
                    array("name"=>"分类管理","icon"=>'iconwangzhandaohang','url'=>url('admin/main','page',array('page'=>'sort'))),
                    array("name"=>"链接管理","icon"=>'iconchangyongicon-','url'=>url('admin/main','page',array('page'=>'url')))
                )
            ),
            array(
                'name'=>'网站设置',
                'icon'=>'iconwangzhan',
                "url"=>"javascript:;",
                'subMenus'=>array(
                    array("name"=>"博主信息","icon"=>'icongeren','url'=>url('admin/main','page',array('page'=>'adminInfo'))),
                    array("name"=>"侧栏设置","icon"=>'iconcaidan','url'=>url('admin/main','page',array('page'=>'sidebar'))),
                    array("name"=>"主题设置","icon"=>'icon984caidan_zhuti','url'=>url('admin/main','page',array('page'=>'themes'))),
                    array("name"=>"插件设置","icon"=>'iconchajian1','url'=>url('admin/main','page',array('page'=>'plugin'))),
                    array("name"=>"图床设置","icon"=>'icon14','url'=>url('admin/main','page',array('page'=>'pic'))),
                    array("name"=>"附件设置","icon"=>'iconfujian','url'=>url('admin/main','page',array('page'=>'enclosure'))),
                    array("name"=>"导航设置","icon"=>'iconxitongguanli','url'=>url('admin/main','page',array('page'=>'nav'))),
                )
            ),
            array(
                'name'=>'系统管理',
                'icon'=>'iconxitongguanli1',
                "url"=>"javascript:;",
                'subMenus'=>array(
                    array("name"=>"系统设置","icon"=>'iconxitongshezhi','url'=>url('admin/main','page',array('page'=>'system'))),
                    array("name"=>"验证码配置","icon"=>'iconyanzhengma2','url'=>url('admin/main','page',array('page'=>'captcha'))),
                    array("name"=>"邮件配置","icon"=>'iconyoujian','url'=>url('admin/main','page',array('page'=>'email'))),
                    array("name"=>"图床设置","icon"=>'icon14','url'=>url('admin/main','page',array('page'=>'pic'))),
                    array("name"=>"登录设置","icon"=>'iconicon','url'=>url('admin/main','page',array('page'=>'loginSetting'))),
                    array("name"=>"备份恢复","icon"=>'iconcaogao','url'=>url('admin/main','page',array('page'=>''))),
                    array("name"=>"操作日志","icon"=>'iconxitongrizhi','url'=>url('admin/main','page',array('page'=>''))),
                )
            ),
            array(
                'name'=>'使用文档',
                'icon'=>'iconGroup-',
                "url"=>"javascript:;",
                'subMenus'=>array(
                    array("name"=>"使用帮助","icon"=>'iconxinxi','url'=>url('admin/main','page',array('page'=>''))),
                    array("name"=>"更新日志","icon"=>'iconrizhi','url'=>url('admin/main','page',array('page'=>'log'))),
                    array("name"=>"在线升级","icon"=>'iconshengji','url'=>url('admin/main','page',array('page'=>''))),
                )
            ),
        );
    }

    /**
     * 页面展示
     */
    public function actionPage(){
        $t_name=str_replace('.html','',arg("page"));
        $isMatched = preg_match_all('/[a-zA-Z]/', $t_name, $matches);
        if(!$isMatched)Error::err('[Err]No this page "'.$t_name.'"');
        $path=APP_VIEW.'theme'.DS.$this->getTheme()."/admin/include/".$t_name;
        $arr=$this->hook('include_'.$t_name);
        if(is_file($path.'.html')){
            $this->layout='include/layout';
            $this->display("/include/".$t_name,false,$arr);
        }
    }
    public function actionTpl(){
        $t_name=str_replace('.html','',arg("tpl"));
        $isMatched = preg_match_all('/[a-zA-Z]/', $t_name, $matches);
        if(!$isMatched)Error::err('[Err]No this tpl "'.$t_name.'"');
        $path=APP_VIEW.'theme'.DS.$this->getTheme()."/admin/tpl/".$t_name;
        $arr=$this->hook('tpl_'.$t_name);
        if(is_file($path.'.html')){
            $this->display("/tpl/".$t_name,false,$arr);
        }
    }
    public function actionEdit(){
        $this->layout='';
        if(arg('type')==='html'){
            $this->display('edit/htmlEdit');
        }else{
            $this->display('edit/markdownEdit');
        }
    }

    public function actionPlugin(){
        $plugin=new Plugin(arg('p'));
        if($plugin->isEnable()){
            $result=Plugin::hook('Do',arg(),true,null,arg('p'),true);
            if($result!==null){
                foreach ($result as $val){
                    echo $val;
                }
            }
        }
    }


}