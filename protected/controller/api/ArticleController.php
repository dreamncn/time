<?php
namespace app\controller\api;
/*
 * 文章页面的api
 * */

use app\includes\FileUpload;
use app\includes\StringDeal;
use app\lib\blog\Plugin;
use app\model\Article;
use app\model\Comment;
use app\model\Sort;
use app\model\Upload;
use app\Sync;

class ArticleController extends BaseController{
    /**
     * 获取文章列表
     */
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
        if(arg('sid','')!==''){
            $condition["sid"]=arg('sid','');
        }
        $result=$article->getByPageAdmin(arg('page',1),arg('limit',12),$count,$condition);

        if(!$result||empty($result)){
            $this->api(false,null,0,'暂无文章');
        }else{
            $this->api(true,$result,($article->page===null)?sizeof($result):$article->page["total_count"],'');
        }
    }

    public function actionSort(){
        $sort=new Sort();
        $this->api(true,$sort->get(),0,'');
    }
    /**
     * 获取标签列表
     */
    public function actionTag(){
        $article=new Article();
        $tag=$article->getTagCount();
        $tagName=array_keys($tag);
        if(sizeof($tagName)!==0)
            $this->api(200,$tagName);
        else
            $this->api(-1,null,0,'没有书签');
    }

    /**
     * 根据ID获得文章信息
     */
    public function actionGetById(){
        $article=new Article();
        if(arg('id')!==null){
                $result=$article->getArticleByID(arg('id'));
                if($result){
                    $this->api(true,$result,0,'');
                }
        }
        $this->api(false,null,0,'找不到该文章');
    }

    /**
     * 设置文章
     */
    public function actionSet(){
        $allow=['copyurl','password','alians','info'];

        $noallow=['allowremark','author','content','date','hide','iscopy','ismarkdown','sid','tag','title','top'];
        $total=array_merge($allow,$noallow);
        $total[]='picToMe';
        foreach($total as $v){
            if(!isset($this->arg[$v]))
                $this->api(false,null,0,"参数错误！".$v);
        }
        foreach($noallow as $v){
            if($this->arg[$v]==="")
                $this->api(false,null,0,$v."不允许为空！");
        }
        if($this->arg['iscopy']==='1' && $this->arg['copyurl']==='')
            $this->api(false,null,0,"非原创文章必须注明原地址！");

        $t=new StringDeal();
        if($this->arg['alians']===''){
            $this->arg['alians']=$t->getAlians($this->arg['title']);
        }
        if($this->arg['info']===''){
            $this->arg['info']=$t->getDescriptionFromContent($this->arg['content'],100);
        }

        //先进行文章存储
        $article=new Article();
        $array=[];
        $t=array_merge($allow,$noallow);
        foreach ($t as $v){
            $array[$v]=$this->arg[$v];
        }
        $r=$article->getByAlians($this->arg['alians']);
        if(isset($this->arg['gid'])){
            $gid=$this->arg['gid'];
            if($r&&$r['gid']!=$this->arg['gid'])$this->api(false,null,0,'别名重复');;
            foreach ($array as $v=>$k){
                $article->setOpt($this->arg['gid'],$v,$k);
            }
        }else{
            if($r)$this->api(false,null,0,'别名重复');
            $gid=$article->add($array);
        }
        //插件不允许执行耗时任务,如需耗时任务请使用后台模式
        Plugin::hook('setArticle',array('param'=>$this->arg,'gid'=>$gid),false,null,null,true,false);
        //为图片转储做准备
        Sync::request(url('sync','main','setpic'),'POST',['gid'=>$gid,'picToMe'=>$this->arg['picToMe']]);
        //进行图片转储
        echo json_encode(array(
            'code' => 0,
            'count' => 0,
            'msg' => '保存成功！如有图片转储则在后台自动进行！',
            'data' => $this->arg['alians']
        ));

    }
    public function actionSetOption(){
      if(isset($this->arg['id'])&&isset($this->arg['opt'])&&isset($this->arg['val'])){
          $opt=["title","date","comments","views","alians","top","hide","password"];
          $article=new Article();

          //setTop
          if(in_array($this->arg['opt'],$opt)&&($this->arg['val']!==""||$this->arg['opt']==="alians"||$this->arg['opt']==="password")){
              if($this->arg['opt']==='top')
                  $re=$article->setTop($this->arg['id'],$this->arg['val']);
              else
                  $re=$article->setOpt($this->arg['id'],$this->arg['opt'],$this->arg['val']);

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
                $upload=new Upload();
                $upload->delByBindID(arg('gid'));
                $this->api(0,null,0,'');
        }else
                $this->api(-1,null,0,'参数错误！');
    }//删除某个导航


    public function actionUpload(){
        //文章上传
        $upload=new FileUpload('article');
        if(arg('type','markdown')==='html'){
            //0为html

            $res=$upload->upload("file");
            if($res){
                $url=$upload->getFileUrl();
                echo json_encode(array('location' => $url));
            }else{
                echo json_encode(array('location' => null));
            }
        }else{
            $res=$upload->upload("editormd-image-file");
            if($res){
                $url=$upload->getFileUrl();

                echo json_encode(array("success"=>1,'message'=>'','url'=>$url));
            }else{
                echo json_encode(array("success"=>0,'message'=>'上传失败了，重新试试吧！','url'=>''));
            }
        }
    }



}