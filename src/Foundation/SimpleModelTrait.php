<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\Core\App;

trait SimpleModelTrait
{
    protected $table_name = null;
    protected $table_prefix = null;
    protected $table_pk = 'id';

    protected function getTableNameByClass($class)
    {
        $t = explode('\\', $class);
        $class = array_pop($t);
        
        $table_name = strtolower(substr($class, 0, -strlen('Model')));
        return $table_name;
    }
    
    protected function getTablePrefixByClass($class)
    {
        return '';
    }
    
    public function table()
    {
        if (!isset($this->table_prefix)) {
            $this->table_prefix = $this->getTablePrefixByClass(static::class);
        }
        if (!isset($this->table_name)) {
            $this->table_name = $this->getTableNameByClass(static::class);
        }
        return $this->table_prefix .  $this->table_name;
    }
    
    public function prepare($sql)
    {
        return str_replace(App::DbForRead()->quote('TABLE'), $this->table(), $sql);
    }
    public function getList(int $page = 1, int $page_size = 10)
    {
        $sql = "SELECT * from 'TABLE' where true order by id desc";
        $sql = $this->prepare($sql);
        
        $total = App::DbForRead()->fetchColumn(App::SqlForCountSimply($sql));
        $data = App::DbForRead()->fetchAll(App::SqlForPager($sql, $page, $page_size));
        return ['data' => $data,"total" => $total];
    }

    public function find($a)
    {
        if (is_scalar($a)) {
            $a = [$this->table_pk => $a];
        }
        $f = [];
        foreach ($a as $k => $v) {
            $f[] = $k . ' = ' . App::DbForRead()->quote($v);
        }
        $frag = implode('and ', $f);
        
        $sql = "SELECT * FROM 'TABLE' WHERE ".$frag;
        $sql = $this->prepare($sql);
        $ret = App::DbForRead()->fetch($sql);
        return $ret;
    }
    public function add($data)
    {
        $ret = App::DbForWrite()->insertData($this->table(), $data);
        return $ret;
    }
    public function update($id, $data)
    {
        $ret = App::DbForWrite()->updateData($this->table(), $id, $data, $this->table_pk);
        
        return $ret;
    }
    public function delete($id)
    {
        throw new \ErrorException('Impelement It.');
    }
    /*
    fetch, fetchAll,fetchClumn,fetchClass
    */
    protected function exec($sql, ...$args)
    {
        return App::DbForWrite()->table($this->table)->exec($sql, ...$args);
    }
    //////////
    protected function fetchAll($sql, ...$args)
    {
        return App::DbForRead()->table($this->table)->DbForRead($sql, ...$args);
    }
    protected function fetch($sql, ...$args)
    {
        return App::DbForRead()->table($this->table)->fetch($sql, ...$args);
    }
    protected function fetchColumn($sql, ...$args)
    {
        return App::DbForRead()->table($this->table)->fetchColumn($sql, ...$args);
    }
    protected function fetchObject($sql, ...$args)
    {
        return App::DbForRead()->setObjectResultClass(static::class)->table($this->table)->fetchObject($sql, ...$args);
    }
    protected function fetchObjectAll($sql, ...$args)
    {
        return App::DbForRead()->setObjectResultClass(static::class)->table($this->table)->fetchObjectAll($sql, ...$args);
    }
}
