<?php

namespace app\includes;
/**
 * Class Img2local
 * 图片本地化功能，或者说是图片转存功能，回头写成插件
 * @package includes
 */
class Img2local
{
    private $img = false;
    private $content;
    private $err;
    private $total = 1;
    private $upNum = 0;

    /**
     * Img2local constructor.
     * @param $content  string
     * @param $ismarkdown  string
     */
    public function __construct($content, $ismarkdown)
    {
        $this->content = $content;
        if (!$ismarkdown) {
            $isMatched = preg_match_all('/<img([^>]+)src=\"([^>\"]+)\"?([^>]*)>/', $content, $matches);
            if ($isMatched) {
                $this->total = $isMatched;
                $this->img = $matches[2];
            }
        } else {
            $isMatched = preg_match_all('/!\[.*?]\((.*?)\)/', $content, $matches);
            if ($isMatched) {
                $this->total = $isMatched;
                $this->img = $matches[1];
            }

        }

    }

    /**
     * 保存图片
     * @return bool|string|string[]
     */
    public function savePic()
    {
        if(isDebug())
        logs('Img2local Start -> Size:'.strval(sizeof($this->img)), 'info', 'Img2local');
        if ($this->img) {
            $replaceImg = [];
            $u = new FileUpload('article');
            foreach ($this->img as $v) {
                $this->upNum++;
                $file = $this->saveTmp($v);
                if(isDebug())
                logs('Save pic->' . $v, 'debug', 'Img2local');
                if ($file) {
                    $result = $u->upload($file);
                    if(isDebug())
                    logs('Upload pic->' . $file, 'debug', 'Img2local');
                    if ($result) {
                        //var_dump($result,$u->getFileUrl());
                        $replaceImg[] = $u->getFileUrl();
                        if(isDebug())
                        logs('Upload Success', 'debug', 'Img2local');
                    } else {//上传失败
                        $replaceImg[] = $v;
                        if(isDebug())
                        logs('Upload Failed', 'debug', 'Img2local');
                    }
                    unlink($_FILES[$file]['tmp_name']);
                } else {
                    //保存失败，不动
                    $replaceImg[] = $v;
                }
            }
            $this->content = str_replace($this->img, $replaceImg, $this->content);
            logs('Img2local END', 'info', 'Img2local');
            return $this->content;
        } else  return false;
    }

    public function getErr()
    {
        return $this->err;
    }

    private function get($testurl, $header = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $testurl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 检查证书中是否设置域名
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //是否抓取跳转后的页面
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:72.0) Gecko/20100101 Firefox/72.0');
        //参数为1表示传输数据，为0表示直接输出显示。
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //设置头文件的信息作为数据流输出
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //参数为0表示不带头文件，为1表示带头文件
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }


    private function saveTmp($img)
    {
        $url = parse_url($img);
        $host = $_SERVER['HTTP_HOST'];
        if (isset($url['host']) && $url['host'] != $host && !strstr($url['host'], 'cdn.jsdelivr.net')) {
            $header = [
                'Referer:' . $url['host'],
                'HOST:' . $url['host'],
                "ACCEPT: */*",
                "ACCEPT-LANGUAGE: zh-cn",
                'USER_AGENT: ' . $_SERVER['HTTP_USER_AGENT'],
                "CONNECTION: close"
            ];
            $imgData = $this->get($img, $header);
            if ($imgData !== "") {
                $fname = md5($img) . '.jpg';
                $attachpath = APP_TMP . $fname;
                if ($fp = @fopen($attachpath, 'w+')) {
                    fwrite($fp, $imgData);
                    $imgLength = filesize($attachpath);
                } else {
                    $this->err = "创建临时文件失败，请检查是否具有可写权限！";
                    return false;
                }
            } else {
                $this->err = "图片不存在！";
                return false;
            }
        } else {
            $this->err = "网络错误！";
            return false;
        }
        $_FILES=[];
        $name = md5($fname);
        $_FILES[$name]["name"] = $fname;
        $_FILES[$name]["tmp_name"] = $attachpath;
        $_FILES[$name]["size"] = $imgLength;
        $_FILES[$name]["error"] = UPLOAD_ERR_OK;
        $_FILES[$name]["type"] = "image/jpg";
        return $name;
    }
}