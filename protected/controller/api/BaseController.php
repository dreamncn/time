<?php

namespace app\controller\api;

use app\includes\AES;
use app\lib\blog\Plugin;
use app\lib\blog\Theme;
use app\model\Admin;
use app\model\Config;

class BaseController extends Theme
{
    public $layout = "";
    protected $arg = array();

    public function init()
    {
        session_start();
        header("Content-type: text/html; charset=utf-8");
        $config=new Config();
        $data['login_type']= $config->getData('login_type');
        if(!Plugin::hook('isLogin',arg(),true,false,$data['login_type']))
            $this->jump(url('index','login','index'));
        
        if (isPost() && arg("param") !== null) {
            //所有传递的数据在这里解密
            $key = isset($_SESSION['key']) ? $_SESSION['key'] : false;
            $_SESSION['key'] = false;//key重置为错误值
            if (!$key) exit(json_encode(array("state" => false, "msg" => "数据来源不可信！")));
            $aes = new AES();
            $result = $aes->decrypt(arg("param"), $key);
            $this->arg = json_decode($result, true);
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
     * @param array $array 参数列表
     * @return false|string
     */
    public function display($tpl_name, $return = false, $array = null)
    {
        //做一些附加工作
        $this->i = DS . 'i' . DS . 'theme' . DS . $this->themeName . DS . 'admin';
        if ($array !== null) $array += $this->hook('viewDisplay');
        else $array = $this->hook('viewDisplay');
        return parent::display($tpl_name, $return, $array);
    }

    public static function err404($module, $controller, $action, $msg)
    {
        header("HTTP/1.0 404 Not Found");
        exit(json_encode(array(
            'code' => -1,
            'count' => 0,
            'msg' => '',
            'data' => null
        )));
    }

    public function api($state, $data = null, $count = 0, $msg = '')
    {
        if (is_bool($state))
            if ($state) $code = 0;
            else $code = -1;
        else $code = $state;
        exit(json_encode(array(
            'code' => $code,
            'count' => $count,
            'msg' => $msg,
            'data' => $data
        )));
    }


} 