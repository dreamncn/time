<?php
namespace app\controller\api;
/*
 * 导航按钮的api
 * */

use app\model\Article;
use app\model\Native;
use app\model\Sort;

class NavController extends BaseController{

    public function actionGet(){
        $nav=new Native();
        $result=$nav->getAll();
        if(!$result||empty($result)){
           $this->api(-1,null,0,'导航数据异常，尝试重新安装以解决此问题');
        }else{
            $this->api(0,$result,sizeof($result),'');
        }
    }
    public function actionSet(){
        //一条一条设置
        $arr=['nname','pid','url','lastest','hide','stype'];

        foreach ($arr as $v){
            if(!isset($this->arg[$v]))
                $this->api(-1,null,0,'参数错误');
        }
        $nav=new Native();
        $tick=$nav->getNoLastest();
        if(in_array($this->arg['lastest'],$tick))
            $this->api(-1,null,0,'只支持二级导航');
        $arr=array(
            "nname"=>$this->arg["nname"],
            "url"=>$this->arg["url"],
            "hide"=>$this->arg["hide"],
            "stype"=>$this->arg["stype"],
            "pid"=>$this->arg["pid"],
            'nexts'=>'',
            "lastest"=>$this->arg["lastest"]);
        $id=$nav->add($arr);
        /*
         * 更新
         * */

        if(intval($this->arg['lastest'])===0)
            exit(json_encode(array("state"=>true)));
        $n=$nav->gerById($this->arg["lastest"]);
        if(!$n){
            $nav->setOption($id,"lastest",0);
            $this->api(-1,null,0,'好像有点问题');
        }
        $array=explode(',', $n['nexts']);

        if(!in_array($id,$array))
            $array[]=$id;
        $nav->setOption($this->arg["lastest"],"nexts",implode(",",$array));
        $this->api(0,null,0,'');

    }
    public function actionSetOption(){
      if(isset($this->arg['id'])&&isset($this->arg['opt'])&&isset($this->arg['val'])){
          $opt=["hide","pid","nname"];
          $nav=new Native();
          if(in_array($this->arg['opt'],$opt)&&$this->arg['val']!==""){
              $nav->setOption($this->arg['id'],$this->arg['opt'],$this->arg['val']);
              $this->api(0,null,0,'');
          }else{
              $this->api(-1,null,0,'参数错误');
          }

      }else
          $this->api(-1,null,0,'参数错误');
    }//设置单个选项，排序，隐藏
    public function actionDel(){
        if(arg('id')!==null){
            $nav=new Native();
            $d=$nav->gerById(arg('id'));
            if(!$d) $this->api(-1,null,0,'参数错误');
            if(intval($d['stype'])!==0){
                $nav->del(arg('id'));
                //删除所有下级依赖
                $array=explode(',', $d['nexts']);
                foreach ($array as $v)
                    $nav->setOption($v,"lastest",0);
                //删除所有上级依赖
                if(intval($d['lastest'])!==0){
                    $dd=$nav->gerById(arg($d['lastest']));
                    if(!$dd){
                        $array=explode(',', $dd['nexts']);
                        $array=array_diff($array,array(arg('id')));
                        $nav->setOption($d["lastest"],"nexts",substr(implode(",",$array),1));
                    }

                }


                echo $this->api(0,null,0,'');
            }else  $this->api(-1,null,0,'系统导航禁止删除');

        }else
            $this->api(-1,null,0,'参数错误');
    }//删除某个导航

    public function actionGetData(){
        //先收集分类数据

        $s=new Sort();
        $arr['type']=$s->get();
        $page=new Article();
        $arr['page']=$page->getPages();
        $nav=new Native();
        $arr['nav']=$nav->getNoLastest();
        $this->api(0,$arr,sizeof($arr),'success');
       

    }

    
}