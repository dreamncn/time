<?php
/**
 * Admin.php
 * Created By Dreamn.
 * Date : 2020/3/14
 * Time : 1:40 下午
 * Description :后台部分
 */

namespace app\theme\cn_dreamn_theme_even\core;

use app\includes\Web;
use app\lib\blog\Blog;
use app\lib\blog\Plugin;
use app\model\Article;
use app\model\Comment;
use app\model\Config;
use app\model\Sort;

class Admin extends Plugin
{
    public function __construct()
    {
        parent::__construct("cn_dreamn_theme_even");
    }

    public function viewDisplay()
    {
        $config=new Config();
        $arr['name']=$config->getData('author');
        $arr['title'] = '后台管理 - Dreamn';
        $arr['ver']=Blog::version;
        return $arr;
    }

    public function displayLogin($wechat = false)
    {
        if ($wechat) {
            $arr['title'] = '微信登录 - 请扫描二维码登录';
        }
        else {
            $arr['title'] = '后台登录';

        }
        return $arr;
    }
    public function include_console(){
        $article=new Article();
        $arr['readCount']=number_format($article->getCountView());
        $arr['articleCount']=number_format($article->getCount());
        $comment=new Comment();
        $arr['commentCount']=number_format($comment->getCount());
        $sort=new Sort();
        $arr['sortCount']=number_format($sort->getCount());
        $arr['ver']=Blog::version;
        $arr['url']=Blog::site;
        $arr['comment']= $comment->select()->where(['hide'=>0])->orderBy('date DESC')->limit(3)->commit();
        return $arr;
    }
    public function include_adminInfo(){
        $config=new Config();
        $arr['mail']=$config->getData('mail');
        $arr['info']=$config->getData('info');
        $web=new Web();
        $json= $web->get('http://whois.pconline.com.cn/ipJson.jsp?ip='.getClientIP().'&json=true');
        //dump(trim($json),true);
        $result=json_decode(chkCode(trim($json)));
        if($result){
            $arr['addr']=$result->addr;
        }else{
            $arr['addr']='未知地址';
        }
        return $arr;
}
}