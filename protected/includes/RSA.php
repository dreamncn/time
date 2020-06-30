<?php
/**
 * Name         :RSA.php
 * Author       :dreamn
 * Date         :2020/2/14 00:20
 * Description  :RSA加密签名算法，用于登录验证与插件签名验证
 */
namespace app\includes;
class RSA
{
    /**
     * @var array 创建秘钥时需要的配置
     * 格式如下：
     * array(
     *      'config'        => 'D:\phpStudy\Apache\conf\openssl.cnf',    //openssl.cnf的路径。有些电脑不用配置。90%是用配置的
     *      "digest_alg"    => "sha512",
     *      "private_key_bits" => 4096,
     *      "private_key_type" => OPENSSL_KEYTYPE_RSA,
     *
     * );
     *
     */
    private $config = array();

    public function __construct($config = array())
    {
        $this->config = $config;
    }

    /**
     * 新建一个秘钥对
     * @param string $key 生成秘钥的密码.
     * @return array array('private_key'=>'','public_key'=>'')
     */
    public function create_keypair($key = null)
    {
        // 创建新的密钥对
        $res = openssl_pkey_new($this->config);
        // 获取私钥
        openssl_pkey_export($res, $privkey, $key, $this->config);

        // 解析这个秘钥资源
        $pubkey = openssl_pkey_get_details($res);
        // 得到公钥
        $pubkey = $pubkey["key"];

        // 得到之后。最好是用base64_encode编译一下。
        return array('private_key' => $privkey, 'public_key' => $pubkey);
    }


    /**
     * 使用私钥将数据加密
     * @param $data string 一个字符串类型的数据
     * @param $private_key string 私钥
     * @param $key string 创建秘钥时你设置的密码
     * @return string 返回加密后的数据
     */
    public function private_encrypt($data, $private_key, $key = null)
    {
        $bool = openssl_private_encrypt($data, $crypted_data, openssl_pkey_get_private($private_key, $key));
        return $bool ? $crypted_data : $bool;
    }

    /**
     * 使用公钥解密
     * @param $data string 用私钥加密后的数据
     * @param $public_key string 公钥
     * @return string 返回解析出来的数据
     */
    public function public_decrypt($data, $public_key)
    {
        $bool = openssl_public_decrypt($data, $decrypted_data, $public_key);
        return $bool ? $decrypted_data : $bool;
    }

    /**
     * 使用公钥将数据加密
     * @param $data string 需要加密的数据
     * @param $public_key string 公钥
     * @return string 加密后的数据
     */
    public function public_encrypt($data, $public_key)
    {
        $bool = openssl_public_encrypt($data, $enctypted_data, $public_key);
        return $bool ? $enctypted_data : $bool;
    }

    /**
     * 使用私钥加密
     * @param $data string 公钥加密数据
     * @param $private_key string 私钥
     * @param null $key 创建秘钥时你设置的密码
     * @return string 返回解密后的数据
     */
    public function private_decrypt($data, $private_key, $key = null)
    {
        $bool = openssl_private_decrypt($data, $decrypted_data, openssl_pkey_get_private($private_key, $key));
        return $bool ? $decrypted_data : $bool;
    }
}