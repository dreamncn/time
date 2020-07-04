<?php
/**
 * Download.php
 * Created By Dreamn.
 * Date : 2020/7/4
 * Time : 12:12 下午
 * Description :
 */
namespace app\plugin\cn_dreamn_plugin_article_download\core;
use app\lib\blog\Plugin;

class Download extends Plugin{
    public function __construct()
    {
        parent::__construct('cn_dreamn_plugin_article_download');

    }

    public function add($gid,$url,$passwd,$title,$desc){
        return $this->insert(self::Duplicate)->table('blog_download')->keys(['url','passwd','title','desc','gid'],['url','passwd','title','desc'])->values([[$url,$passwd,$title,$desc,$gid]])->commit();
    }
    public function getByGid($gid){
        $result=$this->select()->table('blog_download')->where(['gid'=>$gid])->limit(1)->commit();
        if(empty($result)){
            return null;
        }
        return $result[0];
    }

    public function delByGid($gid)
    {
        return $this->delete()->where(['gid'=>$gid])->commit();
    }
}