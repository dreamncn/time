<?php

namespace app\includes;
use app\lib\blog\Plugin;
use app\model\Config;
use app\model\Upload;
use PHPMailer\PHPMailer\Exception;

/**
 *
 * file: fileupload.class.php 文件上传类FileUpload
 *
 * 本类的实例对象用于处理上传文件，可以上传一个文件，也可同时处理多个文件上传
 */
class FileUpload
{
    private $path = '';          //上传文件保存的路径
    private $allowtype = array('jpg', 'gif', 'png', 'jpeg'); //设置限制上传文件的类型
    private $maxsize = 3145728;           //限制文件上传大小（字节），默认最大不超过3M
    private $israndname = true;           //设置是否随机重命名文件， false不随机
    private $picBed = null;            //使用其他图床
    private $originName;              //源文件名
    private $tmpFileName;              //临时文件名
    private $fileType;               //文件类型(文件后缀)
    private $fileSize;               //文件大小
    private $newFileName;              //新文件名
    private $errorNum = 0;             //错误号
    private $errorMess = "";             //错误报告消息
    private $upPath = "";           //文件上传后的路径
    private $model='';//模块名
    public function __construct($model)
    {
        $this->model=$model;
        $this->path=APP_UPLOAD.$model;
        switch ($model){
            case 'article':
                $c = new Config();
                $picbed= $c->getData("pic_bed");
                if(class_exists('app\\plugin\\'.$picbed.'\\core\\Index'))
                    $this->picBed = $picbed;
                $this->path=APP_UPLOAD_ARTICLE;break;
            case 'setting':$this->path=APP_UPLOAD_SETTING;break;
            case 'plugin':$this->path=APP_UPLOAD_PLUGIN;break;
            case 'theme':$this->path=APP_UPLOAD_THEME;break;
            default:$this->path=APP_UPLOAD.$model;
        }


    }

    /**
     * 用于设置成员属性（$path, $allowtype,$maxsize, $israndname）
     * 可以通过连贯操作一次设置多个属性值
     * @param string $key 成员属性名(不区分大小写)
     * @param mixed $val 为成员属性设置的值
     * @return  object     返回自己对象$this，可以用于连贯操作
     */
    function set($key, $val)
    {

        $key = strtolower($key);

        if (array_key_exists($key, get_class_vars(get_class($this)))) {

            $this->setOption($key, $val);

        }

        return $this;

    }

    /**
     * 调用该方法上传文件
     * @param $fileField
     * @return bool        如果上传成功返回数true
     */
    function upload($fileField)
    {
        $return = true;

        /* 检查文件路径是滞合法 */
        if (!$this->checkFilePath()) {
            $this->errorMess = $this->getError();
            return false;
        }
        /* 将文件上传的信息取出赋给变量 */
        $name = $_FILES[$fileField]['name'];
        $tmp_name = $_FILES[$fileField]['tmp_name'];
        $size = $_FILES[$fileField]['size'];
        $error = $_FILES[$fileField]['error'];
        /* 设置文件信息 */
        if ($this->setFiles($name, $tmp_name, $size, $error)) {
            /* 上传之前先检查一下大小和类型 */
            if ($this->checkFileSize() && $this->checkFileType()) {
                /* 为上传文件设置新文件名 */
                $this->setNewFileName();
                /* 上传文件  返回0为成功， 小于0都为错误 */
              $result = $this->copyFile();
                if ($result) {
                    return true;
                } else {
                    $return = false;
                }
            } else {
                $return = false;
            }
        } else {
            $return = false;
        }
        //如果$return为false, 则出错，将错误信息保存在属性errorMess中
        if (!$return)
            $this->errorMess = $this->getError();
        return $return;
    }



    /**
     * 获取上传后的文件名称
     * @param void   没有参数
     * @return string 上传后，新文件的名称， 如果是多文件上传返回数组
     */
    public function getFileName()
    {
        return $this->newFileName;
    }

    /**
     * 获取上传后的文件路径
     * @param void   没有参数
     * @return string 上传后，新文件的名称， 如果是多文件上传返回数组
     */

    public function getFileUrl()
    {
        /**
         * 如果是本地直接返回的是本地访问地址
         */
        return str_replace(APP_DIR,'',$this->upPath);

    }

    /**
     * 获取文件物理路径
     * @return string|null 返回物理地址
     */
    public function getFilePath(){
        /**
         * 如果是本地直接返回的是物理地址
         */
        if(is_file($this->upPath)) return $this->upPath;
        else return null;
    }


    /**
     * 上传失败后，调用该方法则返回，上传出错信息
     * @param void   没有参数
     * @return string  返回上传文件出错的信息报告，如果是多文件上传返回数组
     */

    public function getErrorMsg()
    {
        return $this->errorMess;
    }


    /**
     * 获取原始文件名
     * @return mixed
     */
    public function getOriginName()
    {
        $arr=explode('.',$this->originName);
        if(sizeof($arr)!==2)
            return  $this->originName;
        return $arr[count($arr) - 2];
    }

    /* 设置上传出错信息 */
    private function getError()
    {
        $str = "上传文件<font color='red'>{$this->originName}</font>时出错 : ";
        switch ($this->errorNum) {
            case 4:
                $str .= "没有文件被上传";
                break;
            case 3:
                $str .= "文件只有部分被上传";
                break;
            case 2:
                $str .= "上传文件的大小超过了HTML表单中MAX_FILE_SIZE选项指定的值";
                break;
            case 1:
                $str .= "上传的文件超过了php.ini中upload_max_filesize选项限制的值";
                break;
            case -1:
                $str .= "未允许类型";
                break;
            case -2:
                $str .= "文件过大,上传的文件不能超过{$this->maxsize}个字节";
                break;
            case -3:
                $str .= "上传失败";
                break;
            case -4:
                $str .= "建立存放上传文件目录失败，请重新指定上传目录";
                break;
            case -5:
                $str .= "必须指定上传文件的路径";
                break;
            case -6:
                $str .= "第三方图床上传错误，请检查设置！";
                break;
            default:
                $str .= "未知错误";
        }
        return $str . '<br>';
    }


    /* 设置和$_FILES有关的内容 */
    private function setFiles($name = "", $tmp_name = "", $size = 0, $error = 0)
    {
        $this->setOption('errorNum', $error);
        if ($error)
            return false;
        $this->setOption('originName', $name);
        $this->setOption('tmpFileName', $tmp_name);
        $aryStr = explode(".", $name);
        $this->setOption('fileType', strtolower($aryStr[count($aryStr) - 1]));
        $this->setOption('fileSize', $size);
        return true;

    }


    /* 为单个成员属性设置值 */

    private function setOption($key, $val)
    {
        $this->$key = $val;
    }


    /* 设置上传后的文件名称 */

    private function setNewFileName()
    {
        if ($this->israndname) {
            $this->setOption('newFileName', $this->proRandName());
        } else {
            $this->setOption('newFileName', $this->originName);
        }

    }


    /* 检查上传的文件是否是合法的类型 */

    private function checkFileType()
    {

        if (in_array(strtolower($this->fileType), $this->allowtype)) {
            return true;
        } else {
            $this->setOption('errorNum', -1);
            return false;
        }

    }


    /* 检查上传的文件是否是允许的大小 */

    private function checkFileSize()
    {
        if ($this->fileSize > $this->maxsize) {
            $this->setOption('errorNum', -2);
            return false;
        } else {
            return true;
        }

    }


    /* 检查是否有存放上传文件的目录 */

    private function checkFilePath()
    {

        if (empty($this->path)) {
            $this->setOption('errorNum', -5);
            return false;
        }

        if (!file_exists($this->path) || !is_writable($this->path)) {
            if (!@mkdir($this->path, 0755)) {
                $this->setOption('errorNum', -4);
                return false;
            }
        }
        return true;

    }


    /* 设置随机文件名 */

    private function proRandName()
    {
        $fileName = date('YmdHis') . "_" . rand(100, 999);
        return $fileName . '.' . $this->fileType;
    }



    /* 复制上传文件到指定的位置 */

    private function copyFile()
    {

        if (!$this->errorNum) {
            $path = rtrim($this->path, '/') . '/';
            $path .= $this->newFileName;
            $this->upPath = $path;
            if($this->picBed!==null){//article才需要
                try{
                    $this->upPath = Plugin::hook('Upload',array("tmpFileName"=>$this->tmpFileName, "newFileName"=>$this->newFileName, "fileType"=>$this->fileType,"upPath"=>$this->upPath),true,[],$this->picBed,true,true);
                    if ($this->upPath) {
                        $upload=new Upload();
                        $upload->add($this->getOriginName(),$this->getFileUrl());
                        return true;
                    } else {
                        $this->setOption('errorNum', -6);
                        return false;
                    }
                }catch(\Exception $e){
                    $this->setOption('errorNum', -6);
                    return false;
                }
            }else{
                if ((@move_uploaded_file($this->tmpFileName, $this->upPath))||@copy($this->tmpFileName, $this->upPath)) {
/*
                    $upload=new Upload();
                    $upload->add($this->getOriginName(),$this->getFilePath());*/
                    return true;
                } else{
                    $this->setOption('errorNum', -3);
                    return false;
                }
            }
        }else {
            return false;
        }
    }
}