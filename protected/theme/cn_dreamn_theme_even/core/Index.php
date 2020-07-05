<?php
namespace app\theme\cn_dreamn_theme_even\core;
use app\includes\Web;
use app\lib\blog\Plugin;
use app\model\Article;
use app\model\Comment;
use app\model\Config;
use app\model\Native;
use app\model\SideBar;
use app\model\Sort;

/**
 * Created by dreamn.
 * Date: 2019-09-08
 * Time: 19:24
 * Description : 前端部分
 */
/*默认类名，不允许修改*/
class Index extends Plugin{
    /**
     * Index constructor.
     */
    public function __construct()
    {
        //声明主题名
        parent::__construct("cn_dreamn_theme_even");
    }

    /**
     * 主题渲染时附加参数
     */
    public function viewDisplay(){
        $config=new Config();
        $arr['site_url']=getAddress();
        $arr['blog_name']=$config->getData('blog_name');
        $arr['article_title']=$config->getData('blog_name').' - '.$config->getData('info');
        $arr['description']=$config->getData('seo_description');
        $arr['key']=$config->getData('seo_key');
        $arr['copyright']=str_replace(array('{year}'),array(date('Y')),$config->getData('copyright'));
        $arr['icp']=$config->getData('icp');
        $arr['footer']=base64_decode($config->getData('footer'));
        $arr['qq']=$config->getData('qq');
        $arr['mail']=$config->getData('mail');
        $arr['github']=$config->getData('github');
        $arr['head']=$config->getData('head');
        $arr['author']=$config->getData('author');
        $nav=new Native();
        $arr['nav']=$nav->getNoHide();
        return $arr;
    }

    /**
     * 首页渲染的参数
     */
    public function displayIndex(){
        $sidebar=new SideBar();
        $arr['sidebar']=$sidebar->getSideBar();
        return $arr;
    }
    /**
     * TAG渲染的参数
     */
    public function displayTag($tag){
        //根据标签id查找文章
        $arr['title']='标签：'.$tag;
        $arr['tag']=$tag;
        $sidebar=new SideBar();
        $arr['sidebar']=$sidebar->getSideBar();
        return $arr;
    }
    /**
     * Sort渲染的参数
     */
    public function displaySort($sort){
        $arr['title']='分类：'.$sort;
        $arr['sort']=$sort;
        $sidebar=new SideBar();
        $arr['sidebar']=$sidebar->getSideBar();
        return $arr;
    }

    public function displayArchives($data){
        $config=new Config();
        $arr['article_title']='文章归档 - '.$config->getData('blog_name');
        $arr['title']='文章归档';
        $arr['data']=$data;
        $arr['count']=sizeof($data);
        $sidebar=new SideBar();
        $arr['sidebar']=$sidebar->getSideBar();
        return $arr;
    }
    public function displayLink($data){
        $config=new Config();
        $arr['article_title']='友情链接 - '.$config->getData('blog_name');
        $arr['title']='友情链接';
        $arr['data']=$data;
        $arr['count']=sizeof($data);
        return $arr;
    }

    public function displaySearch($name){
        $config=new Config();
        $arr['article_title']='搜索结果 - '.$name.' - '.$config->getData('blog_name');
        $arr['title']='关键字 '.$name.' 的搜索结果';
        $arr['search']=$name;
        $sidebar=new SideBar();
        $arr['sidebar']=$sidebar->getSideBar();
        return $arr;
    }
    /**
     * 文章输出的数组
     * @param $data
     * @return mixed
     */
    public function displayArticle($data){
       $res=$data['article'];
        $arr['title']=$res["title"];//文章标题
        $arr['alian']=arg('alian');//文章别名
        $arr['author']=$res['author'];//文章作者
        $arr['allow_remark']=$res['allowremark'];//是否允许评论
        $arr['is_copy']=$res['iscopy'];//是否非原创
        $arr['copy_url']=$res['copyurl'];//原文地址
        $arr['date']=$res['date'];//发布时间
        $arr['comment']=$res['allowremark'];//是否允许评论
        $arr['views']=$res['views'];//阅读数量
        $arr['article_title']=  $res["title"];
        if($res['type']==='article'){
            $s=new Sort();
            $arr['sort']=$s->getNameByID($res["sid"])['sname'];
            if($res["tag"]!=='')
                $arr['tag']=explode(',',$res["tag"]);
            else
                $arr['tag']=array();
        }
        return $arr;
    }

    /**
     * 普通侧栏hook点
     * @param $v array
     * @return array
     */
    public function sidebar($v){
        $arr['bar_title']=$v['title'];
        $arr['bar_content']=$v['html'];
        return $arr;
    }

    /**
     * 主题自带的侧栏hook点
     * @param $v array 数据库中获得的数据，只有后台启用该侧栏，才会调用该hook函数
     * @return mixed
     */
    public function sidebar_tags($v){
        $article=new Article();
        $arr['tag']=$article->getTagCount();
        $arr['bar_title']=$v['title'];
        return $arr;
    }

    /**
     * @param $v
     * @return mixed
     */
    public function sidebar_hitokoto($v){
         $web=new Web();
        $hitokoto=json_decode($web->get('https://v1.hitokoto.cn/'));
        $arr['hitokoto']=$hitokoto->hitokoto.'<br>—— '.$hitokoto->from;
        $arr['bar_title']=$v['title'];
        return $arr;
    }

    /**
     * @param $v
     * @return mixed
     */
    public function sidebar_info($v){
        $arr['url']=getAddress();
        $conf=new Config();
        $arr['info']=$conf->getData('info');
        $arr['name']=$conf->getData('author');
        $article=new Article();

        $arr['article']=number_format($article->getCount());
        $comment=new Comment();
        $arr['comment']=number_format($comment->getCount());


        $arr['day']=intval((time()-intval($conf->getData('start_time')))/(60*60*24));
        return $arr;
    }
    /**
     * @param $v
     * @return mixed
     */
    public function sidebar_calendar($v){
        $arr['bar_title']=$v['title'];
        return $arr;
    }

}//必须继承Plugin