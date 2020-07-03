<?php
namespace app\controller\api;


use app\includes\Email;
use app\model\Config;

class MailController extends BaseController{
    private  $data=array('mail_smtp',"mail_port","mail_send","mail_pass","mail","mail_notice_me","mail_notice_you" );
    public function actionGet(){
        $arg=[];
        $conf=new Config();
        foreach ($this->data as $v){
            $arg[$v]=$conf->GetData($v);
        }
        echo json_encode(array("state"=>true,'data'=>$arg));
    }
    public function actionSet(){
        foreach ($this->data as $v){
            if($this->arg[$v]===null) exit(json_encode(array("state"=>false,"msg"=>"参数错误！")));
        }
        $conf=new Config();
        //更新选项
        foreach ($this->data as $v){
            $conf->setData($v,$this->arg[$v]);
        }
        echo json_encode(array("state"=>true));
    }
    public function actionTest(){
        //发送测试邮件
        $email=new Email();
        $this->title="这是一封测试邮件";
        $this->content="测试";
        $content=$this->display("../mail/test",true);
        echo "正在尝试发送邮件！<br>";
        $c=new Config();
        $re=$email->send($c->GetData('mail'),$this->title,$content,"测试站点",2);
        if($re)echo "测试成功！";
        else echo "测试失败！具体错误原因请查看上边的日志记录！";
    }
}