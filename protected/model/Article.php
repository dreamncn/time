<?php
/**
 * Created by dreamn.
 * Date: 2019-08-22
 * Time: 19:45
 */
namespace app\model;
use app\lib\speed\mvc\Model;

/**
 * 文章操作类
 * Class Article
 * @package model
 */
class Article extends Model{
    public function __construct()
    {
        parent::__construct("blog_content");
    }

    /**
     * 添加文章
     * @param $arg
     * @return mixed
     */
    public function add($arg){
        return $this->insertOne($arg);
    }

    public function setOpt($id,$k,$v){
        return $this->setOption('gid',$id,$k,$v);
    }

    /**
     * 更新阅读量
     * @param $id
     * @return mixed
     */
    public function updateReadCount($id){
        return $this->execute("update ".$this->table_name.' set views = views + 1 where gid = :gid',array(':gid'=>$id));
    }

    /**
     * 通过ID删除
     * @param $id
     * @return mixed
     */
    public function del($id){
        return $this->delete()->where(["gid"=>$id])->commit();
    }

    /**
     * 通过ID获得文章
     * @param $id
     * @param string $article
     * @return bool|mixed
     */
    public function getArticleByID($id,$article='article'){
         if($article!=='')$condition= ["gid"=>$id,'type'=>$article];
         else   $condition= ["gid"=>$id];
        return $this->find($condition);
    }

    /**
     * 通过分类获取文章列表
     * @param $sort
     * @param $page
     * @param $size
     * @return array|mixed
     */
    public function getSortByPage($sort,$page,$size){
       return $this->select()->table('blog_sort_view')->where(['sname'=>$sort,'hide'=>0,'type'=>'article'])->orderBy('gid DESC')->limit(1,true,$page,$size)->commit();
    }

    /**
     * 通过标签获取文章列表
     * @param $tag
     * @param $page
     * @param $size
     * @return array|mixed
     */
    public function getTagsByPage($tag,$page,$size){
        return $this->select()->where(['hide=0 and type="article" and tag like :key',':key'=>"%".$tag."%"])->orderBy("date DESC")->limit(1,true,$page,$size)->commit();
    }

    /**
     * 根据输入查找文章
     * @param $key
     * @param $page
     * @param $size
     * @return array|mixed
     */
    public function getSearchByPage($key,$page,$size){
        return $this->select()->where(['hide=0 and type="article" and title like :key',':key'=>"%".$key."%"])->orderBy("date DESC")->limit(1,true,$page,$size)->commit();
    }

    /**
     * 按照日期降序获取文章列表
     * @param $condition
     * @param $page
     * @param $size
     * @return array|mixed
     */
    public function getByPage($condition,$page,$size){
        $condition["hide"]=0;//不隐藏的文章
        $condition["type"]="article";//是文章
        return $this->select()->table('blog_sort_view')->where($condition)->limit(1,true,$page,$size)->orderBy("top desc,date desc")->commit();
    }
    /**
     * 通过别名获取文章
     * @param $name
     * @return bool|mixed
     */
    public function getByAlians($name){
        return $this->find(["alians=:alians and hide=0",":alians"=>$name]);
    }

    /**
     * 进行标签统计
     */
    public function getTagCount(){
       $result=$this->select('tag')->where(['hide'=>0,"type"=>'article'])->commit();
       $tag=array();
       if(!empty($result)){
           foreach ($result as $v){
               $exp=explode(',',$v['tag']);
               foreach ($exp as $vv){
                   $vv=strtolower($vv);
                   if(!isset($tag[$vv]))$tag[$vv]=0;
                   $tag[$vv]++;
               }
           }
       }
       return $tag;
    }

    /**
     * 获取归档
     * @return array
     */
    public function getArchives()
    {
        $result=$this->query('select year(date) As year, month(date)As month, group_concat(day(date)) As days, group_concat(gid) As gids, group_concat(alians) AS alian,group_concat(title) As titles, group_concat(views) As views from (select date,gid,title,views,alians from `blog_content` where hide=0 and type="article" ORDER BY date) as a group by year(date),month(date) ORDER BY date DESC ');
        if(empty($result))return array();
        $arr=array();
        foreach($result as $v){
            $days=explode(",",$v['days']);
            $gids=explode(",",$v['gids']);
            $alian=explode(",",$v['alian']);
            $titles=explode(",",$v['titles']);
            $views=explode(",",$v['views']);
            for($i=0;$i<sizeof($days);$i++){
                $arr[$v['year'].'-'.$v['month']][]=array(
                    'day'=>$v['month']."-".$days[$i],
                    'gid'=>$gids[$i],'alians'=>$alian[$i],'title'=>$titles[$i],
                    'views'=>$views[$i]
                );
            }
        }
        return $arr;
    }

    /**
     * 获得文章阅读数
     * @return int|mixed
     */
    public function getCountView(){
        return $this->getSum('views');
    }

    /**
     * 后台获取所有用户列表
     * @param $page
     * @param $size
     * @param $count
     * @param null $condition
     * @return array|mixed
     */
    public function getByPageAdmin($page,$size,&$count,$condition=null){
        $condition1='type="article" and hide<>2';
        $condition[] =$condition1;
        $result=$this->select()->where($condition)->table('blog_sort_view')->orderBy('gid DESC')->limit(1,true,$page,$size)->commit();
        $count=($this->page===null)?sizeof($result):$this->page["total_count"];
        return $result;

    }
    public function getPages(){
       return $this->select()->where(['type="page" and hide<>2'])->orderBy("gid DESC")->commit();

    }
    /*
     * 置顶文章
     * */
    public function setTop($gid,$used){
        $this->update()->set(['top'=>0])->commit();
        $this->update()->set(['top'=>$used])->where(['gid'=>$gid])->commit();
        return true;
    }
    public function getByPageAdminPage($page,$size,&$count,$condition=null){

        $condition1='type="page"';
        $condition[] =$condition1;
         $result=$this->select()->table('blog_sort_view')->where($condition)->orderBy('gid DESC')->limit(1,true,$page,$size)->commit();
        $count=($this->page===null)?sizeof($result):$this->page["total_count"];
        return $result;

    }

    public function getIDBySort($arg)
    {
        return $this->select()->where(['sid'=>$arg])->commit();
    }




}