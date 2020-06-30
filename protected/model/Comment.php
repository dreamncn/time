<?php
namespace app\model;
use app\lib\speed\mvc\Model;

/**
 * Created by dreamn.
 * Date: 2019-09-02
 * Time: 12:59
 */

class Comment extends Model{
    public function __construct()
    {
        parent::__construct("blog_comment");
    }

    /**
     * @desc 添加评论
     * @param array $arg
     * @return mixed
     */
    public function add($arg=array()){
        return $this->insertOne($arg);
    }

    /**
     * @desc 通过comment id删除
     * @param $id
     * @return mixed
     */
    public function delByCid($id){
        return $this->delete()->where(["cid"=>$id])->commit();
    }

    public function isExist($cid){
        $result=$this->find(['cid'=>$cid]);
        if(!empty($result))return true;
        else return false;
    }
    /**
     * @desc 通过文章ID删除，一般在删除文章的时候删除
     * @param $id
     * @return mixed
     */
    public function delByGid($id){
       return $this->delete()->where(["gid"=>$id])->commit();
    }

    /**
     * @desc 根据文章id查找
     * @param $id
     * @return array|mixed
     */
    public function getByGid($id){
        return $this->select()->where(["gid"=>$id,"hide"=>0])->commit();
    }

    /**
     * @desc 根据页面加载评论
     * @param $id
     * @param $page
     * @param $size
     * @return array|mixed
     */
    public function getByArticle($id,$page=null,$size=null){
        $bool=($page&&$size)?true:false;
        return $this->select()->where(['gid'=>$id,'hide'=>0])->orderBy("cid DESC")->limit($size,$bool,$page,$size)->commit();
    }

    /**
     * @desc 获得最新的几条评论
     * @param $count int 获取评论数
     * @return array|mixed
     */
    public function getNewest($count){
        return $this->select()->where(['hide'=>0])->orderBy('cid DESC')->limit($count)->commit();
    }

    /**
     * @desc 进行评论设置
     * @param $id
     * @param $opt
     * @param $value
     * @return mixed
     */
    public function setOpt($id,$opt,$value){
        return $this->setOption('cid',$id,$opt,$value);
    }
    public function getByPage($page,$size,$condition,&$count){
        $result=$this->select()->table('blog_comment_view')->where($condition)->limit(1,true,$page,$size)->commit();
        $count=($this->page===null)?sizeof($result):$this->page["total_count"];
        return $result;
    }
    public function getTitles(){
        return $this->select("gid,title")->table('blog_comment_view')->commit();
    }

    public function getByID($id){//根据评论id查找
        return $this->find(["cid"=>$id]);
    }

}