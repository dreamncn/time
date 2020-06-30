<?php
namespace app\model;
use app\lib\speed\mvc\Model;

/**
 * Created by dreamn.
 * Date: 2019-09-02
 * Time: 12:59
 */
class Link extends Model{
    public function __construct()
    {
        parent::__construct("blog_links");
    }

    /**
     * 添加友情链接
     * @param $name
     * @param $url
     * @param $description
     * @param bool $hide
     */
    public function add($name,$url,$description,$hide=false){
        $this->insertOne(array("sitename"=>$name,"siteurl"=>$url,"description"=>$description,"hide"=>$hide));
    }
    public function setData($id,$name,$url,$description,$hide=false){
        $this->update()->where(['id'=>$id])->set(["sitename"=>$name,"siteurl"=>$url,"description"=>$description,"hide"=>$hide])->commit();
    }
    /**
     * 删除友情链接
     * @param $id
     */
    public function del($id){
        $this->delete()->where(["id"=>$id])->commit();
    }

    /**
     * 获取友链
     * @return array|mixed
     */
    public function getNoHide(){
        return $this->select()->where(['hide=0'])->commit();
    }

    /**
     * [后台]获取所有链接
     * @return array|mixed
     */
    public function get(){
        return  $this->select()->commit();
    }

    /**
     * 设置友链
     * @param $id
     * @param $opt
     * @param $value
     * @return mixed
     */
    public function setOpt($id,$opt,$value){
        return $this->setOption('id',$id,$opt,$value);
    }
}