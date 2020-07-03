<?php
namespace app\plugin\cn_dreamn_plugin_music\core;
use app\includes\Web;

/**
 * Created by dreamn.
 * Date: 2019-09-08
 * Time: 22:38
 */

class music{
    private $data=null;
    private   $site="https://music.liuzhijin.cn/";
    public function analysis($urls){//对传入的url列表进行分析，这个分析要丢到后台去
        $datas["name"]="";
        $datas["id"]="";$u="";
        $urls=explode('|',$urls);
        foreach($urls as $url){

            if(strpos($url,"https://y.qq.com/n/yqq/")===0){
                $data=$this->getQQ($url);
                // var_dump($data);
            }elseif(strpos($url,"https://music.163.com/#/playlist")===0){
                $data=$this->getWy($url);
                //var_dump($data);
            }else{
                $data["name"]="";
                $data["id"]="";
            }
            if(!$data)return false;
            $datas["name"].=$data["name"];
            $datas["id"].=$data["id"];
            $u.=$url."|";
        }
        $this->data["id"]=$datas["id"];
        $this->data["name"]=$datas["name"];
        $this->data["urls"]=$u;

        return true;
    }

    public function musicDownload($songUrl)
    {

        $url = $this->site."?url=" . urlencode($songUrl);
        $web = new Web();


        $data = array(
            "input" => $songUrl,
            "filter" => "url",
            "type" => "_",
            "page" => "1"
        );
        $header = [
            'X-Requested-With:XMLHttpRequest',
            'Referer:' . $url
        ];
        $res = $web->post($this->site, $data, $header);

        $json = json_decode($res);


        if ($json->code === 200) {

            $data = $json->data[0];
            return $data;
        } else {
            return false;
        }
    }//音乐下载

    private function getQQ($url){//获取qq的专辑页面的id
        $isMatched = preg_match_all('/playlist\/(.*?).html/', $url, $matches);

        if($isMatched===1){
            $sid=$matches[1][0];//专辑id
            $url1="https://c.y.qq.com/qzone/fcg-bin/fcg_ucc_getcdinfo_byids_cp.fcg?type=1&json=1&utf8=1&onlysong=0&new_format=1&disstid=$sid&loginUin=0&hostUin=0&format=json&inCharset=utf8&outCharset=utf-8&notice=0&platform=yqq&needNewCode=0";
            $header=array(
                "Referer:".$url
            );
            $web= new Web();
            $result=json_decode($web->get($url1,$header));
            $data=$result->cdlist[0]->songlist;
            //var_dump($data);
            $i="";$s="";$n=0;
            foreach ($data as $value){
                $i.=$value->mid."qq|";
                $s.=$value->name."|";
                $n++;
            }
            $arr["id"]=$i;
            $arr["name"]=$s;
            return $arr;
        }else return false;
    }//获得qq专辑页的id，成功返回id，失败返回false

    private function getWy($url){

        $web= new Web();
        $result=$web->get($url);

        $isMatched = preg_match_all('/window.REDUX_STATE = {(.*?)};/', $result, $matches);
        $i="";$s="";
         if($isMatched){
             $json=json_decode('{'.$matches[1][0].'}',true);
             
             foreach ($json['Playlist']['data'] as $val){
                 $i.= $val['id']."wy|";
                 $s.=$val['songName']."|";
             }
         }
         
        $arr["id"]=$i;
        $arr["name"]=$s;
       
        return $arr;
    }//获得网易专辑页的id，成功返回id，失败返回false

    public function getData(){
        return $this->data;
    }
    public function imgColor($url) {
        $imageInfo = getimagesize($url);

        $imgType = strtolower(substr(image_type_to_extension($imageInfo[2]) , 1));

        $imageFun = 'imagecreatefrom' . ($imgType == 'jpg' ? 'jpeg' : $imgType);
        $i = $imageFun($url);

        $rColorNum = $gColorNum = $bColorNum = $total = 0;
        for ($x = 0; $x < imagesx($i); $x++) {
            for ($y = 0; $y < imagesy($i); $y++) {
                $rgb = imagecolorat($i, $x, $y);

                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $rColorNum+= $r;
                $gColorNum+= $g;
                $bColorNum+= $b;
                $total++;
            }
        }
        $rgb = array();
        $rgb['r'] = round($rColorNum / $total);
        $rgb['g'] = round($gColorNum / $total);
        $rgb['b'] = round($bColorNum / $total);
        return $rgb;
    }//为图片匹配主题
}