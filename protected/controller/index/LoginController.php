<?php
/**
 * LoginController.php
 * Created By Dreamn.
 * Date : 2020/5/17
 * Time : 6:28 下午
 * Description :
 */
namespace app\controller\index;


class LoginController extends BaseController{
    public $layout='';
    public function actionIndex(){

    }
    public function actionLogout(){
        setcookie("token","");
        session_destroy();
        $this->jump(url('index','login','index'));
    }
}