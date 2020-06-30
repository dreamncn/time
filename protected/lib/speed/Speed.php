<?php

namespace app;

use app\lib\speed\mvc\Controller;

/**
 * Class Speed
 * @package lib\speed
 */
class Speed
{
    const filter_post = 3;
    const filter_get = 1;
    const filter_cookie = 2;

    static public function Start()
    {
        //框架开始类
        Speed::Init();
        Speed::rewrite();
        Speed::createObj();

    }

    static public function Init()
    {
        if (isDebug()) {
            error_reporting(-1);
            ini_set("display_errors", "On");
        } else {
            error_reporting(E_ALL & ~(E_STRICT | E_NOTICE));
            ini_set("display_errors", "Off");
        }
        if ((!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == "https") || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) {
            $GLOBALS['http_scheme'] = 'https://';
        } else {
            $GLOBALS['http_scheme'] = 'http://';
        }

    }

    static public function rewrite()
    {
        Cache::init(365 * 24 * 60 * 60, APP_ROUTE);
        //初始化路由缓存，不区分大小写
        $url = strtolower(urldecode($_SERVER['REQUEST_URI']));
        if ($GLOBALS['cache'] && $data=Cache::get($url) !== null && isset($data['real']) && isset($data['route'])) {
            if (isDebug()) {
                logs('[route]Find Rewrite Cache: ' . $url . ' => ' . $data['real'], 'info');
            }
            $route_arr_cp = $data['route'];

        } else {
            if (isDebug()) {
                logs('[route]Not Find Rewrite Cache: ' . $url, 'info');
            }
            $route_arr = [];
            if (!empty($GLOBALS['rewrite']))
                foreach ($GLOBALS['rewrite'] as $rule => $mapper) {
                    $rule = ($rule);
                    if ('/' == $rule) $rule = '/$';
                    if (0 !== stripos($rule, $GLOBALS['http_scheme']))
                        $rule = $GLOBALS['http_scheme'] . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER["SCRIPT_NAME"]), '/\\') . '/' . $rule;
                    $rule = '/' . str_ireplace(
                            array('\\\\', $GLOBALS['http_scheme'], '/', '<', '>', '.'),
                            array('', '', '\/', '(?P<', '>[\x{4e00}-\x{9fa5}a-zA-Z0-9_-]+)', '\.'), $rule) . '/u';
                    $rule = str_replace('\/\/', '\/', $rule);

                    if (preg_match($rule, ($GLOBALS['http_scheme'] . $_SERVER['HTTP_HOST'] . urldecode($_SERVER['REQUEST_URI'])), $matchs)) {
                        $route = explode("/", $mapper);
                        if (isset($route[2])) {
                            list($route_arr['m'], $route_arr['c'], $route_arr['a']) = $route;
                        } elseif (isset($route[1])) {
                            list($route_arr['m'], $route_arr['c']) = $route;
                        } elseif (isset($route[0])) {
                            list($route_arr['m']) = $route;
                        }
                        foreach ($matchs as $matchkey => $matchval) {
                            if (!is_int($matchkey)) $route_arr[$matchkey] = $matchval;
                        }
                        break;
                    }
                }
            if(isset($route_arr['m']))
            $route_arr['m']=strtolower($route_arr['m']);
            if(isset($route_arr['c']))
            $route_arr['c']=strtolower($route_arr['c']);
            if(isset($route_arr['a']))
            $route_arr['a']=strtolower($route_arr['a']);
            $route_arr = $_GET + $route_arr;
            $route_arr_cp = $route_arr;

            //重写缓存表
            $__module = 'index';
            if (isset($route_arr['m'])) {

                $__module = $route_arr['m'];
                unset($route_arr['m']);
            }
            $__controller = 'main';
            if (isset($route_arr['c'])) {

                $__controller = $route_arr['c'];
                unset($route_arr['c']);
            }
            $__action = 'index';
            if (isset($route_arr['a'])) {

                $__action = $route_arr['a'];
                unset($route_arr['a']);
            }
            $real = "$__module/$__controller/$__action";
            if (sizeof($route_arr)) {
                $real .= '?' . http_build_query($route_arr);
            }
            $arr = [
                'real' => $real,
                'route' => $route_arr_cp
            ];
            Cache::set($url, $arr);
            if (isDebug()) {
                logs('[route]Rewrite Cache: ' . $real, 'info');
            }
        }

        $_REQUEST = array_merge($_GET, $_POST, $_COOKIE, $route_arr_cp);

        GLOBAL $__module, $__controller, $__action;
        $__module = isset($_REQUEST['m']) ? $_REQUEST['m'] : 'index';
        $__controller = isset($_REQUEST['c']) ? $_REQUEST['c'] : 'main';
        $__action = isset($_REQUEST['a']) ? $_REQUEST['a'] : 'index';


    }

    static public function createObj()
    {
        GLOBAL $__module, $__controller, $__action;
        if ($__controller === 'base') Error::_err_router("Err: Controller 'BaseController' is not correct!Not allowed to be accessed！");

        $controller_name = ucfirst($__controller) . 'Controller';
        $action_name = 'action' . $__action;

        if (isDebug()) {
            logs('[mvc]Module: ' . $__module, 'info');
            logs('[mvc]Controller: ' . $controller_name, 'info');
            logs('[mvc]Action: ' . $action_name, 'info');
            logs('[Speed]Routing time-consuming: ' . strval((microtime(true) - $GLOBALS['start']) * 1000) . 'ms', 'info');
        }

        if (!self::is_available_classname($__module)) Error::_err_router("Err: Module '$__module' is not correct!");
        if (!is_dir(APP_DIR . DS . 'protected' . DS . 'controller' . DS . $__module)) Error::_err_router("Err: Module '$__module' is not exists!");

        $controller_name = 'app\\controller\\' . $__module . '\\' . $controller_name;

        if (!self::is_available_classname($__controller)) Error::_err_router("Err: Controller '$controller_name' is not correct!");
        if (!class_exists($controller_name, true)) Error::_err_router("Err: Controller '$controller_name' is not exists!");
        if (!method_exists($controller_name, $action_name)) Error::_err_router("Err: Method '$action_name' of '$controller_name' is not exists!");

        /**
         * @var $controller_obj Controller
         */
        $controller_obj = new $controller_name();
        $controller_obj->$action_name();

        if ($controller_obj->_auto_display) {
            $auto_tpl_name = $__controller . '_' . $__action;
            if (file_exists(APP_VIEW . $__module . DS . $auto_tpl_name . '.html')) $controller_obj->display($auto_tpl_name);
        }
        if (isDebug()) {
            logs('[Speed]Total time-consuming: ' . strval((microtime(true) - $GLOBALS['start']) * 1000) . 'ms', 'info');
        }

    }

    /**
     * @param $name
     * @return false|int
     */
    static public function is_available_classname($name)
    {
        return preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $name);
    }


}
