<?php
namespace app\controller\api;
/*
 * 导航按钮的api
 * */

use app\model\Link;

class UrlController extends BaseController{
    //前台的导航
    public function actionGet(){
        $link=new Link();
        $result=$link->get();
        if(!$result||empty($result)){
            $this->api(-1,null,0,'暂无友链');
        }else{
            $this->api(0,$result,sizeof($result),'');
        }
    }
    public function actionSet(){
        //一条一条设置
        $arr=['sitename','siteurl','description','hide'];
        foreach ($arr as $v){
            if(!isset($this->arg[$v]))
                $this->api(-1,null,0,'参数错误');
        }
        $link=new Link();
        if(substr($this->arg["siteurl"],0,4)!=='http'){
            $this->arg["siteurl"]='http://'.$this->arg["siteurl"];
        }
        $url=parse_url($this->arg["siteurl"]);

        if(!isset($url['host']))
            $this->api(-1,null,0,'URL错误');
        if(isset($this->arg["id"])){
            $link->setData($this->arg["id"],$this->arg["sitename"],$url['host'],$this->arg["description"],intval($this->arg["hide"]));
        } else{
            $link->add($this->arg["sitename"],$url['host'],$this->arg["description"],intval($this->arg["hide"]));
        }


        $this->api(0,null,0,'设置成功');

    }
    public function actionSetOption(){
      if(isset($this->arg['id'])&&isset($this->arg['opt'])&&isset($this->arg['val'])){
          $opt=["hide","sitename","siteurl",'description'];
          $link=new Link();
          if(in_array($this->arg['opt'],$opt)&&$this->arg['val']!==""){
              $link->setOpt($this->arg['id'],$this->arg['opt'],$this->arg['val']);
              $this->api(0,null,0,'设置成功');
          }else{
              $this->api(-1,null,0,'未知错误');
          }

      }else
          $this->api(-1,null,0,'参数错误');
    }//设置单个选项，排序，隐藏
    public function actionDel(){
        if(arg('id')!==null){
            $link=new Link();
            $link->del(arg('id'));
            $this->api(0,null,0,'删除成功');

        }else
            $this->api(-1,null,0,'参数错误');
    }//删除某个导航

}