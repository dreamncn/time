<?php
/**
 * User.php
 * Created By Dreamn.
 * Date : 2020/7/2
 * Time : 9:39 上午
 * Description :
 */
namespace app\plugin\cn_dreamn_plugin_login_password\core;
use app\includes\AES;
use app\includes\Hash;
use app\lib\blog\Plugin;

class User extends Plugin{
    private $admin;
    private $passwd;
    private $logintime;
    public function __construct()
    {
        parent::__construct('cn_dreamn_plugin_login_password');
        $this->admin=$this->getItem('username',null);
        $this->passwd=$this->getItem('password',null);
        $this->logintime=$this->getItem('logintime',null);
    }
    public function login($username,$password){
        if($this->admin===$username){
            $aes=new AES();
            if(!isset($_SESSION['key']))return false;
            $pass=$aes->decrypt($password,$_SESSION['key']);
            $_SESSION['key'] = false;
            $hash=new Hash();
            $hashPass=$hash->sha256($username.md5($pass));
            if($hashPass===$this->passwd){
                $this->getToken();
                return true;
            }

        }
        return false;
    }
    private function getToken(){
        $hash=new Hash();
        $loginTime=time();
        $timeout=$loginTime+3600*12;//过期时间是当前时间加12小时
        $token=$hash->sha256($loginTime.$this->passwd.$hash->md5($timeout)).$hash->md5($this->admin.$timeout);
        $this->setItem('logintime',$loginTime);
        $_SESSION["login"]=true;//服务器端置登录状态
        setcookie("token",$token,$timeout,"/");//设置cookie
    }
    public function isLogin($token){
        if(isset($_SESSION["login"])&&$_SESSION["login"]===true){
            $hash=new Hash();
            $token2=$hash->sha256($this->logintime.$this->passwd.$hash->md5(intval($this->logintime)+3600*12)).$hash->md5($this->admin.(intval($this->logintime)+3600*12));
            if($token===$token2)return true;
        }
        return false;
    }//检查是否登录
    public function changePassword($admin,$old,$new){
        //通过token校验的，认为是该用户本人...
        $hash=new Hash();
        if($hash->sha256($this->admin.md5($old))===$this->passwd){
            $this->setItem("password",$hash->sha256($admin.md5($new)));
            $this->setItem("username",$admin);
            $_SESSION["login"]=false;
            return true;
        }else return false;
    }
    public function getKey(){
        $_SESSION['key']=AES::getRandom(16);
        return json_encode(array('key'=>$_SESSION['key']));
    }
}