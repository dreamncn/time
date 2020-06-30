<?php
namespace app\controller\index;
use app\Error;

use app\includes\StringDeal;
use app\lib\blog\Plugin;
use app\model\Link;


class MainController extends BaseController
{
    // 首页
    public function actionIndex()
    {
        $arr=$this->hook('displayIndex');
       $this->display('main',false,$arr);
    }

    public function actionJump()
    {
        $url=base64_decode(urldecode(arg('url')));
        $s=new StringDeal();
        if($s->isUrl($url)){
            $this->layout='';
            $this->display('go',false,array('url'=>$url,'host'=>getAddress()));
        }else{
            Error::err('[Warn]"'.$url.'"is not a useful url!');
        }
    }
    public function actionUrls(){
        $link=new Link();
        $result=$link->getNoHide();
        $arr=$this->hook('displayLink',$result);
        $this->display('url',false,$arr);
    }
    public function actionSearch(){
        if(arg('search')!==null) {
            $arr=$this->hook('displaySearch',arg('search'));
            $this->display('search',false,$arr);

        }else Error::err('[Warn]搜索关键字不允许为空！');

    }

    public function actionPlugin(){
        if(arg('n')!==null){
            $pluginName=arg('n');
            $string=Plugin::hook('Do',arg(),true,'',$pluginName);
            foreach ($string as $value){
                echo $value;
            }
        }
    }
}