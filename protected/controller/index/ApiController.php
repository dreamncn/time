<?php
/**
 * Created by dreamn.
 * Date: 2019-11-02
 * Time: 13:43
 */
namespace app\controller\index;

use app\includes\Email;
use app\includes\StringDeal;
use app\includes\Web;
use app\lib\blog\Plugin;
use app\model\Admin;
use app\model\Article;
use app\model\Comment;
use app\model\Config;

class ApiController extends BaseController
{
    public function actionGetArticleList(){
        //请求文章列表的api
        $c=new Article();

        $result=$c->getByPage(null,intval(arg("page",1)),8);
        if(!empty($result)){
            $e["state"]=true;
            $e['page']=$c->page;
            $e['data']=$result;
        }else{
            $e["state"]=false;
        }

        echo json_encode($e);
    }

    public function actionGetInfo(){
        $qq=arg('qq');
        $w=new Web();
        echo str_replace(array('(',")"),array("",''),$w->get("https://fly.pjax.cn/api/nic.php?qq=".$qq));
    }

    public function actionRecevieComment(){
        $config=new Config();
        //是否开启验证码
        $arr['captcha_is_open']=intval($config->getData("need_captcha_passwd"))&&intval($config->getData("captcha_is_open"));
        //验证码类型
        $pass=true;//是否通过验证

        if($arr['captcha_is_open']){
            $arr['captcha_type']=$config->getData("captcha_type");
            $pass=false;
            //获取插件验证码的验证结果
            $result=Plugin::hook('Verity',arg(),true,[false],$arr['captcha_type']);
            
            if(isset($result[0]))$pass=$result[0];
            //验证码输出参数
        }
        //验证码未能通过
        if(!$pass){
            exit(json_encode(array('state'=>false,'msg'=>'验证码错误！','data'=>arg())));
        }

        $arg['gid']=intval(arg('gid'));
        $article=new Article();
        $result=$article->getArticleByID($arg['gid']);
        if(!$result)exit(json_encode(array('state'=>false,'msg'=>'没有该文章')));
        if(!intval($result['allowremark']))exit(json_encode(array('state'=>false,'msg'=>'不允许评论的文章！')));

        //评论接收的api
        $comment=new Comment();
        $n=['qq'=>'/\d{5,12}/','name'=>null,'comment'=>null,'url'=>null];
        foreach ($n as $k=>$v){
            if($v==null)continue;
            if(arg($k)==null)exit(json_encode(array('state'=>false,'msg'=>'请将信息填写完整！')));
            $isMatched = preg_match_all($v, arg($k), $matches);
            if($k=='url'){
                $s=new StringDeal();
                if(!$s->isUrl($v))exit(json_encode(array('state'=>false,'msg'=>'不符合要求的参数')));
            }
            if(!$isMatched||$matches[0][0]!==arg($k))exit(json_encode(array('state'=>false,'msg'=>'不符合要求的参数')));
        }
        $arg['pid']=intval(arg('cid',0));
        if($arg['pid']!==0&&!$comment->isExist($arg['pid']))
            exit(json_encode(array('state'=>false,'msg'=>'回复的评论错误！')));
        $arg['date']=date("Y-m-d H:i:s",time());
        $arg['url']=arg('url');
        $arg['comment']=arg('comment');
        $arg['qq']=arg('qq');
        $arg['yname']=arg('name');
        $arg['hide']=0;
        $arg['browser']=arg('browser');
        $arg['opsystem']=arg('os');
        $admin=new Admin();
        if($admin->checkLogin(arg('token')))$arg['admin']=1;
        else $arg['admin']=0;
        $comment->add($arg);

        if($config->getData('mail_notice_me')){
            $mail=new Email();

            $html=$mail->complieNotify(array(
                'notice1'=>'您的文章有新的留言','notice2'=>'<a href="'. getAddress().'/posts/'.$result['alians'].'">《'.$result['title'].'》</a>','notice3'=>$arg['comment']
            ));

            $mail->send($config->getData('mail'),'您的文章有新的留言！',$html,$arg['yname']);

        }


        echo json_encode(array('state'=>true));
    }
    public function actionSearch(){
        $c=new Article();

        $result=$c->getSearchByPage(arg("s"),intval(arg("page",0)),8);
        if($result){
            $e["state"]=true;
            $e['page']=$c->page;
            $e['data']=$result;
        }else{
            $e["state"]=false;
        }
        echo json_encode($e);
    }

    public function actionCategory(){
        //根据分类查找文章
        $c=new Article();
        $r=$c->getSortByPage(arg("sort"),arg('page',1),8);


        if($r){
            $e["state"]=true;
            $e['page']=$c->page;
            $e['data']=$r;
        }else{
            $e["state"]=false;
        }

        echo json_encode($e);

    }
    public function actionTag(){
        //根据分类查找文章
        $n=arg("tag");
        $c=new Article();
        $r=$c->getTagsByPage($n,arg('page',1),8);
        if(!$r) exit(json_encode(array('state'=>false)));

        if($r){
            $e["state"]=true;
            $e['page']=$c->page;
            $e['data']=$r;
        }else{
            $e["state"]=false;
        }

        echo json_encode($e);

    }

}