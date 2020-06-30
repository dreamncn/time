<?php
namespace app\includes;
/**
 * Class AES
 * @package includes
 */
class AES
{
    /**
     * AES加密
     * @param $data
     * @param $key
     * @return string
     */
    public function encrypt($data,$key){
        return  base64_encode(openssl_encrypt($data, 'AES-128-ECB',$key, OPENSSL_RAW_DATA ));
    }

    /**
     * AES解密
     * @param $data
     * @param $key
     * @return false|string
     */
    public function decrypt($data,$key){
        return openssl_decrypt(base64_decode($data),  'AES-128-ECB', $key,OPENSSL_RAW_DATA);
    }

    /**
     * 获取随机字符串
     * @param int $length
     * @return string
     */
    public static function getRandom($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $password;
    }
}