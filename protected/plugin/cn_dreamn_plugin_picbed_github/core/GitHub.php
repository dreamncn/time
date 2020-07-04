<?php
namespace app\includes;
use app\model\Config;

/**
 * Created by dreamn.
 * Date: 2019-11-16
 * Time: 22:14
 * github 图床，已经做了cdn加速
 */
class GitHub {

    private $ownerRepo;
    private $key;
    private $path;
    private $name;
    private $data;
    public function __construct()
    {
        $c=new Config();
        $this->ownerRepo=$c->getData('git_owner_repo');
        $this->key=$c->getData('git_key');
    }

    /**
     * github上传
     * @return bool|string
     */
    public function upload($path,$name,$ext){
        $this->path=$path;
        $this->data=base64_encode(file_get_contents($path));
        $this->name=md5($name.time()).'.'.$ext;
        $url="https://api.github.com/repos/$this->ownerRepo/contents/$this->name";
        $json=array(
            "message"=>"Upload by Dreamn",
            "committer"=>array(
                "name"=>"Dreamn",
                "email"=> "dreamn@github.com"
            ),
            "content"=> $this->data
        );
       $w=new Web();
       $result=$w->put($url,$json,array("Authorization:token ".$this->key,'Content-type:application/json'));

       if(isset($result->content)){
           return "https://cdn.jsdelivr.net/gh/".$this->ownerRepo.'/'.$this->name;

       }else{
           return false;
       }
    }
}