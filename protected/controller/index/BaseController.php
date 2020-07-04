<?php

namespace app\controller\index;

use app\lib\blog\Plugin;
use app\lib\blog\Theme;
use app\model\Config;

class BaseController extends Theme
{
    public $layout = "layout";

    public function init()
    {
        header("Content-type: text/html; charset=utf-8");
        session_start();
        $conf = new Config();
        if ($conf->getData('blog_open') != 'true') {

            if (!(arg('c') == 'login' && arg('a') == 'index')) {
                $this->layout = "";
                $this->display('close');
                exit;
            }

        }
    }

    /**
     * @param null $tpl_name 模板名
     * @param bool $return   是否返回HTML
     * @param array $array 参数列表
     * @return false|string
     */
    public function display($tpl_name, $return = false, $array = null)
    {
        $head = Plugin::hook('LayoutHead', null, true, []);
        $head_css = '';
        if (!empty($head))
            foreach ($head as $val) {
                if (is_array($val)) {
                    foreach ($val as $val2) {
                        $head_css .= $val2;
                    }
                } else {
                    $head_css .= $val;
                }

            }
        $array['plugin_css'] = $head_css;
        $footer = Plugin::hook('LayoutFooter', null, true, []);
        $footer_js = '';
        
        if (!empty($footer))
            foreach ($footer as $val) {
                if (is_array($val)) {
                    foreach ($val as $val2) {
                        $footer_js .= $val2;
                    }
                } else  $footer_js .= $val;
            }
        $array['plugin_js'] = $footer_js;
        //做一些附加工作
        $this->i = DS . 'i' . DS . 'theme' . DS . $this->themeName . DS . 'index';
        if ($array !== null) $array += $this->hook('viewDisplay');
        else $array = $this->hook('viewDisplay');
        return parent::display($tpl_name, $return, $array);
    }
} 