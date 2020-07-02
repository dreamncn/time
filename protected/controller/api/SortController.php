<?php
/*
 * 导航按钮的api
 * */
namespace app\controller\api;
use app\model\Article;
use app\model\Sort;

class SortController extends BaseController{
    //前台的导航
    public function actionGet(){
        $s=new Sort();
        $result=$s->get();
        if(!$result||empty($result)){
            $this->api(-1,null,0,'分类数据存在问题！尝试重新安装以解决。');
        }else{
            $this->api(0,$result,sizeof($result),'');
        }
    }
    public function actionSet(){
        //一条一条设置
        $arr=['sname'];

        foreach ($arr as $v){
            if(!isset($this->arg[$v]))
                $this->api(-1,$this->arg,0,'参数错误');
        }
        $s=new Sort();
        $s->add($this->arg["sname"]);
        $this->api(0,null,0,'成功');

    }
    public function actionSetOption(){
      if(isset($this->arg['id'])&&isset($this->arg['opt'])&&isset($this->arg['val'])){
          $opt=["sname"];
          $s=new Sort();
          if(in_array($this->arg['opt'],$opt)&&$this->arg['val']!==""){
              $s->setData($this->arg['id'],$this->arg['val']);
              echo json_encode(array("state"=>true));
          }else{
              echo json_encode(array("state"=>false,'msg'=>"未知错误！"));
          }

      }else
          echo json_encode(array("state"=>false,'msg'=>"参数错误！"));
    }//设置单个选项，排序，隐藏
    public function actionDel(){
        if(arg('id')!==null){
            if(intval(arg('id'))!==1){
                $con=new Article();
                $id=$con->getIDBySort(arg('id'));
                if($id){
                    foreach ($id as $v)
                        $con->setOpt($v['gid'],'sid','1');
                }
                $s=new Sort();
                $s->del(arg('id'));
                echo json_encode(array("err"=>false));
            }else  echo json_encode(array("err"=>true,'msg'=>"默认分类禁止删除！"));

        }else
            echo json_encode(array("err"=>true,'msg'=>"参数错误！"));
    }//删除某个导航

}