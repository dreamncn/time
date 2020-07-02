<?php
namespace app\controller\api;
use app\includes\Email;
use app\model\Article;
use app\model\Comment;
use app\model\Config;

class CommentController extends BaseController{
    public function actionGetTitles(){
        $c=new Comment();
        $res=$c->getTitles();
        if($res){
            $this->api(true,$res,sizeof($res));
        } else{
            $this->api(false,null,0,'暂无文章');
        }
    }
    public function actionGet(){
        $c=new Comment();
        $condition=[];
        if(arg('name','')!==""){
            $condition['gid']=arg('name','');
        }
        if(arg('author','')!==""){
           
            $condition['admin']=intval(arg('author',''));
        }
        $result=$c->getByPage(arg('page',1),arg('limit',12),$condition,$count);
        $this->api($count!==0,$result,$count,'暂无文章');
    }
    public function actionGetById(){
        $c=new Comment();
        if(arg('id')!==null){
                $result=$c->getByID(arg('id'));
                if($result){
                    echo json_encode(array(
                        "err"=>false,
                        "msg"=>"",
                        "data"=>$result
                    ));
                }else{
                    echo json_encode(array(
                        "err"=>true,
                        "msg"=>"找不到该评论"
                    ));
                }
        }else{
            echo json_encode(array(
                "err"=>true,
                "msg"=>"找不到该评论"
            ));
        }
    }
    public function actionSet(){
        $total=['pid','comment','gid','yname'];
        foreach($total as $v){
            if(!isset($this->arg[$v]))
                exit(json_encode(array("state"=>false,'msg'=>"参数错误！".$v)));
        }
        $c=new Comment();
        foreach ($total as $v){
            $array[$v]=$this->arg[$v];
        }
        $conf=new Config();
        $array['date']=date('Y-m-d H:i:s');
        $array['qq']=$conf->getData("qq");
        $array['yname']= $conf->getData("author");
        $c->add($array);
        if($conf->getData('mail_notice_you')==='on') {
            $mail = new Email();
            $content = new Article();
            $result1 = $content->getArticleByID($this->arg['gid']);
            $result = $c->getByID($this->arg['pid']);
            if ($result1 && $result) {

                $html = $mail->complieNotify(array(
                    'notice1' => '您的留言收到了新的回复', 'notice2' => '<a href="//' . $_SERVER["HTTP_HOST"] . '/' . $result1['alians'] . '">《' . $result1['title'] . '》</a>', 'notice3' => $this->arg['comment']
                ));

                $mail->send($result['qq'] . '@qq.com', '您的留言有新回复！', $html, $array['yname']);

            }
        }

        exit(json_encode(array("state"=>true,'msg'=>"")));

    }
    public function actionSetOption(){
      if(isset($this->arg['id'])&&isset($this->arg['opt'])&&isset($this->arg['val'])){
          $opt=["hide"];
          if(!in_array($this->arg['opt'],$opt))
             exit(json_encode(array("state"=>false,'msg'=>"参数错误！")));

          $c=new Comment();
          $c->setOpt($this->arg['id'],$this->arg['opt'],$this->arg['val']);
          echo json_encode(array("state"=>true,'msg'=>""));
            }else
                echo json_encode(array("state"=>false,'msg'=>"参数错误！"));
        }//设置单个选项，排序，隐藏
    public function actionDel(){
            if(arg('id')!==null){
                $c=new Comment();
                $c->delByCid(arg('id'));
                echo json_encode(array("err"=>false));
        }else
            echo json_encode(array("err"=>true,'msg'=>"参数错误！"));
    }//删除某个导航




}