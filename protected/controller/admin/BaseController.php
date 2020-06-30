<?php

namespace app\controller\admin;

use app\lib\blog\Theme;
use app\model\Admin;

class BaseController extends Theme
{
    public $layout = "";
    public function init()
    {
        header("Content-type: text/html; charset=utf-8");
        session_start();
        //检查登录
        $arr=array(
            'allow'=>array(
                'main'=>array('login'=>'','loginpost'=>'','wechat'=>'','wechatpost'=>'','getkey'=>'')
            ),
            'nojump'=>array(
                'main'=>array('getkey'=>'')
            )
        );
        $user=new Admin();
        if($user->checkLogin(arg("token"))&&!isset($arr['nojump'][strtolower(arg('c'))][strtolower(arg('a'))])&&isset($arr['allow'][strtolower(arg('c'))][strtolower(arg('a'))])){
            $this->jump(url('admin/main','index'));
        }else if(!$user->checkLogin(arg("token"))&&!isset($arr['allow'][strtolower(arg('c'))][strtolower(arg('a'))])){
            $this->jump(url("admin/main","login"));
        }
        
    }

    /**
     * 重写hook函数，用于后端hook
     * @param $location
     * @param null $data
     * @param bool $isAdmin
     * @return null
     */
    public function hook($location, $data = null, $isAdmin = true)
    {
        return parent::hook($location, $data, true);
    }
    /**
     * @param null $tpl_name 模板名
     * @param bool $return
     * @param array $array  参数列表
     * @return false|string
     */
    public function display($tpl_name, $return = false,$array=null)
    {
        //做一些附加工作
        $this->i=DS.'i'.DS.'theme'.DS.$this->themeName.DS.'admin';
        if($array!==null)$array+=$this->hook('viewDisplay');
        else $array=$this->hook('viewDisplay');
        return parent::display($tpl_name, $return,$array);
    }


} 