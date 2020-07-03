<?php
namespace app\controller\index;
use app\Error;
use app\includes\AES;
use app\lib\blog\Plugin;
use app\lib\blog\Theme;
use app\model\Article;
use app\model\Comment;
use app\model\Config;

/**
 * Created by dreamn.
 * Date: 2019-11-02
 * Time: 13:43
 */
class ArticleController extends BaseController
{
    /**
     * 获取信息
     */
    public function actionGet(){
        $article=new Article();
        $res=$article->getByAlians(arg('alian'));

        if(empty($res)||intval($res['hide'])!==0){
            //找不到直接报错
            Error::err('[Warn] The article "'.arg('alian').'" was not found');
        }
        // 无密码是基础 ,只有有密码的时候才需要验证码，防止爆破
        $arr['alian']=arg('alian');
        //先检查是否有密码，并且验证密码
        if($res['password']!==''){
            $password=arg("passwd","");
            //获取密码信息
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
            $arr['tpl']='';
            $arr['script']='';
            //验证码未能通过，获取应该输出的页面信息
            if(!$pass||md5($password)!=md5($res["password"])){
                if(isPost()){
                    echo json_encode(['state'=>false,'msg'=>'验证码或密码错误！']);
                } else {
                    if($arr['captcha_is_open']){
                        $result=Plugin::hook('ArticlePassword',arg(),true,[],$arr['captcha_type']);
                        if(isset($result['tpl'])&&isset($result['script'])){
                            $arr['tpl']=$result['tpl'];
                            $arr['script']=$result['script'];
                        }
                    }
                    $this->display('article_passwd',false,$arr);
                }
                return;
            }

        }

        $arr['content']=$res['content'];//文章内容
        $arr['is_markdown']=$res['ismarkdown'];//是否markdown语法
        $arr['id']=$res['gid'];//文章id
        //交给主题进行处理
        $arr = array_merge($arr, Theme::hook('displayArticle',array('arg'=>arg(),'article'=>$res)));
        //交给插件进行处理，插件未定义则不处理
        $arr_plugin = Plugin::hook('displayArticle',array('arg'=>arg(),'article'=>$res),true,false);
        //插件返回数组表示继续执行，插件返回false不继续执行，插件中可以自行输出
        if(!$arr_plugin)return;
        //合并数据
        $arr=array_merge($arr,$arr_plugin);
        $article->updateReadCount($res['gid']);
        if(isPost()){
            $arr['layout']="";
        }
        if($res['type']==='article'){
            if(intval($arr['is_markdown']))
                $echo = $this->display('article_md',true,$arr);
            else
                $echo =  $this->display('article_html',true,$arr);
        }else
            $echo = $this->display('article_page',true,$arr);
        if(isPost()){
            echo json_encode(['state'=>true,'data'=>$echo]);
        }else echo $echo;

    }
    public function actionComment(){
           $this->layout='';
            $comment=new Comment();
            $result=$comment->getByArticle(arg('id'));

        if(!$result){
            $arr['data']=array();
            $arr['total']=0;

        }
        else {
            $comment_list=array();//列表处理
            //对评论做级联处理
            foreach ($result as $v){
                if($v['pid']==0){
                    $v['blockquote']='';
                    $comment_list[]=$v;
                }else{
                    foreach ($result as $tmp){
                        if($tmp['cid']==$v['pid']){
                            $v['blockquote']=$tmp;
                            $comment_list[]=$v;
                            break;
                        }
                    }
                }
            }


            //$arr['data']=array_reverse($comment_list);
            $arr['data']=$comment_list;
            $arr['total']=sizeof($result);
           
        }
        $arr['id']=arg('id');




        $config=new Config();
        //是否开启验证码
        $arr['captcha_is_open']=intval($config->getData("need_captcha_comment"))&&intval($config->getData("captcha_is_open"));
        //验证码类型
        $arr['captcha_type']=$config->getData("captcha_type");
        $arr['tpl']='';
        $arr['script']='';

        if($arr['captcha_is_open']){
            //如果不是本地插件
            $result=Plugin::hook('CommentCode',arg(),true,[],$arr['captcha_type']);
            if(isset($result['tpl'])&&isset($result['script'])) {
                $arr['tpl'] = $result['tpl'];
                $arr['script'] = $result['script'];
            }
        }
        

        $this->display('comment',false,$arr);
    }
    public function actionTag(){
        $arr=$this->hook('displayTag',arg('tag'));
        $this->display('tag',false,$arr);
    }
    public function actionCategory(){
        $arr=$this->hook('displaySort',arg('sort'));
        $this->display('sort',false,$arr);
    }
    public function actionArchives(){
        $c=new Article();
        $result=$c->getArchives();
        $arr=$this->hook('displayArchives',$result);
        $this->display('archive',false,$arr);
    }
}