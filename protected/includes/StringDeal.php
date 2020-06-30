<?php
/**
 * Translate.php
 * User: Dreamn
 * Date: 2020/1/17 17:26
 * Description:
 */
namespace app\includes;
/**
 * Class StringDeal
 * @package app\includes
 */
class StringDeal{
    /**
     * 调用有道翻译进行翻译
     * @param $txt
     * @return mixed
     */
    public function getTranslate($txt){
        //http://fanyi.youdao.com/translate?&doctype=json&type=AUTO&i=
        $web=new Web();
        $result=$web->get('http://fanyi.youdao.com/translate?&doctype=json&type=AUTO&i='.urlencode($txt));
        $json=json_decode($result);
        return $json->translateResult[0][0];
    }

    /**
     * html转文本
     * @param $content
     * @param $count
     * @return string
     */
    public function getDescriptionFromContent($content, $count)
    {
        $content = preg_replace("@<script(.*?)</script>@is", "", $content);
        $content = preg_replace("@<iframe(.*?)</iframe>@is", "", $content);
        $content = preg_replace("@<style(.*?)</style>@is", "", $content);
        $content = preg_replace("@<(.*?)>@is", "", $content);
        $content = str_replace(PHP_EOL, '', $content);
        $space = array(" ", "　", "  ", " ", " ");
        $go_away = array("", "", "", "", "");
        $content = str_replace($space, $go_away, $content);
        $res = mb_substr($content, 0, $count, 'UTF-8');
        if (mb_strlen($content, 'UTF-8') > $count) {
            $res = $res . "...";
        }
        return $res;
    }

    /**
     * 过滤别名翻译出来的奇怪字符串
     * @param $title
     * @return string|string[]|null
     */
    public function getAlians($title){
        $result=$this->getTranslate($title);
        $res=ucwords($result->tgt);
        $res=urlencode($res);//将关键字编码
        $res=preg_replace("/(%7E|%60|%21|%40|%23|%24|%25|%5E|%26|%27|%2A|%28|%29|%2B|%7C|%5C|%3D|\-|_|%5B|%5D|%7D|%7B|%3B|%22|%3A|%3F|%3E|%3C|%2C|\.|%2F|%A3%BF|%A1%B7|%A1%B6|%A1%A2|%A1%A3|%A3%AC|%7D|%A1%B0|%A3%BA|%A3%BB|%A1%AE|%A1%AF|%A1%B1|%A3%FC|%A3%BD|%A1%AA|%A3%A9|%A3%A8|%A1%AD|%A3%A4|%A1%A4|%A3%A1|%E3%80%82|%EF%BC%81|%EF%BC%8C|%EF%BC%9B|%EF%BC%9F|%EF%BC%9A|%E3%80%81|%E2%80%A6%E2%80%A6|%E2%80%9D|%E2%80%9C|%E2%80%98|%E2%80%99)+/",'-',$res);
        $res=urldecode($res);//将过滤后的关键字解码
        $res=str_replace(' ','-',$res);
        $res=str_replace('--','-',$res);
        return $res;
    }
    //代码来源：Monxin ./config/functions.php
    public function isUrl($v){
        $pattern="#(http|https)://(.*\.)?.*\..*#i";
        if(preg_match($pattern,$v)){
            return true;
        }else{
            return false;
        }
    }
}