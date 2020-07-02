<?php
namespace app\controller\api;
/*
 * 导航按钮的api
 * */


use app\model\SideBar;

class SidebarController extends BaseController{

    public function actionGet(){
        $nav=new SideBar();
        $result=$nav->getAll();
        if(!$result||empty($result)){
           $this->api(-1,null,0,'导航数据异常，尝试重新安装以解决此问题');
        }else{
            $this->api(0,$result,sizeof($result),'');
        }
    }
    public function actionSet(){
        //一条一条设置
        $arr=['title','sort','type','html'];

        foreach ($arr as $v){
            if(!isset($this->arg[$v]))
                exit(json_encode(array("state"=>false,'msg'=>"参数错误！")));
        }
        $sidebar=new SideBar();
        $sidebar->add($this->arg['title'],$this->arg['html'],$this->arg['type'],$this->arg['sort']);
        $this->api(0,null,0,'');

    }
    public function actionSetOpt(){
        if(isset($this->arg['id'])&&isset($this->arg['opt'])&&isset($this->arg['val'])){
            $opt=["sort"];
            $sidebar=new SideBar();
            if(in_array($this->arg['opt'],$opt)&&$this->arg['val']!==""){
                $sidebar->setOpt($this->arg['id'],$this->arg['opt'],$this->arg['val']);
                $this->api(0,null,0,'');
            }else{
                $this->api(-1,null,0,'参数错误');
            }

        }else
            $this->api(-1,null,0,'参数错误');
    }//设置单个选项，排序，隐藏
    public function actionGetData(){
        $this->api(0,$this->sidebar,sizeof($this->sidebar),'');
    }
    public function actionDel(){
        if(arg('id')!==null){
            $sidebar=new SideBar();
            $sidebar->del(arg('id'));
            $this->api(0,null,0,'');

        }else
           $this->api(-1,null,0,'参数错误');
    }//删除某个导航


    
}