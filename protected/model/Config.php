<?php
namespace app\model;
use app\lib\speed\mvc\Model;

/**
 * Created by dreamn.
 * Date: 2019-08-30
 * Time: 15:43
 */

class Config extends Model{


    public function __construct()
    {
        parent::__construct("blog_options");
    }

    /**
     * 获得设置信息
     * @param $index
     * @return mixed
     */
    public function getData($index){
        return $this->find(["op_name"=>$index],"op_value")["op_value"];
    }

    /**
     * 设置信息
     * @param $index
     * @param $value
     * @return mixed
     */
    public function setData($index,$value)
    {
        return $this->insert(self::Duplicate)->keys(["op_name","op_value"],['op_value'])->values([[$index,$value]])->commit();
    }

}