<?php
namespace app\plugin\cn_dreamn_plugin_article_download\core;
use app\lib\blog\Plugin;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

   /**
 * Created by dreamn.
 * Date: 2019-09-08
 * Time: 19:24
 */
/*
 * 绝对绝对不允许自行输出任何信息，所有信息的输出请直接调用内置函数
 * */
/*默认类名，不允许修改*/
class Admin extends Plugin{
    /**
     * Index constructor.
     */
    public function __construct()
    {
        parent::__construct("cn_dreamn_plugin_article_download");
    }
 

    public function hookDo($data){
        $download=new Download();
        switch ($data['type']){
            case "get":
                $result=$download->getByGid($data['gid']);
                if($result) return [json_encode(['code'=>0,'data'=>$result])];
                else return [json_encode(['code'=>-1,'data'=>$result])];
                break;

        }

    }

    public function hooksetArticle($data){
      
        if(!isset($data['gid'])||!isset($data["param"]['download_url'])||!isset($data["param"]['download_passwd'])||!isset($data["param"]['download_title'])||!isset($data["param"]['download_desc']))return ;
        $download=new Download();
        $download->add($data['gid'],$data["param"]['download_url'],$data["param"]['download_passwd'],$data["param"]['download_title'],$data["param"]['download_desc']);
    }

    public function hookinclude_newsEdit(){

        $return=['tpl'=>$this->display('tpl'),'js'=>$this->display('script')];
        return $return;
    }
}//必须继承Plugin.