<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Db;

trait DbAdvanceTrait
{
    public function quoteIn($array)
    {
        if (empty($array)) {
            return 'NULL';
        }
        array_walk(
            $array,
            function (&$v, $k) {
                $v = is_string($v)?$this->quote($v):(string)$v;
            }
        );
        return implode(',', $array);
    }
    public function quoteSetArray($array)
    {
        $a = array();
        foreach ($array as $k => $v) {
            $a[] = $this->qouteScheme($k)."=".$this->pdo->quote((string)$v);
        }
        return implode(',', $a);
    }
    public function quoteAndArray($array)
    {
        $a = array();
        foreach ($array as $k => $v) {
            $a[] = $this->qouteScheme($k)."=".$this->pdo->quote((string)$v);
        }
        return implode('and ', $a);
    }
    public function qouteInsertArray($array)
    {
        if (empty($array)) {
            return '';
        }

        $pdo = $this->pdo;
        
        $keys = array_map(function ($v) {
            return '`'.$v.'`';
        }, array_keys($array));
        $values = array_map(function ($v) use ($pdo) {
            return $pdo->quote(''.$v);
        }, array_values($array));
        
        
        $str_keys = implode(',', $keys);
        $str_values = implode(',', $values);
        $ret = "($str_keys)VALUES($str_values)";
        return $ret;
    }


    public function findData($table_name, $id, $key = 'id')
    {
        $sql = "select * from ".$this->qouteScheme($table_name)." where {$key}=? limit 1";
        return $this->fetch($sql, $id);
    }
    
    public function insertData($table_name, $data, $return_last_id = true)
    {
        $sql = "insert into ".$this->qouteScheme($table_name)." ".$this->qouteInsertArray($data);

        $ret = $this->execute($sql);
        if (!$return_last_id) {
            return $ret;
        }
        $ret = $this->pdo->lastInsertId();
        return $ret;
    }
    public function deleteData($table_name, $id, $key = 'id', $key_delete = 'is_deleted')
    {
        if ($key_delete) {
            $sql = "update ".$this->qouteScheme($table_name)." set {$key_delete}=1 where {$key}=? limit 1";
            return $this->execute($sql, $id);
        } else {
            $sql = "delete from ".$this->qouteScheme($table_name)." where {$key}=? limit 1";
            return $this->execute($sql, $id);
        }
    }
    
    public function updateData($table_name, $id, $data, $key = 'id')
    {
        if (isset($data[$key])) {
            unset($data[$key]);
        }
        $frag = $this->quoteSetArray($data);
        $sql = "update ".$this->qouteScheme($table_name)." set ".$frag." where {$key}=?";
        $ret = $this->execute($sql, $id);
        return $ret;
    }
}
