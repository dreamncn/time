<?php
namespace app\model;
use app\includes\AES;
use app\includes\Hash;
use app\includes\Web;

/**
 * Created by dreamn.
 * Date: 2019-09-14
 * Time: 22:25
 */

class Admin{
    private $admin="";
    private $passwd="";
    private $wechat="";
    private $loginType="passwd";
    private $loginTime="";
    private $head="";
    public function __construct()
    {
        $conf = new Config();
        $this->admin=$conf->getData("admin");
        $this->passwd=$conf->getData("password");
        $this->wechat=$conf->getData("wechat_login");
        $this->loginType=$conf->getData("login_style");
        $this->loginTime=$conf->getData("login_time");
        $this->head=$conf->getData("login_head");
    }//读取用户信息到内存
    public function loginByPasswd($admin,$passwd){//用户登录
        //传入的password做过hash加密处理，防止中间截取
        //password来之前就应该做好哈希处理
        $hash=new Hash();
        if($this->admin===$admin&&$hash->sha256($admin.md5($passwd))===$this->passwd){//这样才是成功登录了
            $this->getToken();//登录成功了呗
            return true;
        }else return false;
    }
    private function getToken(){
        $hash=new Hash();
        $loginTime=time();
        $timeout=$loginTime+3600*12;//过期时间是当前时间加12小时
        $token=$hash->sha256($loginTime.$this->passwd.$hash->md5($timeout)).$hash->md5($this->admin.$timeout);
        $conf=new Config();
        $conf->setData("login_time",$loginTime);//更新登录时间
        $_SESSION["login"]=true;//服务器端置登录状态


        //$conf->setData("Password",$hash->sha256($this->admin.md5($passwd).$loginTime));//更新登录哈希

        setcookie("token",$token,$timeout,"/");//设置cookie
    }//认证成功后下发token，token是会话有效的，token内置过期时间记录规则，默认12小时过期
    public function checkLogin($token){
        if(isset($_SESSION["login"])&&$_SESSION["login"]===true){
            $hash=new Hash();
            $token2=$hash->sha256($this->loginTime.$this->passwd.$hash->md5(intval($this->loginTime)+3600*12)).$hash->md5($this->admin.(intval($this->loginTime)+3600*12));
            if($token===$token2)return true;
            else return false;
        }else return false;
    }//检查是否登录
    public function getLoginType(){
        return $this->loginType;
    }
    public function loginByWechat($sk,$user){

        $conf=new Config();

        $json=json_decode($conf->getData("WechatData"),true);

        if(isset($json["sk"])&&isset($json["timeout"])&&$json["sk"]===$sk&&intval($json["timeout"])>=time()){
            $j=json_decode($user);
            $openid=$j->openid;
            if($this->wechat===""){//如果之前没有绑定过，则重新绑定
                $conf = new Config();
                $conf->setData("wechat_login",$openid);//
                $json["islogin"]=true;
                $conf->setData("wechat_data",json_encode($json));
                return true;
            }elseif ($this->wechat===$openid){
                //登录成功
                $json["islogin"]=true;
                $conf->setData("wechat_data",json_encode($json));
                return true;
            }else return false;
        }else return false;


    }
    public function getWechatQr(){
        $web = new Web();
        //$json=json_decode($web->get("https://weauth.isdot.net/sk"));
        $code =AES::getRandom(12);
        //$url = "https://wechat.dreamn.cn/main/Get?scene=".$_SERVER["HTTP_HOST"]."@" . $code;
        $url = "https://wa.isdot.net/qrcode?str=".$_SERVER["HTTP_HOST"]."@" . $code;
        //var_dump($url);

       $res = $web->get($url);
        $json = json_decode($res);
        $conf=new Config();

        $conf->setData("wechat_data",json_encode(array("sk"=>$code,"timeout"=>strtotime("+5 minute"))));
        $_SESSION['sk']=$code;
       // var_dump($res);
       return $json->qrcode;
       //return "";
    }//取得微信登录二维码

    public function WechatLogin($sk){
        $conf = new Config();
        $json=json_decode($conf->getData("wechat_data"));

        if(isset($json->sk)&&$json->sk===$sk&&isset($json->timeout)&&intval($json->timeout)>=time()&&isset($json->islogin)&&$json->islogin){
            $this->getToken();
            return true;
        }else return false;
    }
    public function getUserName(){
        return $this->admin;
    }
    public function getLoginTime(){
        return date("Y-m-d H:i",$this->loginTime);
}
    public function ChangePassword($admin,$old,$new){
        //通过token校验的，认为是该用户本人...
        $hash=new Hash();
        //var_dump($hash->sha256($admin.md5($old)),$this->passwd,$admin,$old,$new);
        if($hash->sha256($this->admin.md5($old))===$this->passwd){
            $conf=new Config();
            $conf->setData("password",$hash->sha256($admin.md5($new)));
            $conf->setData("admin",$admin);
            return true;
        }else return false;
    }

    public function getUserHead(){
        return $this->head;
    }
}//超级管理员类