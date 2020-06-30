<?php
/**
 * Name         :File.php
 * Author       :dreamn
 * Date         :2020/2/12 22:47
 * Description  :文件文件夹io操作类
 */
namespace app\includes;
/**
 * Class File
 * @package includes
 */
class File {
    /**
     * 文件夹删除
     * @param $dirname
     * @return bool|string
     */
    public function del($dirname)
    {
        if (!is_dir($dirname)) {
            return " $dirname is not a dir!";
        }
        $handle = opendir($dirname); //打开目录
        while (($file = readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') {
                //排除"."和"."
                $dir = $dirname .'/' . $file;
                is_dir($dir) ? $this->del($dir) : unlink($dir);
            }
        }
        closedir($handle);
        $result = rmdir($dirname) ? true : false;
        return $result;
    }
    /**
     * 文件夹文件拷贝
     *
     * @param string $src 来源文件夹
     * @param string $dst 目的地文件夹
     * @return bool
     */
    public function copyDir($src = '', $dst = '')
    {
        if (empty($src) || empty($dst))
        {
            return false;
        }

        $dir = opendir($src);
        $this->mkDir($dst);
        while (false !== ($file = readdir($dir)))
        {
            if (($file != '.') && ($file != '..'))
            {
                if (is_dir($src . '/' . $file))
                {
                    $this->copyDir($src . '/' . $file, $dst . '/' . $file);
                }
                else
                {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);

        return true;
    }

    /**
     * 创建文件夹
     *
     * @param string $path 文件夹路径
     * @param int $mode 访问权限
     * @param bool $recursive 是否递归创建
     * @return bool
     */
    public function mkDir($path = '', $mode = 0777, $recursive = true)
    {
        clearstatcache();
        if (!is_dir($path))
        {
            mkdir($path, $mode, $recursive);
            return chmod($path, $mode);
        }

        return true;
    }

    /**
     * 获得文件夹的MD5值，做校验用
     * @param $dir
     * @return string
     */
    public function getDirMd5($dir){
        $md5='';
        foreach (glob($dir . '\*') as $file_path) {
            //file change GBK to utf-8
            $file_path = iconv("GBK","utf-8//IGNORE",$file_path);
            $file_name =  substr($file_path, strlen(dirname($file_path)) + 1);
            if($file_name == 'info.json') continue;
            else $md5 = $md5 . md5_file($file_path);
        }
        return md5($md5);
    }

    /**
     * 判断是否符合命名规则
     * @param $name
     * @return bool
     */
    public function isName($name){
        $isMatched = preg_match_all('/^[0-9a-zA-Z_]+$/', $name);
        if($isMatched)return true;
        else {

            return false;
        }
    }
}
