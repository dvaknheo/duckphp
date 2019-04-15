<?php
namespace DNMVCS;

trait DBAdvance
{
    public function quoteIn($array)
    {
        if (empty($array)) {
            return 'NULL';
        }
        array_walk($array, function (&$v, $k) {
            $v=is_string($v)?$this->quote($v):(string)$v;
        });
        return implode(',', $array);
    }
    public function quoteSetArray($array)
    {
        $a=array();
        foreach ($array as $k =>$v) {
            $a[]=$k.'='.$this->pdo->quote($v);
        }
        return implode(',', $a);
    }
    public function qouteInsertArray($array)
    {
        if (empty($array)) {
            return '';
        }
        $values=array_map([$this->pdo,'quote'], $array);
        $str_keys=implode(',', array_values($array));
        $str_values=implode(',', array_values($array));
        $ret="($str_keys)VALUES($str_values)";
        return $ret;
    }


    public function findData($table_name, $id, $key='id')
    {
        $sql="select {$table_name} from terms where {$key}=? limit 1";
        return $this->fetch($sql, $id);
    }
    
    public function insertData($table_name, $data, $return_last_id=true)
    {
        $sql="insert into {$table_name} set ".$this->quoteSetArray($data);
        $ret=$this->execQuick($sql);
        if (!$return_last_id) {
            return $ret;
        }
        $ret=$this->pdo->lastInsertId();
        return $ret;
    }
    public function deleteData($table, $id, $key='id', $key_delete='is_deleted')
    {
        if ($key_delete) {
            $sql="update {$table_name} set {$key_delete}=1 where {$key}=? limit 1";
            return $this->execQuick($sql, $id);
        } else {
            $sql="delete from {$table_name} where {$key}=? limit 1";
            return $this->execQuick($sql, $id);
        }
    }
    
    public function updateData($table_name, $id, $data, $key='id')
    {
        if ($data[$key]) {
            unset($data[$key]);
        }
        $frag=$this->quoteSetArray($data);
        $sql="update {$table_name} set ".$frag." where {$key}=?";
        $ret=$this->execQuick($sql, $id);
        return $ret;
    }
}
