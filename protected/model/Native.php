<?php
namespace app\model;
use app\lib\speed\mvc\Model;

/**
 * Created by dreamn.
 * Date: 2019-09-02
 * Time: 12:59
 */
class Native extends Model{
    public function __construct()
    {
        parent::__construct("blog_nav");
    }

    /**
     * 添加导航标签
     * @param $arg array 参数列表
     * @return mixed
     */
    public function add($arg){
        return $this->insertOne($arg);
    }

    /**
     * 设置导航标签
     * @param $id
     * @param $opt
     * @param $value
     * @return mixed
     */
    public function setOpt($id,$opt,$value){
        return $this->setOption("id",$id,$opt,$value);
    }

    /**
     * 删除指定的nav
     * @param $id
     * @return mixed
     */
    public function del($id){
        return $this->delete()->where(["id"=>$id])->commit();
    }

    /**
     * 获得所有nav，后台获取
     * @return array|bool|mixed
     */
    public function getAll(){
        $result=$this->select()->orderBy('pid ASC')->commit();
        if(empty($result))return false;
        $i=0;
        foreach ($result as $v){
            if(intval($v["lastest"])!==0){
                foreach ($result as $vv) {
                    if(intval($vv["id"])===(intval($v["lastest"])))
                        $result[$i]["lastName"] = $vv["nname"];
                }
            }

            $i++;
        }
        return $result;
    }

    /**
     * 前台获取不隐藏的nav
     * @return array|mixed
     */
    public function getNoHide(){
        $result=$this->select()->where(['hide=0'])->orderBy('pid ASC')->commit();
        $data=[];
        foreach ($result as $val){
            if($val['lastest']!=='0')continue;
            $return=$val;
             if($val['nexts']!==""){
                 $next=explode(",",$val['nexts']);
                 foreach ($next as $val2){
                     foreach ($result as $val3){
                          if($val2==$val3['id']){
                              $return['data'][]=$val3;
                          }
                     }
                 }
             }
             $data[]=$return;
        }
        return $data;
    }

    /**
     * 通过id获得nav信息
     * @param $id
     * @return bool|mixed
     */
    public function gerById($id){
        return $this->find(['id'=>$id]);
    }

    /**
     * 获得没有上级的nav
     * @return array|mixed
     */
    public function getNoLastest(){
        return $this->select()->where(['lastest=0'])->orderBy('pid ASC')->commit();
    }
}