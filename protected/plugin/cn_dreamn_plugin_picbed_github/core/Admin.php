<?php
namespace app\plugin\cn_dreamn_plugin_picbed_qiniu\core;
use app\lib\blog\Plugin;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

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
       
        $accessKey = $this->getItem('accessKey');
        $secretKey = $this->getItem('secretKey');

        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);

        // 要上传的空间
        $bucket = $this->getItem('bucket');                                          //设定的上传空间名

        // 生成上传 Token
        $token = $auth->uploadToken($bucket);

        // 要上传文件的本地路径
        $filePath = $data['tmpFileName'];                                     //上传时候图片在本地的路径

        // 上传到七牛后保存的文件名
        $key = $data['newFileName'];                                         //保存到七牛上面的命名

        // 初始化 UploadManager 对象并进行文件的上传
        $uploadMgr = new UploadManager();

        // 调用 UploadManager 的 putFile 方法进行文件的上传
        try {
            list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        } catch (\Exception $e) {
            return false;
        }
        if ($err !== null) {
           return false;                                                 //返回结果根据需要处理
        } else {
            $domain=$this->getItem('domain');
            return $domain.'/'.$ret['key'];
        }
    }

    public function hookDo($data){
        if(!isset($data['accessKey'])||!isset($data['secretKey'])||!isset($data['bucket'])||!isset($data['domain']))
            return [json_encode(['code'=>-1,'msg'=>'保存失败'])];
        $this->setItem('accessKey',$data['accessKey']);
        $this->setItem('secretKey',$data['secretKey']);
        $this->setItem('bucket',$data['bucket']);
        $this->setItem('domain',$data['domain']);
        return [json_encode(['code'=>0,'msg'=>'保存成功'])];
    }

    public function hookinclude_pic(){
        $tpl=$this->display('set',['accessKey'=>$this->getItem('accessKey'),'secretKey'=>$this->getItem('secretKey'),'bucket'=>$this->getItem('bucket'),'domain'=>$this->getItem('domain')]);
        $return=['title'=>"七牛云配置",'tpl'=>$tpl,'js'=>$this->display('setScript')];
        return $return;
    }
}//必须继承Plugin.