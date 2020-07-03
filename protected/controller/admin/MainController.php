<?php

namespace app\controller\admin;
use app\Error;
use app\includes\AES;
use app\lib\blog\Plugin;

class MainController extends BaseController
{

    /**
     * 获取API通讯加密密钥
     */
    public function actionGetKey(){
        $_SESSION['key']=AES::getRandom(16);
        echo json_encode(array('key'=>$_SESSION['key']));
    }

    /**
     * index
     */
    public function actionIndex(){
        $this->data=array(
            array(
                'name'=>'内容管理',
                'icon'=>'iconziliaoneirongguanli_huaban',
                "url"=>"javascript:;",
                'subMenus'=>array(
                    array("name"=>"所有文章","icon"=>'iconguanli','url'=>url('admin','main','page',array('page'=>'newsList'))),
                    array("name"=>"评论管理","icon"=>'iconpinglun','url'=>url('admin','main','page',array('page'=>'commentList'))),
                    array("name"=>"页面管理","icon"=>'iconziliaoneirongguanli_huaban','url'=>url('admin','main','page',array('page'=>'pageList'))),
                    array("name"=>"分类管理","icon"=>'iconwangzhandaohang','url'=>url('admin','main','page',array('page'=>'sort'))),
                    array("name"=>"链接管理","icon"=>'iconchangyongicon-','url'=>url('admin','main','page',array('page'=>'url')))
                )
            ),
            array(
                'name'=>'网站设置',
                'icon'=>'iconwangzhan',
                "url"=>"javascript:;",
                'subMenus'=>array(
                    array("name"=>"博主信息","icon"=>'icongeren','url'=>url('admin','main','page',array('page'=>'adminInfo'))),
                    array("name"=>"侧栏设置","icon"=>'iconcaidan','url'=>url('admin','main','page',array('page'=>'sidebar'))),
                    array("name"=>"主题设置","icon"=>'icon984caidan_zhuti','url'=>url('admin','main','page',array('page'=>'themes'))),
                    array("name"=>"插件设置","icon"=>'iconchajian1','url'=>url('admin','main','page',array('page'=>'plugin'))),
                    array("name"=>"图床设置","icon"=>'icon14','url'=>url('admin','main','page',array('page'=>'pic'))),
                    array("name"=>"导航设置","icon"=>'iconxitongguanli','url'=>url('admin','main','page',array('page'=>'nav'))),
                )
            ),
            array(
                'name'=>'系统管理',
                'icon'=>'iconxitongguanli1',
                "url"=>"javascript:;",
                'subMenus'=>array(
                    array("name"=>"系统设置","icon"=>'iconxitongshezhi','url'=>url('admin','main','page',array('page'=>'system'))),
                    array("name"=>"验证码配置","icon"=>'iconyanzhengma2','url'=>url('admin','main','page',array('page'=>'captcha'))),
                    array("name"=>"邮件配置","icon"=>'iconyoujian','url'=>url('admin','main','page',array('page'=>'email'))),
                    array("name"=>"登录设置","icon"=>'iconicon','url'=>url('admin','main','page',array('page'=>'login'))),
                    /*array("name"=>"备份恢复","icon"=>'iconcaogao','url'=>url('admin','main','page',array('page'=>'backup'))),
                    array("name"=>"操作日志","icon"=>'iconxitongrizhi','url'=>url('admin','main','page',array('page'=>'log'))),*/
                )
            ),
            array(
                'name'=>'使用文档',
                'icon'=>'iconGroup-',
                "url"=>"javascript:;",
                'subMenus'=>array(
                    array("name"=>"使用帮助","icon"=>'iconxinxi','url'=>"https://doc.dreamn.cn/"),
                    array("name"=>"更新日志","icon"=>'iconrizhi','url'=>url('admin','main','page',array('page'=>'uplog'))),
                    /*array("name"=>"在线升级","icon"=>'iconshengji','url'=>url('admin','main','page',array('page'=>'update'))),*/
                )
            ),
        );
    }

    /**
     * 页面展示
     */
    public function actionPage(){
        $t_name=str_replace('.html','',arg("page"));
        $isMatched = preg_match_all('/[a-zA-Z]/', $t_name, $matches);
        if(!$isMatched)Error::err('[Err]No this page "'.$t_name.'"');
        $path=APP_VIEW.'theme'.DS.$this->getTheme()."/admin/include/".$t_name;

        $arr1=$this->hook('include_'.$t_name);
        if($arr1==null)
            $arr1=[];

        //主题HOOK

        $arr2=Plugin::hook('include_'.$t_name,null,true,[],null,true);
        if($arr2!==[])
            $arr2=['data'=>$arr2];

        //插件HOOK

        if(is_file($path.'.html')){
            $this->layout='include/layout';
            $this->display("/include/".$t_name,false,array_merge($arr1,$arr2));
        }
    }
    public function actionTpl(){
        $t_name=str_replace('.html','',arg("tpl"));
        $isMatched = preg_match_all('/[a-zA-Z]/', $t_name, $matches);
        if(!$isMatched)Error::err('[Err]No this tpl "'.$t_name.'"');
        $path=APP_VIEW.'theme'.DS.$this->getTheme()."/admin/tpl/".$t_name;
        $arr1=$this->hook('tpl_'.$t_name);
        if($arr1==null)
            $arr1=[];

        //主题HOOK

        $arr2=Plugin::hook('tpl_'.$t_name,null,true,[],null,true);
        if($arr2!==[])
            $arr2=['data'=>$arr2];
        if(is_file($path.'.html')){
            $this->display("/tpl/".$t_name,false,array_merge($arr1,$arr2));
        }
    }
    public function actionEdit(){
        $this->layout='';
        if(arg('type')==='html'){
            $this->display('edit/htmlEdit');
        }else{
            $this->display('edit/markdownEdit');
        }
    }

    public function actionPlugin(){
        $plugin=new Plugin(arg('p'));
        if($plugin->isEnable()){
            $result=Plugin::hook('Do',arg(),true,null,arg('p'),true);
            if($result!==null){
                foreach ($result as $val){
                    echo $val;
                }
            }
        }
    }


}