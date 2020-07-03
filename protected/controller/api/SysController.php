<?php
namespace app\controller\api;
//系统设置相关的api

use app\model\Config;

class SysController extends BaseController {
    private $data=array('blog_open',"blog_name","copyright","icp","footer" );
    private $data2=array("seo_description","seo_key");
    //系统设置查询,与数据提交
    public function actionGet(){

        $conf=new Config();
        $s = array_merge($this->data,$this->data2);
        $arg=[];
        foreach ($s as $v){
            $arg[$v]=$conf->GetData($v);
        }
        echo json_encode(array("state"=>true,'data'=>$arg));
    }
    public function actionSet(){
        //检查是提交还是查询
        if(arg('type')==1)$s=$this->data2;
        else $s=$this->data;
        foreach ($s as $v){
            if($this->arg[$v]===null)
                exit(json_encode(array("state"=>false,"msg"=>"参数错误！")));
        }
        $conf=new Config();
        //更新选项
        foreach ($s as $v){
            $conf->setData($v,$this->arg[$v]);
        }

        echo json_encode(array("state"=>true));
    }

}