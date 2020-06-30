<?php
namespace app\includes;
/**
 * Created by dreamn.
 * Date: 2019-09-15
 * Time: 10:24
 */
class Hash{
    /**
     * MD5
     * @param $str
     * @return string
     */
    public static function md5($str){
        return hash("md5", $str);
    }

    /**
     * sha256
     * @param $str
     * @return string
     */
    public static function sha256($str){
        return hash("sha256", $str);
    }

    /**
     * sha512
     * @param $str
     * @return string
     */
    public static function sha512($str){
        return hash("sha512", $str);
    }
}