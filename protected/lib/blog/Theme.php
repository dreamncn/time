<?php
/**
 * Name         :Theme.php
 * Author       :dreamn
 * Date         :2020/2/11 18:06
 * Description  :主题输出控制器
 */
namespace app\lib\blog;
use app\includes\File;
use app\lib\speed\mvc\Controller;
use app\model\Config;
use ZipArchive;

/**
 * Class Theme
 * @package lib\blog
 * 主题管理工具
 */
class Theme extends Controller{
    const DIR=APP_DIR.  DS.'protected' .DS."theme" .DS;
    protected $themeName='cn_dreamn_theme_even';//默认主题名
    protected $sidebar=array();
    private $err="";


    /**
     * Theme constructor.
     */
    public function __construct()
    {
        //覆盖重写主题目录
        $config=new Config();
        $this->themeName = $config->getData('theme');
        //获得当前使用的主题
        if(!$this->isInstall()){
            $config->setData('theme','cn_dreamn_theme_even');
            $this->themeName='cn_dreamn_theme_even';
        }
        $json=json_decode(file_get_contents(self::DIR.$this->themeName.DS.'info.json'));
        $this->sidebar=$json->sidebar;
        //修正主题错误
        GLOBAL $__module;
        $__module_really=str_replace('theme'.DS.$this->themeName.DS,'',$__module);
        $__module='theme'.DS.$this->themeName.DS.$__module_really;
        //修正全局错误文件
        $GLOBALS['error']=str_replace('theme'.DS.$this->themeName.DS,'',$GLOBALS['error']);
        $GLOBALS['error']='theme'.DS.$this->themeName.DS.$GLOBALS['error'];
        parent::__construct();
    }

    /**
     * 获取主题列表
     * @return array
     */
    public  function getThemes(){
        $p = scandir(self::DIR);
        $arr=[];
        foreach($p as $val){
            if($val !="." && $val !=".."){
                if($this->isTheme($val)){
                    $tmp=json_decode(file_get_contents(self::DIR.DS.$val.DS."info.json"),true);
                    $tmp['install'] = $this->isInstall($val);
                    $tmp['cover']='data:image/png;base64,'.base64_encode(file_get_contents(self::DIR.DS.$val.DS.'preview.png'));
                    $tmp['used']=$tmp['appName']===$this->themeName;
                    $tmp['info']=$this->err;
                    $arr[]=$tmp;
                }

            }
        }
        return $arr;
    }

    /**
     * 主题hook函数，用于主题创作
     * @param $location
     * @param null $data
     * @param bool $isAdmin
     * @return null |null
     */
    public function hook($location,$data=null,$isAdmin=false){
        $class='app\\theme\\'.$this->themeName.'\\core\\'.($isAdmin?'Admin':'Index');
        $func=$location;
        if(method_exists($class, $func)){
            $obj=new $class();
            if($data!==null)return $obj->$func($data);
            else return $obj->$func();
        }
        return null;
    }

    /**
     * 获得错误信息
     * @return string
     */
    public function getErr(){
        return $this->err;
    }

    /**
     * 继承模板输出，进行补充
     * @param null $tpl_name
     * @param bool $return
     * @param null $array
     * @return false|string
     */
    public function display($tpl_name, $return = false,$array=null)
    {
        //所有基于底层控制模板的输出都应该像这样
        if($array!==null)
        foreach ($array as $opt=>$val){
            $this->$opt=$val;
        }
        return parent::display($tpl_name, $return);
    }

    public function getTheme(){
        return $this->themeName;
    }

    /**
     * 检查主题是否安装
     * @param null $theme
     * @return bool
     */
    public function isInstall($theme=null)
    {
        if($theme==null)
            $theme=$this->themeName;
        if(!$this->checkInfo($theme))return false;
        if(!is_dir(APP_I.'theme'.DS.$theme))return false;
        if(!is_dir(APP_VIEW.'theme'.DS.$theme))return false;
        $this->err='';
        return true;
    }

    /**
     * 检查主题信息
     * @param null $theme
     * @return bool
     */
    private function checkInfo($theme=null){
        if($theme==null)
            $theme=$this->themeName;
        
        $this->err='"'.$theme.'"不符合命名要求';
        //检查命名规范
        $file=new File();
        if(!$file->isName($theme))return false;
        $path=self::DIR.$theme;
        $this->err="目标主题不存在";
        if(!is_dir($path))return false;
        $this->err="缺失主题信息文件";
        if(!is_file($path.DS."info.json"))return false;

        $info=json_decode(file_get_contents($path.DS."info.json"),true);
        $infoParam=array('name','appName','version','author','description','date','sidebar');
        foreach ($infoParam as $v){
            if(!isset($info[$v])){
                $this->err='缺少info的"'.$v.'"字段！';
                return false;
            }
        }
        if($theme==null)
            $this->sidebar=$info['sidebar'];
        $this->err='';
        return true;
    }

    /**
     * 检查是否为主题
     * @param null $theme
     * @return bool
     */
    public function isTheme($theme=null){
        if($theme==null)
            $theme=$this->themeName;
        if(!$this->checkInfo($theme))return false;
        $this->err="缺失主题样式文件";
        if(!is_dir(self::DIR.$theme.DS.'i'))return false;
        $this->err="缺失主题核心文件";
        if(!is_dir(self::DIR.$theme.DS.'view'))return false;
        $this->err="缺失主题预览文件";
        if(!is_file(self::DIR.$theme.DS.'preview.png'))return false;
        $this->err='';
        return true;
    }
    public function upload($upload,$name){

        $zip = new ZipArchive();//打开压缩文件，打开成功时返回true
        if ($zip->open($upload)) {

            $path=self::DIR.$name;
            logs($path,'debug','theme');
            $zip->extractTo($path);//解压到这里
            $zip->close();
            //检查主题完整性
            unlink($upload);
            if($this->isTheme($name)){//完整

                return true;
            }else{
                $file=new File();
                $file->del($path);//删除解压后的文件
                unlink($upload);
                return false;
            }
        } else {
            unlink($upload);
            $this->err="压缩文件打开失败！";
            return false;
        }
    }
    public function install($name){
        $path=self::DIR.$name.DS;
       if(!$this->isInstall($name)&&$this->err==''){
           $file=new File();
           $i= APP_I.'theme'.DS.$name.DS;
           $view=APP_VIEW.'theme'.DS.$name.DS;
           logs($path,'debug','theme');
           logs($i,'debug','theme');
           logs($view,'debug','theme');
           $file->copyDir($path.'i',$i);
           $file->copyDir($path.'view',$view);
           return true;
       }elseif($this->err==''){
           $this->err='该主题已经安装';
       }
       return false;

    }//模板安装程序
    public function uninstall($name){
        if($name==="cn_dreamn_theme_even"){
            $this->err="默认模板禁止卸载！";
            return false;
        }
       if(!$this->isTheme($name)){
           $this->err="这不是一个主题！";
           return false;
       }
        $i= APP_I.'theme'.DS.$name.DS;
        $view=APP_VIEW.'theme'.DS.$name.DS;
        $file=new File();
        $re1=$file->del($i);
        $re2=$file->del($view);
        if($re1===true&&$re2===true) return true;
        else{
            $this->err=is_string($re1)?$re1:"";
            $this->err.=is_string($re2)?$re2:"";
            return false;
        }

    }//模板卸载程序

    public function del($name){
        if($name==="cn_dreamn_theme_even"){
            $this->err="默认模板禁止删除！";
            return false;
        }
        $this->uninstall($name);

        $path=self::DIR.$name;
        $file=new File();

        $file->del($path);

        return true;

    }//模板卸载程序

}