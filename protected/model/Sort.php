<?php
namespace app\model;
use app\lib\speed\mvc\Model;

/**
 * Created by dreamn.
 * Date: 2019-09-02
 * Time: 12:59
 */
class Sort extends Model{
    public function __construct()
    {
        parent::__construct("blog_sort");
    }

    /**
     * 添加分类
     * @param $sname
     */
    public function add($sname){
        $this->insert()->keys(['sname'])->values([[$sname]])->commit();
    }

    /**
     * 分类更新
     * @param $id
     * @param $sname
     */
    public function setData($id,$sname){
        $this->update()->set(["sname"=>$sname])->where(["sid"=>$id])->commit();
    }

    /**
     * 删除某个分类
     * @param $id
     */
    public function del($id){
        $this->delete()->where(["sid"=>$id])->commit();
    }
    /*
     * 取得所有的分类
     */
    public function get(){
        return $this->select()->commit();
    }
    public function getByPage($page,$size,&$count){
        $result = $this->select()->limit(1,true,$page,$size)->commit();
        $count=($this->page===null)?sizeof($result):$this->page["total_count"];
        return $result;
    }

    /**
     * 根据id取名称
     * @param $id
     * @return bool|mixed
     */
    public function getNameByID($id){
        return $this->find(["sid"=>$id],'sname');
    }

    /**
     * 根据名称取ID
     * @param $name
     * @return bool|mixed
     */
    public function getIDByName($name){
        return $this->find(["sname"=>$name],'sid');
    }


}