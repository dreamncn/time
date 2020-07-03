<?php
/**
 * Name         :Plugin.php
 * Author       :dreamn
 * Date         :2020/2/11 18:06
 * Description  :插件控制器
 */

namespace app\lib\blog;

use app\includes\File;
use app\lib\speed\mvc\Model;
use ZipArchive;

class Plugin extends Model
{
    //插件目录
    const DIR = APP_DIR . DS . 'protected' . DS . 'plugin' . DS;
    //插件变量

    protected $pluginName = '';//插件名
    protected $pluginDir = '';//该插件目录
    protected $err = '';//错误信息
    protected $viewDir = '';//html位置
    protected $iDir = '';//html位置

    /**
     * Plugin constructor.
     * @param string $plugin_name 插件名称
     */
    public function __construct($plugin_name = null)
    {
        if ($plugin_name !== null) {
            $this->pluginName = $plugin_name;
            $this->pluginDir = self::DIR . $plugin_name;
            $this->viewDir = APP_VIEW . 'plugin/' . $plugin_name . '/';
            $this->iDir = '/i/plugin/' . $plugin_name . '/';
        }
        parent::__construct('blog_plugin');
    }

    /*
     * 在博客加载的时候进行插件注册并应用
     */
    public static function register()
    {
        $dir = scandir(self::DIR);
        $arr = array();
        foreach ($dir as $v) {
            if ($v === '.' || $v === '..') continue;
            if (file_exists(self::DIR . DS . $v . DS . '.used'))
                $arr[] = $v;//如果插件在使用则加入数组

        }
        global $plugin_list;
        $plugin_list = $arr;

    }

    private function resetInfo($plugin = null)
    {
        if ($plugin !== null) {
            $this->pluginName = $plugin;
            $this->pluginDir = self::DIR . $plugin;
            $this->viewDir = APP_VIEW . 'plugin/' . $plugin . '/';
            $this->iDir = '/i/plugin/' . $plugin . '/';
        }
    }

    public function getAll()
    {
        $dir = scandir(self::DIR);
        $arr = array();
        foreach ($dir as $v) {
            if ($v === '.' || $v === '..') continue;
            if ($this->isPlugin($v)) {
                $json = json_decode(file_get_contents(self::DIR . DS . $v . DS . 'info.json'));
                if (file_exists(self::DIR . DS . $v . DS . '.used')) $json->enable = true;
                else  $json->enable = false;
                if ($this->isInstall($v)) $json->install = true;
                else $json->install = false;
                $arr[] = $json;
            }

        }
        return $arr;

    }

    /**
     * 插件hook函数
     * @param $location string hook位置
     * @param $data mixed 传入参数
     * @param bool $return 是否需要返回
     * @param null $default 默认返回
     * @param null|string $plugin 具体插件
     * @param bool $isAdmin 是否后台
     * @param bool $only 是否独占,当具体插件不为空时为独占模式
     * @return null |null
     */
    public static function hook($location, $data = null, $return = true, $default = null, $plugin = null, $isAdmin = false, $only = false)
    {


        global $plugin_list;
        $Res = [];
        if ($plugin !== null){
            $pluginList = [$plugin];
            $only=true;
        }
        else
            $pluginList = $plugin_list;
        foreach ($pluginList as $v) {
            $result = null;
            $class = 'app\\plugin\\' . $v . '\\core\\' . ($isAdmin ? 'Admin' : 'Index');
            $func = 'hook' . $location;
            if (method_exists($class, $func)) {

                $obj = new $class();
                if ($data !== null) {
                    $result = $obj->$func($data);
                } else {
                    $result = $obj->$func();
                }
            }
            if ($result !== null) {
                if (is_array($result)) $Res[] =  $result;
            }
            if ($only) return $result;

        }

        if ($return) {
            if ($Res) return $Res;
            return $default != null ? $default : $data;
        } else return null;
    }

    public function manger($location)
    {
        $class = 'app\\plugin\\' . $this->pluginName . '\\core\\Install';
        $func = 'hook' . $location;
        if (method_exists($class, $func)) {
            $obj = new $class();
            $obj->$func();
        }
    }


    public function install($plugin = null)
    {
        $this->resetInfo($plugin);
        if ($this->isPlugin($plugin)) {
            $file = new File();
            $file->del(APP_I . 'plugin/' . $this->pluginName . '/');
            $file->del(APP_VIEW . 'plugin/' . $this->pluginName . '/');
            //复制静态资源
            $re = $file->copyDir($this->pluginDir . '/i/', APP_I . 'plugin/' . $this->pluginName . '/');
            $this->err = '静态资源文件创建失败！';
            if (!$re) return false;
            //复制html资源进行渲染
            $re = $file->copyDir($this->pluginDir . '/view/', APP_VIEW . 'plugin/' . $this->pluginName . '/');
            $this->err = '静态html文件创建失败！';
            if (!$re) return false;
            $this->manger('install');
            return true;
        } else {
            $this->err = '该插件已安装。';
            return false;
        }
    }

    public function uninstall($plugin = null)
    {
        $this->disable($plugin);
        $noAllow=['cn_dreamn_plugin_login_password','cn_dreamn_plugin_picbed_local'];
        if(in_array($this->pluginName,$noAllow)){
            $this->err = '系统核心插件，禁止卸载。';
            return false;
        }
        if ($this->isInstall($plugin)) {
            $file = new File();
            $file->del(APP_I . 'plugin/' . $this->pluginName . '/');
            $file->del(APP_VIEW . 'plugin/' . $this->pluginName . '/');
            $this->delete()->where(['blog_plugin.index like ":plugin_%"', ':plugin' => $this->pluginName])->commit();//删除插件数据
            $this->clearCache();//清理插件缓存
            $this->manger('uninstall');
            return true;
        } else {
            $this->err = '该插件未安装。';
            return false;
        }
    }

    public function isEnable($plugin = null)
    {
        $this->resetInfo($plugin);
        return file_exists($this->pluginDir . '/.used');
    }

    public function getInfo($plugin = null)
    {
        $this->resetInfo($plugin);
        $json = json_decode(file_get_contents(self::DIR . DS . $this->pluginName . DS . 'info.json'));
        if ($json == null) return false;
        if (file_exists(self::DIR . DS . $this->pluginName . DS . '.used')) $json->enable = true;
        else  $json->enable = false;
        if ($this->isInstall($this->pluginName)) $json->install = true;
        else $json->install = false;
        return $json;
    }

    public function enable($plugin = null)
    {
        $this->resetInfo($plugin);
        if (!$this->isEnable()) {
            file_put_contents($this->pluginDir . '/.used', 'enable plugin ' . $this->pluginName);
            $this->manger('enable');
            return true;
        }
        $this->err = '该插件已启用。';
        return false;
    }

    public function disable($plugin = null)
    {
        $this->resetInfo($plugin);
        if ($this->isEnable()) {
            logs('插件禁用' . $this->pluginDir, 'debug', 'plugin');
            unlink($this->pluginDir . '/.used');
            $this->manger('disable');
            return true;
        }
        $this->err = '该插件未被启用。';
        return false;
    }

    public function del($plugin = null)
    {
        $this->resetInfo($plugin);
        if($this->uninstall()){
            $file = new File();
            $file->del($this->pluginDir);
            $this->manger('del');
            return true;
        }
        return false;

    }

    /*
     * 插件缓存
     * */
    public function setCache($fileName, $content)
    {
        $file = new File();
        if (!$file->isName($fileName)) {
            $this->err = '缓存名称非法！';
            return false;
        }
        $dir = APP_CACHE . $this->pluginName . DS;
        if (!is_dir($dir)) {
            mkdir($dir, 666);
        }
        file_put_contents($dir . $fileName . '.cache', $content);
        return true;
    }

    public function getCache($fileName)
    {
        $file = new File();
        if (!$file->isName($fileName)) {
            $this->err = '缓存名称非法！';
            return false;
        }
        $file = APP_CACHE . $this->pluginName . DS . $fileName . '.cache';
        if (file_exists($file)) return file_get_contents($file);
        else {
            $this->err = '缓存文件不存在！';
            return false;
        }
    }

    public function clearCache()
    {
        $dir = APP_CACHE . $this->pluginName . DS;
        if (is_dir($dir)) {
            $file = new File();
            $file->del($dir);
        }
        mkdir($dir, 666);
    }

    public function getErr()
    {
        return $this->err;
    }

    /**
     * 存储插件数据
     * @param string $opt 选项
     * @param string $val 值
     */
    public function setItem($opt, $val)
    {
        $this->insert(self::Duplicate)->keys(['index','value'],['value'])->values([[$this->pluginName . '_' . $opt,$val]])->commit();
    }

    public function getItem($opt, $default = null)
    {
        $result = $this->find(['index' => $this->pluginName . '_' . $opt]);
        return isset($result['value']) ? $result['value'] : $default;//鉴于php7.4效率不及7.3故此不使用7.4特性
    }

    public function css($url)
    {
        return '<link rel="stylesheet" href="' . $this->iDir . $url . '"/>';
    }

    public function js($url)
    {
        $dir=$this->iDir;
        if(substr($url,0,4)=='http'){
            $dir='';
        }
        return '<script src="' . $dir . $url . '"></script>';
    }

    public function display($tpl_name, $arr = null)
    {
        $obj = new Theme();
        return $obj->display('../../../plugin/' . $this->pluginName . '/' . $tpl_name, true, $arr);
    }

    public function getTpl($tpl_name)
    {
        return '../../../plugin/' . $this->pluginName . '/' . $tpl_name;
    }

    public function upload($upload, $name)
    {
        $zip = new ZipArchive();//打开压缩文件，打开成功时返回true
        if ($zip->open($upload)) {
            $path = self::DIR . $name;
            logs($path, 'debug', 'plugin');
            $zip->extractTo($path);//解压到这里
            $zip->close();
            //检查主题完整性
            unlink($upload);
            if ($this->isPlugin($name)) {//完整
                return true;
            } else {
                $file = new File();
                unlink($upload);
                $file->del($path);//删除解压后的文件
                return false;
            }
        } else {
            unlink($upload);
            $this->err = "压缩文件打开失败！";
            return false;
        }
    }

    private function isPlugin($plugin = null)
    {
        logs($plugin, 'debug', 'plugin');
        $this->resetInfo($plugin);
        if (!$this->checkInfo()) return false;
        $this->err = '缺少资源文件夹i';
        if (!is_dir($this->pluginDir . '/i')) return false;
        $this->err = '缺少资源文件夹view';
        if (!is_dir($this->pluginDir . '/view')) return false;
        $this->err = '';
        return true;
    }

    private function checkInfo()
    {
        $this->err = '"' . $this->pluginName . '"不符合命名要求';
        //检查命名规范
        $file = new File();
        if (!$file->isName($this->pluginName)) return false;
        $this->err = '缺少必要的info信息！';
        $info = $this->pluginDir . '/info.json';
        if (!file_exists($info)) return false;
        $info = json_decode(file_get_contents($info), true);
        $infoParam = array('name', 'appName', 'version', 'author', 'description', 'menu', 'date');
        foreach ($infoParam as $v) {
            if (!isset($info[$v])) {
                $this->err = '缺少info的"' . $v . '"字段！';
                return false;
            }
        }
        $this->err = '缺少启动的index.php';
        if (!file_exists($this->pluginDir . '/core/Index.php')) return false;
        return true;
    }

    private function isInstall($plugin = null)
    {
        $this->resetInfo($plugin);
        if (!$this->checkInfo()) return false;
        $this->err = '插件缺失重要文件，建议重新安装！';
        if (!is_dir(APP_I . 'plugin/' . $this->pluginName . '/')) return false;
        $this->err = '插件缺失重要文件，建议重新安装！';
        if (!is_dir(APP_VIEW . 'plugin/' . $this->pluginName . '/')) return false;
        $this->err = '';
        return true;
    }

}