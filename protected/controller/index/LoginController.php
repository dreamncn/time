<?php
/**
 * LoginController.php
 * Created By Dreamn.
 * Date : 2020/5/17
 * Time : 6:28 下午
 * Description :
 */
namespace app\controller\index;


use app\lib\blog\Plugin;
use app\model\Config;

class LoginController extends BaseController{
    public $layout='';
    public function actionIndex(){

        $config=new Config();
        $data['login_type']= $config->getData('login_type');
        if(!Plugin::hook('isLogin',arg(),true,false,$data['login_type']))
                $this->jump(url('admin','main','index'));

        
        $data['title']= $config->getData('blog_name').' - 后台登录';

        $result=Plugin::hook('Login',$data,true,[],$data['login_type']);
        if(isset($result['tpl'])&&isset($result['script'])){
            $data['tpl']=$result['tpl'];
            $data['script']=$result['script'];
        }
      $this->display('login_index',false,$data);
    }
    public function actionLogout(){
        setcookie("token","");
        session_destroy();
        $this->jump(url('index','login','index'));
    }
}