<?php
namespace app\plugin\cn_dreamn_plugin_music\core;
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
        parent::__construct("cn_dreamn_plugin_music");
    }

    /**
     * 前端变量赋值
     * @param null $data
     * @return array
     */
    public function hookLayoutVar($data=null){
        $data['Welcome']=$this->getItem("tips","欢迎来到Dreamn博客");
        return $data;
    }

    /**
     * 前端css处添加（位于index\layout.html文件）
     * @return false|string
     */

    public function hookLayoutHead(){
        $arr['Welcome']=$this->getItem("tips","欢迎来到Dreamn博客");//读取存储的欢迎信息
        //取得渲染结果

        return $this->display("script",$arr);
    }

    /**
     * @return false|string
     */
    public function hookLayoutFooter(){
        //取得渲染结果
        return $this->display("index");
        //使用模板渲染
    }//直接作用于layout模板的Footer部分

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
                return [$xy . '({"location":"' . str_replace('https','http',$data->url) . '","album_cover":"' . $data->pic. '","album_name":"","artist_name":"' . $data->author . '","song_name":"' . $data->title . '","song_id":"' . $id . '"}) '];

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