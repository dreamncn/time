<?php
namespace app\plugin\cn_dreamn_plugin_tencent\core;
use app\includes\Web;
use app\lib\blog\Plugin;

/**
 * Created by dreamn.
 * Date: 2019-09-08
 * Time: 19:24
 */
/*
 * 绝对绝对不允许自行输出任何信息，所有信息的输出请直接调用内置函数
 * */
/*默认类名，不允许修改*/
class Index extends Plugin{
    /**
     * Index constructor.
     */
    public function __construct()
    {
        parent::__construct("cn_dreamn_plugin_tencent");
    }

    public function hookVerity($data){
        if(!isset($data['verity'])||!isset($data['randstr']))return false;

        $param["Nonce"] = rand();
        $param["Timestamp"] = time();
        $param["Region"] = "ap-beijing";
        $param["SecretId"] = $this->getItem('secret_id','');
        $param["Action"] = "DescribeCaptchaResult";
        $param["Version"] = "2019-07-22";
        $param["CaptchaType"] = "9";
        $param["Ticket"] = $data['verity'];
        $param["UserIp"] = $_SERVER['REMOTE_ADDR'];
        $param["Randstr"] = $data['randstr'];
        $param["CaptchaAppId"] = $this->getItem('appid','');
        $param["AppSecretKey"] = $this->getItem('app_secret_key','');


        ksort($param);

        $signStr = "GETcaptcha.tencentcloudapi.com/?";
        $k="";
        foreach ( $param as $key => $value ) {
            $k = $k . $key . "=" . $value . "&";
        }

        $signStr = substr($signStr.$k, 0, -1);

        $signature = base64_encode(hash_hmac("sha1", $signStr,$this->getItem('secret_key',''), true));

        $url='https://captcha.tencentcloudapi.com/?'.$k."Signature=".urlencode($signature);

        $web = new Web();
        $result=$web->get($url);
        
        $json = json_decode($result);
        
        if ($json->retcode===0&&$json->Response->CaptchaCode === 1)return [true];
        else return [false];
    }
    public function hookArticlePassword($data){
        $arr['appid']=$this->getItem('appid','');
        $arr['url']=url('article','get',array('alian'=>$data['alian']));
        $res=$this->display('article_password',$arr);
        $res2=$this->display('article_script',$arr);
        return array('tpl'=>$res,'script'=>$res2);

    }
    public function hookCommentCode($data){
        $arr['appid']=$this->getItem('appid','');
        $res=$this->display('comment_code',$arr);
        $res2=$this->display('comment_script',$arr);
        return array('tpl'=>$res,'script'=>$res2);
    }
    public function hookLayoutFooter($data=null){
        //$data[]=$this->js("https://ssl.captcha.qq.com/TCaptcha.js");
        return $data;
    }
    public function hookDo($arg=[]){
        $m = new music();

        $data["name"] =$this->getItem("songnameList","");
        $data["id"]=$this->getItem("songIdList","");

        switch ($arg["do"]) {
            case "get":

                return ['var wenkmList=[{song_album:"' . $this->getItem("albumname","追梦博客") . '",song_album1:"' . $this->getItem("albumname1","音乐精选") . '",song_file:"/",song_name:"' . $data["name"] . '".split("|"),song_id:"' . $data["id"] . '".split("|")}];'];
                break;

            case 'parse':

                $type = isset($arg['type']) ? $arg['type'] : exit;

                $id = isset($arg['id']) ? $arg['id'] : null;

                $xy = isset($arg['callback']) ? $arg['callback'] : null;

                if ($type === 'wy') {
                    $data = $m->musicDownload("https://music.163.com/#/song?id=" . $arg["id"]);

                } elseif ($type === 'qq') {
                    $data = $m->musicDownload("https://y.qq.com/n/yqq/song/" . $arg["id"] . ".html");

                }
                if (!$data) {
                    break;
                }

                if($data->lrc==="")
                    $data->lrc="[00:00:00]暂无歌词，请您欣赏";
                $this->setCache($arg["id"],$data->lrc);
                return [$xy . '({"location":"' . $data->url . '","album_cover":"' . $data->pic. '","album_name":"","artist_name":"' . $data->author . '","song_name":"' . $data->title . '","song_id":"' . $id . '"}) '];

                break;
            case 'lyric':
                $data = $this->getCache($arg["id"]);
                return ["var cont = '" . str_replace("\n", "", $data) . "';"];
                break;
            case 'color':
                $color = $m->imgColor($arg["url"]);
                $font_color = "{$color['r']},{$color['g']},{$color['b']}";
                $data = array(
                    "font_color" => $font_color
                );
                return ["var cont = '" . $data["font_color"] . "';"];
                break;
        }
        return [""];
    }//前端提交数据接收函数，前端所有提交都走这个路线

}//必须继承PluginController.