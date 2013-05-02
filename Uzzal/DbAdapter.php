<?php
namespace Uzzal;
/**
 * Description of DbAdapter
 *
 * @author uzzal
 */
class DbAdapter extends \PDO{
    private static $_me;
    private $_fetchMode = \PDO::FETCH_ASSOC;

    public function __construct($config) {
        parent::__construct($config['dsn'],$config['username'],$config['password']);
    }

    public function useBufferedQuery(){
        $this->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);
    }

    public static function getInstance($config){
        if(!self::$_me){
            self::$_me = new DbAdapter($config);
        }

        return self::$_me;
    }

    public function setFetchMode(int $mode){
        $this->_fetchMode = $mode;
    }

    public function getFetchMode(){
        return $this->_fetchMode;
    }

    public function fetchAll($sql, $args=''){
        if($args){
            $stat = $this->_prepareExecute($sql, $args);
        }else{
            $stat = $this->query($sql);
        }

        return $stat->fetchAll($this->getFetchMode());
    }

    public function fetch($sql, $args=''){
        if($args){
            $stat = $this->_prepareExecute($sql, $args);
        }else{
            $stat = $this->query($sql);
        }

        return $stat->fetch($this->getFetchMode());
    }

    private function _prepareExecute($sql, $args){
        $stat = $this->prepare($sql);
        if(is_array($args)){
            $stat->execute($args);
        }else{
            $stat->execute(array($args));
        }

        return $stat;
    }

    private function _execute($sql){
        $stat = $this->prepare($sql);
        $stat->execute($args);

        return $stat;
    }

    public function insert($table, array $data){
        foreach($data as $d){
            $w[] = '?';
            $args[] = $d;
        }

        $cols = array_keys($data);
        array_walk($cols, function(&$key){$key = '`'.$key.'`';});

        $cols = implode(',',$cols);
        $vals = implode(',',$w);

        $sql = "INSERT INTO `{$table}` ({$cols}) VALUES ({$vals})";
        $stat = $this->_prepareExecute($sql, $args);

        return $stat->rowCount();
    }

    public function update($table, array $data, $cond=''){
        foreach($data as $k=>$v){
            if(is_numeric($v)){
                $args[] = "`{$k}`={$v}";
            }else{
                $args[] = "`{$k}`='{$v}'";
            }
        }

        $values = implode(',', $args);
        if($cond){
            $cond = ' WHERE '.$cond;
        }

        $sql = "UPDATE `{$table}` SET {$values}".$cond;

        $stat = $this->_execute($sql);
        return $stat->rowCount();
    }
}