<?php

namespace app\controller\admin;

use app\lib\blog\Plugin;
use app\lib\blog\Theme;
use app\model\Config;

class BaseController extends Theme
{
    public $layout = "";
    public function init()
    {
        header("Content-type: text/html; charset=utf-8");
        session_start();
        $config=new Config();
        $data['login_type']= $config->getData('login_type');
        if(!Plugin::hook('isLogin',arg(),true,false,$data['login_type']))
            $this->jump(url('index','login','index'));
        
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