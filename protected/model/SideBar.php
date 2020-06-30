<?php
/**
 * Name         :SideBar.php
 * Author       :dreamn
 * Date         :2020/2/14 15:49
 * Description  :侧栏
 */
namespace app\model;
use app\lib\blog\Theme;
use app\lib\speed\mvc\Model;

/**
 * Class SideBar
 * @package model
 * 分两种，主题自带的和用户自定义
 */
class SideBar extends Model{
    public function __construct()
    {
        parent::__construct('blog_sidebar');
    }

    /**
     * 后台添加一个侧栏
     * @param $title string
     * @param $html string 自定义这里不为空
     * @param $type string  区分自定义还是
     * @param $sort
     */

    public function add($title,$html,$type,$sort)
    {

        $this->insertOne(['title'=>$title,'html'=>$html,'type'=>$type,'sort'=>$sort]);
    }

    /**
     * 后台删除一个侧栏
     * @param $id
     */
    public function del($id){
        $this->delete()->where(['id'=>$id])->commit();
    }

    /**
     * 设置侧栏状态
     * @param $id
     * @param $opt
     * @param $val
     */
    public function setOpt($id,$opt,$val){
        $this->setOption('id',$id,$opt,$val);
    }

    /**
     * 删除主题自带的侧栏，切换主题的时候必须删除，防止造成错误
     */
    public function delTheme(){
        $this->delete()->where(['type'=>1])->commit();
    }

    /**
     * 获取sidebar（编译好的数组）
     * @return array
     */
    public function getSideBar(){
        $res=$this->getAll();
        $arr=array();
        if($res){
            $obj=new Theme();
            foreach ($res as $v){
                if(intval($v['type'])===1){
                    //第三方自带的，html部分是对应的html文件
                    $reArr=$obj->hook('sidebar_'.$v['html'],$v);
                    $arr[]=$obj->display('sidebar'.DS.$v['html'],true,$reArr);
                }else{
                    $reArr=$obj->hook('sidebar',$v);
                    $arr[]=$obj->display('sidebar',true,$reArr);
                }
            }
        }
        return $arr;
    }

    public function getAll()
    {
        return $res=$this->select()->orderBy('sort ASC')->commit();
    }


}