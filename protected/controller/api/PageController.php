<?php
namespace app\controller\api;
/*
 * 导航按钮的api
 * */

use app\includes\StringDeal;
use app\includes\FileUpload;
use app\includes\Img2local;
use app\lib\blog\Plugin;
use app\model\Comment;
use app\model\Article;
use app\model\Config;
use app\Model\Upload;

class PageController extends BaseController{
    //前台的导航
    public function actionGet(){
        $article=new Article();
        $condition=null;

        if(arg('key','')!==''){
            $condition[]="title like :key";
            $condition[":key"]="%".arg('key')."%";
        }
        if(arg('type','')!==''){
            $condition["hide"]=arg('type','');
        }
        $result=$article->getByPageAdminPage(arg('page',1),arg('limit',12),$count,$condition);

        if(!$result||empty($result)){
            $this->api(false,null,0,'暂无文章');
        }else{
            $this->api(true,$result,($article->page===null)?sizeof($result):$article->page["total_count"],'');
        }
    }
    public function actionGetById(){
        $article=new Article();
        if(arg('id')!==null){
            $result=$article->getArticleByID(arg('id'),'page');
            if($result){
                $this->api(true,$result,0,'');
            }
        }
        $this->api(false,null,0,'找不到该页面');
    }
    public function actionSet(){
        $allow=['password','alians','info'];

        $noallow=['allowremark','content','date','hide','title'];
        $total=array_merge($allow,$noallow);
        $total[]='picToMe';
        foreach($total as $v){
            if(!isset($this->arg[$v]))
                exit(json_encode(array("state"=>false,'msg'=>"参数错误！".$v)));
        }
        foreach($noallow as $v){
            if($this->arg[$v]==="")
                exit(json_encode(array("state"=>false,'msg'=>$v."不允许为空！")));
        }
        $t=new StringDeal();
        if($this->arg['alians']===''){
            $this->arg['alians']=$t->getAlians($this->arg['title']);
        }
        //先进行文章存储
        $c=new Article();

        $t=array_merge($allow,$noallow);
        foreach ($t as $v){
            $array[$v]=$this->arg[$v];
        }
        $conf=new Config();
        $array['author']=$conf->getData("author");
        $array['type']="page";
        $array['ismarkdown']=0;
        $array['iscopy']=0;
        $array['copyurl']="";
        $r=$c->getByAlians($this->arg['alians']);
        if(isset($this->arg['gid'])){
            $gid=$this->arg['gid'];
            if($r&&$r['gid']!=$this->arg['gid'])exit(json_encode(array("state"=>false,'msg'=>"别名重复！")));
            foreach ($array as $v=>$k){
                $c->setOption($this->arg['gid'],$v,$k);
            }
        }else{
            if($r)exit(json_encode(array("state"=>false,'msg'=>"别名重复！")));
            $gid=$c->add($array);
        }

        //插件不允许执行耗时任务
        Plugin::hook('setArticle',array('param'=>arg(),'gid'=>$gid));
        //为图片转储做准备
        syncRequest(url('sync/main','setpic'),'POST',array('gid'=>$gid,'picToMe'=>$this->arg['picToMe']));
        //进行图片转储
        echo json_encode(array(
            'code' => 0,
            'count' => 0,
            'msg' => '保存成功！如有图片转储则在后台自动进行！',
            'data' => null
        ));

    }
    public function actionSetOption(){
      if(isset($this->arg['id'])&&isset($this->arg['opt'])&&isset($this->arg['val'])){
          $opt=["title","date","comments","views","alians","top","hide","password"];
          $article=new Article();
          if(in_array($this->arg['opt'],$opt)&&($this->arg['val']!==""||$this->arg['opt']==="alians"||$this->arg['opt']==="password")){
              $re=$article->setOption($this->arg['id'],$this->arg['opt'],$this->arg['val']);

              if(!$re)echo json_encode(array("state"=>false,'msg'=>($this->arg['opt']==="alians")?"别名错误！":"未知错误！"));
               else echo json_encode(array("state"=>true));
          }else{
              echo json_encode(array("state"=>false,'msg'=>"未知错误！"));
          }

            }else
                echo json_encode(array("state"=>false,'msg'=>"参数错误！"));
        }//设置单个选项，排序，隐藏
    public function actionDel(){
        if(arg('gid')!==null){
            $article=new Article();
            $article->del(arg('gid'));
            $articleComment=new Comment();
            $articleComment->delByGid(arg('gid'));
            $upload=new Upload('page');
            $upload->delByBindID(arg('gid'));
            $this->api(0,null,0,'');
        }else
            $this->api(-1,null,0,'参数错误！');
    }//删除某个导航


    public function actionUpload(){
        //文章上传
        $upload=new FileUpload('page');
        $res=$upload->upload("file");
        if($res){
            $url=$upload->getFileUrl();
            echo json_encode(array('location' => $url));
        }else{
            echo json_encode(array('location' => null));
        }
    }


}