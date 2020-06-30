<?php
/**
 * Cache.php
 * Created By Dreamn.
 * Date : 2020/5/17
 * Time : 6:47 下午
 * Description :  缓存类
 */

namespace app;

class Cache
{
    private static $cache_path=3600;//path for the cache
    private static $cache_expire=APP_CACHE;//seconds that the cache expires

//cache constructor, optional expiring time and cache path
    public static function init($exp_time=3600,$path=APP_CACHE){
        self::$cache_expire=$exp_time;
        self::$cache_path=$path;
    }

//returns the filename for the cache
    private static function fileName($key){
        return self::$cache_path.md5($key);
    }

//creates new cache files with the given data, $key== name of the cache, data the info/values to store
    public static function set($key, $data){
        $values = serialize($data);
        $filename = self::fileName($key);
        $file = fopen($filename, 'w');
        if ($file){//able to create the file
            flock($file, LOCK_EX);
            fwrite($file, $values);
            flock($file, LOCK_UN);
            fclose($file);
            return true;
        }
        else return false;
    }

//returns cache for the given key
    public static function get($key){
        $filename = self::fileName($key);
        if (!file_exists($filename) || !is_readable($filename)){//can't read the cache
            return null;
        }
        if ( time() < (filemtime($filename) + self::$cache_expire) ) {//cache for the key not expired
            $file = fopen($filename, "r");// read data file
            if ($file){//able to open the file
                flock($file, LOCK_SH);
                $data = fread($file, filesize($filename));
                flock($file, LOCK_UN);
                fclose($file);
                return unserialize($data);//return the values
            }
            else return null;
        }
        else {
            unlink($filename);
            return null;//was expired you need to create new
        }
    }
}