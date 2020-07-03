<?php
namespace app\plugin\cn_dreamn_plugin_picbed_qiniu\core;
use app\lib\blog\Plugin;
require_once 'sdk/autoload.php';
   /**
 * Created by dreamn.
 * Date: 2019-09-08
 * Time: 19:24
 */
/*
 * 绝对绝对不允许自行输出任何信息，所有信息的输出请直接调用内置函数
 * */
/*默认类名，不允许修改*/
class Admin extends Plugin{
    /**
     * Index constructor.
     */
    public function __construct()
    {
        parent::__construct("cn_dreamn_plugin_picbed_qiniu");
    }
    public function hookPicList($arg){
       $arg=['title'=>'七牛云图床','picbed'=>'cn_dreamn_plugin_picbed_qiniu'];
       return $arg;
    }

    public function hookUpload($data){
        dump($data,true);
        if ((@move_uploaded_file($data['tmpFileName'], $data["upPath"]))||@copy($data['tmpFileName'], $data["upPath"])) {
             return $data["upPath"];
        } else{
             return false;
        }
    }
}//必须继承Plugin.