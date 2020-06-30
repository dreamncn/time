<?php

use app\Cache;
use app\lib\speed\Dump;
use app\Log;
use app\Speed;


function url($m='index',$c = 'main', $a = 'index', $param = array())
{
    if(is_array($m))
        $param=$m;
    if (isset($param['m'])) {
        $m = $param['m'];
        unset($param['m']);
    }
    if (isset($param['c'])) {
        $c = $param['c'];
        unset($param['c']);
    }
    if (isset($param['a'])) {
        $a = $param['a'];
        unset($param['a']);
    }

    $params = empty($param) ? '' : '&' . http_build_query($param);
    $route = "$m/$c/$a";
    $url = $_SERVER["SCRIPT_NAME"] . "?m=$m&c=$c&a=$a$params";
    Cache::init(365 * 24 * 60 * 60, APP_ROUTE);
    //初始化路由缓存，不区分大小写
    $data = Cache::get('route'.$url);
    if($data!==null)return  $data;

    if (!empty($GLOBALS['rewrite'])) {
        if (!isset($GLOBALS['url_array_instances'][$url])) {
            foreach ($GLOBALS['rewrite'] as $rule => $mapper) {
                $mapper = '/^' . str_ireplace(array('/', '<a>', '<c>', '<m>'), array('\/', '(?P<a>\w+)', '(?P<c>\w+)', '(?P<m>\w+)'), $mapper) . '/i';
                if (preg_match($mapper, $route, $matchs)) {
                    $rule = str_ireplace(array('<a>', '<c>', '<m>'), array($a, $c, $m), $rule);
                    $match_param_count = 0;
                    $param_in_rule = substr_count($rule, '<');
                    if (!empty($param) && $param_in_rule > 0) {
                        foreach ($param as $param_key => $param_v) {
                            if (false !== stripos($rule, '<' . $param_key . '>')) $match_param_count++;
                        }
                    }
                    if ($param_in_rule == $match_param_count) {
                        $GLOBALS['url_array_instances'][$url] = $rule;
                        if (!empty($param)) {
                            $_args = array();
                            foreach ($param as $arg_key => $arg) {
                                $count = 0;
                                $GLOBALS['url_array_instances'][$url] = str_ireplace('<' . $arg_key . '>', $arg, $GLOBALS['url_array_instances'][$url], $count);
                                if (!$count) $_args[$arg_key] = $arg;
                            }
                            $GLOBALS['url_array_instances'][$url] = preg_replace('/<\w+>/', '', $GLOBALS['url_array_instances'][$url]) . (!empty($_args) ? '?' . http_build_query($_args) : '');
                        }

                        if (0 !== stripos($GLOBALS['url_array_instances'][$url], $GLOBALS['http_scheme'])) {
                            $GLOBALS['url_array_instances'][$url] = $GLOBALS['http_scheme'] . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER["SCRIPT_NAME"]), '/\\') . '/' . $GLOBALS['url_array_instances'][$url];
                        }
                        Cache::set('route'.$url,$GLOBALS['url_array_instances'][$url]);
                        return $GLOBALS['url_array_instances'][$url];
                    }
                }
            }
            return isset($GLOBALS['url_array_instances'][$url]) ? $GLOBALS['url_array_instances'][$url] : $url;
        }
        return $GLOBALS['url_array_instances'][$url];
    }
    return $url;
}

/**
 * @param null $var 需要输出的变量
 * @param bool $exit 是否退出
 */

function dump($var, $exit = false)
{
    $line = debug_backtrace()[0]['file'] . ':' . debug_backtrace()[0]['line'];
    echo <<<EOF
<style>pre {display: block;padding: 9.5px;margin: 0 0 10px;font-size: 13px;line-height: 1.42857143;color: #333;word-break: break-all;word-wrap: break-word;background-color:#f5f5f5;border: 1px solid #ccc;border-radius: 4px;}</style><div style="text-align: left">
<pre class="xdebug-var-dump" dir="ltr"><small>{$line}</small>\r\n
EOF;

    $dump = new Dump();
    $dump->dumpType($var);

    echo '</pre></div>';


    if ($exit) exit;
}

/**
 * @param string $name
 * @param string $default
 * @param bool $trim 移除字符串两侧的空白字符或其他预定义字符
 * @param string $filter
 * @return mixed|string|null
 */

function arg($name = null, $default = null, $trim = false, $filter = null)
{
    switch ($filter) {
        case Speed::filter_get:
            $_REQUEST = $_GET;
            break;
        case Speed::filter_post:
            $_REQUEST = $_POST;
            break;
        case Speed::filter_cookie:
            $_REQUEST = $_COOKIE;
            break;
        default:
    }
    if (!isset($_REQUEST['m'])) $_REQUEST['m'] = 'index';
    if ($name) {
        if (!isset($_REQUEST[$name])) return $default;
        $arg = $_REQUEST[$name];
        if ($trim) $arg = trim($arg);
    } else {
        $arg = $_REQUEST;
    }
    return $arg;
}

/**
 * 日志记录
 * @param string $msg
 * @param string $type
 * @param string $name
 */
function logs($msg, $type = 'debug', $name = 'speedphp')
{

    $log = new Log(APP_LOG . date('Y-m-d') . DS . $name . '.log');
    switch ($type) {
        case 'debug':
            $log->DEBUG($msg);
            break;
        case 'info':
            $log->INFO($msg);
            break;
        case 'warn':
            $log->WARN($msg);
            break;
        default:
            $log->ERROR($msg);
            break;
    }
}

/**
 * 获得完整域名（包含协议）
 * @return string
 */
function getAddress()
{
    return $GLOBALS['http_scheme'] . $_SERVER["HTTP_HOST"];
}

/**
 * 获取客户端浏览器信息 添加win10 edge浏览器判断
 * @param null
 * @return string
 */
function getBroswer()
{
    $sys = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';  //获取用户代理字符串
    if (stripos($sys, "Firefox/") > 0) {
        preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
        $exp[0] = "Firefox";
        $exp[1] = $b[1];  //获取火狐浏览器的版本号
    } elseif (stripos($sys, "Maxthon") > 0) {
        preg_match("/Maxthon\/([\d.]+)/", $sys, $aoyou);
        $exp[0] = "傲游";
        $exp[1] = $aoyou[1];
    } elseif (stripos($sys, "MSIE") > 0) {
        preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
        $exp[0] = "IE";
        $exp[1] = $ie[1];  //获取IE的版本号
    } elseif (stripos($sys, "OPR") > 0) {
        preg_match("/OPR\/([\d.]+)/", $sys, $opera);
        $exp[0] = "Opera";
        $exp[1] = $opera[1];
    } elseif (stripos($sys, "Edge") > 0) {
        //win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
        preg_match("/Edge\/([\d.]+)/", $sys, $Edge);
        $exp[0] = "Edge";
        $exp[1] = $Edge[1];
    } elseif (stripos($sys, "Chrome") > 0) {
        preg_match("/Chrome\/([\d.]+)/", $sys, $google);
        $exp[0] = "Chrome";
        $exp[1] = $google[1];  //获取google chrome的版本号
    } elseif (stripos($sys, 'rv:') > 0 && stripos($sys, 'Gecko') > 0) {
        preg_match("/rv:([\d.]+)/", $sys, $IE);
        $exp[0] = "IE";
        $exp[1] = $IE[1];
    } else {
        $exp[0] = "未知浏览器";
        $exp[1] = "";
    }
    return $exp[0] . '(' . $exp[1] . ')';
}

/**
 * 获取客户端操作系统信息包括win10
 * @param null
 * @return string
 */
function getOS()
{
    $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $os = false;

    if (preg_match('/win/i', $agent) && strpos($agent, '95')) {
        $os = 'Windows 95';
    } else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90')) {
        $os = 'Windows ME';
    } else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent)) {
        $os = 'Windows 98';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent)) {
        $os = 'Windows Vista';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent)) {
        $os = 'Windows 7';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent)) {
        $os = 'Windows 8';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent)) {
        $os = 'Windows 10';#添加win10判断
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent)) {
        $os = 'Windows XP';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent)) {
        $os = 'Windows 2000';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent)) {
        $os = 'Windows NT';
    } else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent)) {
        $os = 'Windows 32';
    } else if (preg_match('/linux/i', $agent)) {
        $os = 'Linux';
    } else if (preg_match('/unix/i', $agent)) {
        $os = 'Unix';
    } else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent)) {
        $os = 'SunOS';
    } else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent)) {
        $os = 'IBM OS/2';
    } else if (preg_match('/Mac/i', $agent)) {
        $os = 'Mac OS X';
    } else if (preg_match('/PowerPC/i', $agent)) {
        $os = 'PowerPC';
    } else if (preg_match('/AIX/i', $agent)) {
        $os = 'AIX';
    } else if (preg_match('/HPUX/i', $agent)) {
        $os = 'HPUX';
    } else if (preg_match('/NetBSD/i', $agent)) {
        $os = 'NetBSD';
    } else if (preg_match('/BSD/i', $agent)) {
        $os = 'BSD';
    } else if (preg_match('/OSF1/i', $agent)) {
        $os = 'OSF1';
    } else if (preg_match('/IRIX/i', $agent)) {
        $os = 'IRIX';
    } else if (preg_match('/FreeBSD/i', $agent)) {
        $os = 'FreeBSD';
    } else if (preg_match('/teleport/i', $agent)) {
        $os = 'teleport';
    } else if (preg_match('/flashget/i', $agent)) {
        $os = 'flashget';
    } else if (preg_match('/webzip/i', $agent)) {
        $os = 'webzip';
    } else if (preg_match('/offline/i', $agent)) {
        $os = 'offline';
    } else {
        $os = '未知操作系统';
    }
    return $os;
}

/**
 * 获取客户端真实IP
 * @return array|false|mixed|string
 */
function getClientIP()
{
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "127.0.0.1"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "127.0.0.1"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "127.0.0.1"))
        $ip = getenv("REMOTE_ADDR");
    else if (isset($_SERVER["REMOTE_ADDR"]) && $_SERVER["REMOTE_ADDR"] && strcasecmp($_SERVER["REMOTE_ADDR"], "unknown"))
        $ip = $_SERVER["REMOTE_ADDR"];
    else
        $ip = "127.0.0.1";
    return $ip;
}

/**
 * 获取本机IP
 * @return string
 */
function getMyIp(){
    return gethostbyname(gethostname());
}
/**
 * 检查编码
 * @param $string
 * @return string
 */
function chkCode($string)
{
    $encode = mb_detect_encoding($string, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
    return mb_convert_encoding($string, 'UTF-8', $encode);
}

/**
 * 获取header
 * @return array|false
 */
function getHeader() {
    if(function_exists('getallheaders'))return getallheaders();
    $headers = array();
    foreach ($_SERVER as $key => $value) {
        if ('HTTP_' == substr($key, 0, 5)) {
            $headers[ucfirst(strtolower(str_replace('_', '-', substr($key, 5))))] = $value;
        }
        if (isset($_SERVER['PHP_AUTH_DIGEST'])) {
            $header['AUTHORIZATION'] = $_SERVER['PHP_AUTH_DIGEST'];
        } elseif (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            $header['AUTHORIZATION'] = base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
        }
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $header['CONTENT-LENGTH'] = $_SERVER['CONTENT_LENGTH'];
        }
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $header['CONTENT-TYPE'] = $_SERVER['CONTENT_TYPE'];
        }
    }
    return $headers;
}

/**
 * 判断当前是否为调试状态
 * @return bool
 */
function isDebug(){
    return isset($GLOBALS['debug'])&&$GLOBALS['debug'];
}

/**
 * 取随机字符串
 * @param int $length
 * @return string
 */
 function getRandom($length = 8)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $password;
}

/**
 * 是否为Pjax请求
 * @return bool
 */
function isPjax(){
    return (isset($_SERVER['HTTP_X_PJAX']) && $_SERVER['HTTP_X_PJAX'] == 'true');
}
/**
 * 是否是AJAx提交的
 * @return bool
 */
function isAjax(){
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
}
/**
 * 是否是GET提交的
 */
function isGet(){
    return $_SERVER['REQUEST_METHOD'] == 'GET' ? true : false;
}
/**
 * 是否是POST提交
 * @return int
 */
function isPost() {
    return $_SERVER['REQUEST_METHOD'] == 'POST' ?  true : false;
}