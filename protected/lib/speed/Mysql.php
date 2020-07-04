<?php
/**
 * Mysql.php
 * Created By Dreamn.
 * Date : 2020/5/18
 * Time : 8:09 下午
 * Description :Mysql底层封装类
 */

namespace app;
use PDO;
use PDOException;
use PDOStatement;

class Mysql
{
    private $sql=[];//查询过的sql语句列表
    protected $opt=[];//封装常见的数据库查询选项
    protected $page=null;//开启分页的分页数据
    private $table_name;
    private $traSql=null;//编译完成的sql语句
    private $bindParam=[];//绑定的参数列表
    /*SQL语句插入方式*/
    const Normal='NORMAL';
    const Ignore='IGNORE';
    const Duplicate='DUPLICATE';
    public function __construct($table_name='')
    {
        if (!class_exists("PDO") || !in_array("mysql", PDO::getAvailableDrivers(), true)) {
            Error::err('Database Err: PDO or PDO_MYSQL doesn\'t exist!');
        }
        //初始化基础数据
        $this->opt['type']='select';
        $this->opt['table_name']=$table_name;
        $this->table_name=$table_name;
    }

    //对数据进行处理，转换成编译所需的sql语句
   private function translateSql(){
        $sql=$this->opt['type'];//select insert delete update
        $table=$this->opt['table_name'];//必填项目
       if($sql==''||$table=='')Error::err('Database Err: Missing required item "select/insert/delete/update" and "table"');
        switch($sql){
            case 'select':
                $sql='';
                $sql.=$this->getOpt('SELECT','field');
                $sql.=$this->getOpt('FROM','table_name');
                $sql.=$this->getOpt('WHERE','where');
                $sql.=$this->getOpt('ORDER BY','order');
                $sql.=$this->getOpt('LIMIT','limit');
                break;
            case 'update':
                $sql='';
                $sql.=$this->getOpt('UPDATE','table_name');
                $sql.=$this->getOpt('SET','set');
                $sql.=$this->getOpt('WHERE','where');
                break;
            case 'insert':
                $sql='';
                if(isset($this->opt['model'])){
                    switch ($this->opt['model']){
                        case self::Duplicate:
                            $sql.=$this->getOpt('INSERT INTO','table_name');
                            $sql.=$this->getOpt('','key');
                            $sql.=$this->getOpt('VALUES','values');
                            $sql.=$this->getOpt('ON DUPLICATE KEY UPDATE','colums');
                            break;
                        case self::Normal:
                            $sql.=$this->getOpt('INSERT INTO','table_name');
                            $sql.=$this->getOpt('','key');
                            $sql.=$this->getOpt('VALUES','values');
                            break;
                        case self::Ignore:
                            $sql.=$this->getOpt('INSERT IGNORE INTO','table_name');
                            $sql.=$this->getOpt('','key');
                            $sql.=$this->getOpt('VALUES','values');
                            break;
                    }
                }

                break;
            case 'delete':
                $sql='';
                $sql.=$this->getOpt('DELETE FROM','table_name');
                $sql.=$this->getOpt('WHERE','where');
                break;
        }
        $this->traSql=$sql;
        if(isDebug()){
            logs('[SQL]'.$sql,'info');
        }
   }
   /*
    * 获取设置的字段信息
    * */
   private function getOpt($head,$opt){
       if(isset($this->opt[$opt]))return ' '.$head.' '.$this->opt[$opt].' ';
       return ' ';
   }
    /**
     * 获取数据库连接函数
     * @param $db_config array 数据库配置信息
     * @return PDO
     */
    protected function dbInstance($db_config)
    {
        if (empty($GLOBALS['mysql_instances'])) {
            try {
                $GLOBALS['mysql_instances'] = new PDO(
                    'mysql:dbname=' . $db_config['MYSQL_DB'] . ';host=' . $db_config['MYSQL_HOST'] . ';port=' . $db_config['MYSQL_PORT'],
                    $db_config['MYSQL_USER'],
                    $db_config['MYSQL_PASS'],
                    array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'' . $db_config['MYSQL_CHARSET'] . '\'',
                        PDO::ATTR_PERSISTENT => true
                    ));
            } catch (PDOException $e) {
                Error::err('Database Err: ' . $e->getMessage());
            }
        }
        return $GLOBALS['mysql_instances'];
    }
    /**
     * 直接执行sql语句
     * @param string $sql sql语句
     * @param array $params
     * @param bool $readonly 是否为查找模式
     * @return mixed
     */
    public function execute($sql, $params = array(),$readonly=false)
    {
        /**
         * @var $sth PDOStatement
         */
        $this->sql[] = $sql;

        $sth = $this->dbInstance($GLOBALS['mysql'])->prepare($sql);
       
        if (is_array($params) && !empty($params)) foreach ($params as $k => &$v) {
            if (is_int($v)) {
                $data_type = PDO::PARAM_INT;
            } elseif (is_bool($v)) {
                $data_type = PDO::PARAM_BOOL;
            } elseif (is_null($v)) {
                $data_type = PDO::PARAM_NULL;
            } else {
                $data_type = PDO::PARAM_STR;
            }

            $sth->bindParam($k, $v, $data_type);
        }
        if ($sth->execute()) return $readonly ? $sth->fetchAll(PDO::FETCH_ASSOC) : $sth->rowCount();
        $err = $sth->errorInfo();
        Error::err('Database SQL: "' . $sql . '", ErrorInfo: ' . $err[2]);
        return false;
    }
    /************select语句封装***********/
    /**
     * @param string $field
     * @return Mysql
     */
    public function select($field="*"){
        $this->opt=[];
        $this->opt['table_name']=$this->table_name;
        $this->opt['type']='select';
         $this->opt['field']=$field;
         $this->bindParam=[];
         return $this;
    }

    /**
     * @param string|array $table_name
     * @return $this
     */
    public function table($table_name){
        $this->opt['union']=false;
        if(is_array($table_name)){
            $this->opt['union']=true;
            $table_name=implode(',',$table_name);
        }
        $this->opt['table_name']=$table_name;
        return $this;
    }

    /**
     * @param array $conditions 查询条件，支持后面几种格式 array($key=>$val,$key,':bind'=>$bind)
     * @param string $union 多表联合查询
     * @return Mysql
     */
    public function where($conditions,$union=''){
        if(isset($this->opt['union'])&&$this->opt['union']&&$union==''){
             Error::err('Database Err: Multi-table join query requires join fields');
        }
        if($union!==''){
            if(is_array($conditions) && !empty($conditions))$union=' AND '.$union;
        }
        
        if (is_array($conditions) && !empty($conditions)) {

            $sql = null;
            $join = array();
            reset($conditions);

            foreach ($conditions as $key => $condition){
                if (is_int($key)) {
                    $join[] = $condition;
                    unset($conditions[$key]);
                    continue;
                }
                $key=str_replace('.', '_', $key);
                if (substr($key, 0, 1) != ":") {
                    unset($conditions[$key]);
                    $conditions[":_WHERE_" .$key ] = $condition;
                    $join[] = "`" . str_replace('.', '`.`', $key) . "` = :_WHERE_" . $key;
                }

            }
            if (!$sql) $sql = join(" AND ", $join);
            
            $this->opt['where'] =    $sql.$union;
            $this->bindParam+= $conditions;
        }
        return $this;
    }

    /**
     * 数据插入
     * @param string $type 插入方式，参考常量
     * @return Mysql
     */
    public  function insert($type=self::Normal){
        $this->opt=[];
        $this->opt['table_name']=$this->table_name;
        $this->opt['type']='insert';
        $this->opt['model']=$type;
        $this->bindParam=[];
        return $this;
    }

    /**
     * @param array $key 插入的字段名称数组
     * @param array $colums 需要更新的字段
     * @return Mysql
     */
    public function keys($key,$colums=array()){
        if($this->opt['model']==self::Duplicate&&sizeof($colums)==0){
            Error::err('Database Err: duplicate insert must have update field');
        }
        $value = '';
        foreach ($key as $v){
            $value.="`{$v}`,";
        }
        $value='('.rtrim($value, ",").')';
        $this->opt['key']=$value;
        foreach ($colums as $k) {
            $update[] = "`{$k}`" . " = VALUES(`" . $k.'`)';
        }
        if($colums!==[])
            $this->opt['colums']=implode(', ', $update);
        return $this;
    }
    public function values($row){
        $length= sizeof($row);
        $k=0;$values=[];$marks='';
        for($i=0;$i<$length;$i++){
            $marks.='(';
           foreach ($row[$i] as $val){
               $values[":_INSERT_" . $k] = $val;
               $marks.= ":_INSERT_" . $k.',';
               $k++;

           }
            $marks=rtrim($marks, ",").'),';
        }
        $marks=rtrim($marks, ",");
        $this->opt['values']=$marks;
        $this->bindParam+=$values;

        return $this;
    }
    //insert update  del
    public function commit(){

        switch ($this->opt['type']) {
            case 'select':


                if(isset($this->opt['page'])){
                    $sql= 'SELECT COUNT(*) as M_COUNTER ';

                    $sql.=$this->getOpt('FROM','table_name');
                    $sql.=$this->getOpt('WHERE','where');

                    $sql.=$this->getOpt('ORDER BY','order');

                    $total = $this->execute( $sql, $this->bindParam,true);
                    $this->page=$this->pager($this->opt['start'], $this->opt['count'], $this->opt['range'], $total[0]['M_COUNTER']);
                    if(!empty($this->page))
                    $this->opt['limit'] =  $this->page['offset'] . ',' . $this->page['limit'];
                }
                $this->translateSql();
                $result=$this->execute($this->traSql,$this->bindParam,true);
                /*if(sizeof($result)==1)
                    return $result[0];
                else*/
                    return $result;
                break;
            case 'insert':
                $this->translateSql();
                $this->execute($this->traSql,$this->bindParam,false);
                return $this->dbInstance($GLOBALS['mysql'])->lastInsertId();
                break;
        }
        $this->translateSql();
        return $this->execute($this->traSql,$this->bindParam,false);
    }

    /**
     * 更新
     * @return $this
     */
    public function update(){

        $this->opt=[];
        $this->opt['table_name']=$this->table_name;
        $this->opt['type']='update';
        $this->bindParam=[];
        return $this;
    }
    public function set($row){
        $values = array();
        $set='';
        foreach ($row as $k => $v) {
            if(is_int($k)){
                $set.= $v.',';
                continue;
            }
            $values[":_UPDATE_" . $k] = $v;
            $set.= "`{$k}` = " . ":_UPDATE_" . $k.',';
        }
        $set=rtrim($set, ",");
        $this->bindParam+=$values;
        $this->opt['set']=$set;
        return $this;
    }

    /**
     * 删除
     * @return $this
     */
    public function delete(){
        $this->opt=[];
        $this->opt['table_name']=$this->table_name;
        $this->opt['type']='delete';
        $this->bindParam=[];
        return $this;
    }

    public function limit($limit='1',$page=false,$start=1,$count=10,$range=10)
    {
        if($page){
            $this->opt['page'] = true;
            $this->opt['start'] =$start;
            $this->opt['count'] =$count;
            $this->opt['range'] =$range;
        }else $this->opt['limit']=$limit;
        return $this;
    }
    

    public function orderBy( $string)
    {
        $this->opt['order']=$string;
        return $this;
    }
    public function dumpSql(){
        return $this->sql;
    }
    public function getPage(){
        return $this->page;
    }

    /**
     * 分页处理函数
     * @param $page
     * @param int $pageSize
     * @param int $scope
     * @param int $total
     * @return array|null
     */
    protected function pager($page, $pageSize = 10, $scope = 10, $total = 0)
    {
        $this->page = null;
        if ($total > $pageSize) {
            $total_page = ceil($total / $pageSize);
            $page = min(intval(max($page, 1)), $total_page);
            $this->page = array(
                'total_count' => $total,//总数量
                'page_size' => $pageSize,//一页大小
                'total_page' => $total_page,//总页数
                'first_page' => 1,//第一页
                'prev_page' => ((1 == $page) ? 1 : ($page - 1)),//上一页
                'next_page' => (($page == $total_page) ? $total_page : ($page + 1)),//下一页
                'last_page' => $total_page,//最后一页
                'current_page' => $page,//当前页
                'all_pages' => array(),//所有页
                'offset' => ($page - 1) * $pageSize,
                'limit' => $pageSize,
            );
            $scope = (int)$scope;
            if ($total_page <= $scope) {
                $this->page['all_pages'] = range(1, $total_page);
            } elseif ($page <= $scope / 2) {
                $this->page['all_pages'] = range(1, $scope);
            } elseif ($page <= $total_page - $scope / 2) {
                $right = $page + (int)($scope / 2);
                $this->page['all_pages'] = range($right - $scope + 1, $right);
            } else {
                $this->page['all_pages'] = range($total_page - $scope + 1, $total_page);
            }
        }
        return $this->page;
    }


}