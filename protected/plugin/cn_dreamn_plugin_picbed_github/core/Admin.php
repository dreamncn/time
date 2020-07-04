<?php
namespace app\plugin\cn_dreamn_plugin_picbed_github\core;
use app\lib\blog\Plugin;

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
        parent::__construct("cn_dreamn_plugin_picbed_github");
    }
    public function hookPicList($arg){
       $arg=['title'=>'Github图床','picbed'=>'cn_dreamn_plugin_picbed_github'];
       return $arg;
    }

    public function hookUpload($data){

        $github=new GitHub($this->getItem('ownerRepo'),$this->getItem('key'));
        return $github->upload($data['tmpFileName'],$data['newFileName'],$data['fileType']);
    }

    public function hookDo($data){
        if(!isset($data['ownerRepo'])||!isset($data['key']))
            return [json_encode(['code'=>-1,'msg'=>'保存失败'])];
        $this->setItem('ownerRepo',$data['ownerRepo']);
        $this->setItem('key',$data['key']);

        return [json_encode(['code'=>0,'msg'=>'保存成功'])];
    }

    public function hookinclude_pic(){
        $tpl=$this->display('set',['ownerRepo'=>$this->getItem('ownerRepo'),'key'=>$this->getItem('key')]);
        $return=['title'=>"GitHub配置",'tpl'=>$tpl,'js'=>$this->display('setScript')];
        return $return;
    }
}//必须继承Plugin.