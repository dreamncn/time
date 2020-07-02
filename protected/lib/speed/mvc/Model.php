<?php

namespace app\lib\speed\mvc;

use app\Mysql;

/**
 * Class Model
 * @package lib\speed\mvc
 */
class Model extends Mysql
{

    public $page;
    /**
     * 数据表名称
     * @var string $table_name
     */
    protected $table_name;


    /**
     * Model constructor.
     * @param null $table_name
     */
    public function __construct($table_name = null)
    {
        if ($table_name) $this->table_name = $table_name;
        parent::__construct($table_name);
    }



    /*
     * 单个设置
     * */
    public function setOption($idName,$id,$opt,$val){
      return  $this->update()->where([$idName=>$id])->set([$opt=>$val])->commit();
    }

    public function find($condition,$field="*"){
        $result=$this->select($field)->where($condition)->limit(1)->commit();
        if(empty($result)){
            return null;
        }
        return $result[0];

    }

    public function insertOne($condition){
       $keys=array_keys($condition);
       $values=array_values($condition);
       return $this->insert()->keys($keys)->values([$values])->commit();
    }

    public function getCount($condition=null){
        $sql=$this->select('count(*) as M_COUNT');
        if($condition!==null)
            $sql = $sql->where($condition);
        return $sql->commit()[0]['M_COUNT'];
    }

    public function getSum($field,$condition=null){
        $sql=$this->select('sum('.$field.') as M_SUM');
        if($condition!==null)
            $sql = $sql->where($condition);
        return $sql->commit()[0]['M_SUM'];
    }

    public function query($sql, $params = array()){
        return $this->execute($sql,$params,true);
    }

}