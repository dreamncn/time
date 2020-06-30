<?php
namespace app\plugin\cn_dreamn_plugin_music\core;
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
        parent::__construct("cn_dreamn_plugin_music");
    }
    public function hookDo($arg){
        switch($arg['type']){
            case 'menu':
                $arr['albumname']=$this->getItem('albumname','追梦音乐');
                $arr['albumname1']=$this->getItem('albumname1','音乐精选');
                $arr['tips']=$this->getItem('tips','欢迎光临');
                $arr['songnameList']=$this->getItem('songnameList','月夜の飛行|归去来兮');
                $arr['songurl']=$this->getItem('songurl','');
                $arr['songIdList']=$this->getItem('songIdList','4879349wy|1357999894wy');
                $arr['isset']=false;
                if(isset($arg['isset']))
                    $arr['isset']=true;
                $result=$this->display('menu',$arr);
                return [$result];
                break;
            case 'set':
                $this->setItem('albumname',$arg['albumname']);
                $this->setItem('albumname1',$arg['albumname1']);
                $this->setItem('tips',$arg['tips']);
                $this->setItem('songurl',$arg['songurl']);
                $music=new music();
                $result=$music->analysis($arg['songurl']);
                if($result){
                    $data=$music->getData();
                    $this->setItem('songnameList',$data['name']);
                    $this->setItem('songIdList',$data['id']);
                    return [json_encode(array('state'=>true,'msg'=>'设置成功！'))];
                }else{
                    return [json_encode(array('state'=>false,'msg'=>"数据采集错误，请重新尝试！"))];
                }

                break;
        }
        return [];
    }
}//必须继承Plugin.