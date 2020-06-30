<?php
namespace app\Model;
use app\lib\speed\mvc\Model;

/**
 * Name         :Upload.php
 * Author       :dreamn
 * Date         :2020/2/16 15:04
 * Description  :上传文件控制器，每个上传的文件都做记录
 */
class Upload extends Model{
    private $type='';//上传的文件类型
    public function __construct($type)
    {
        $this->type=$type;
        parent::__construct('blog_upload');
    }

    /**
     * 添加上传信息
     * @param $name
     * @param $path
     * @param string $passwd
     * @return mixed
     */
    public function add($name,$path,$passwd=''){
        $arg['file_path']=$path;
        $arg['file_title']=$name;
        $arg['file_passwd']=$passwd;
        $arg['file_bind_id']='tmp';
        $arg['file_type']=$this->type;
        return $this->insert($arg);
    }

    /**
     * 根据ID删除绑定的信息
     * @param $id
     * @return mixed|null
     */
    public function delByBindID($id){

        $re=$this->select()->where(['file_bind_id'=>$id,'file_type'=>$this->type])->commit();
        if(!empty($re)){
            foreach ($re as $val){
                if(is_file($val['file_path']))unlink($val['file_path']);
            }
            return $this->delete()->where(['file_bind_id'=>$id,'file_type'=>$this->type])->commit();
        }
        return null;
    }

    /**
     * 设置绑定的信息
     * @param $id
     * @return mixed
     */
    public function setBind($id){
        return $this->update()->where(['file_bind_id'=>'tmp'])->set(['file_bind_id'=>$id])->commit();
    }

    /**
     * 清理临时信息
     * @return mixed
     */
    public function clearTmp(){
        return $this->delete()->where(['file_bind_id'=>'tmp'])->commit();
    }

    /**
     * 信息更新
     * @param $id string 上传的id
     * @param $opt string 更新选项
     * @param $val string 更新值
     * @return mixed
     */
    public function setOpt($id,$opt,$val){
        return  $this->update()->set([$opt=>$val])->where(['id'=>$id])->commit();
    }
}