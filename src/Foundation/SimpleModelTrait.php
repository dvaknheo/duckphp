<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\Component\DbManager;
use DuckPhp\Core\App;
use DuckPhp\Core\Helper;
use DuckPhp\Core\SingletonTrait;

trait SimpleModelTrait
{
    use SingletonTrait;
    
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
        return App::Current()->options['table_prefix'] ?? '';
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
        return str_replace(DbManager::_()->_DbForRead()->quote('TABLE'), $this->table(), $sql);
    }
    protected function getList(int $page = 1, int $page_size = 10)
    {
        $sql = "SELECT * from 'TABLE' where true order by id desc";
        $sql = $this->prepare($sql);
        
        $total = DbManager::_()->_DbForRead()->fetchColumn(Helper::_()->_SqlForCountSimply($sql));
        $data = DbManager::_()->_DbForRead()->fetchAll(Helper::_()->_SqlForPager($sql, $page, $page_size));
        return ['data' => $data,"total" => $total];
    }

    protected function find($a)
    {
        if (is_scalar($a)) {
            $a = [$this->table_pk => $a];
        }
        $f = [];
        foreach ($a as $k => $v) {
            $f[] = $k . ' = ' . DbManager::_()->_DbForRead()->quote($v);
        }
        $frag = implode('and ', $f);
        
        $sql = "SELECT * FROM 'TABLE' WHERE ".$frag;
        $sql = $this->prepare($sql);
        $ret = DbManager::_()->_DbForRead()->fetch($sql);
        return $ret;
    }
    protected function add($data)
    {
        $ret = DbManager::_()->_DbForWrite()->insertData($this->table(), $data);
        return $ret;
    }
    protected function update($id, $data)
    {
        $ret = DbManager::_()->_DbForWrite()->updateData($this->table(), $id, $data, $this->table_pk);
        
        return $ret;
    }
    /*
    fetch, fetchAll,fetchClumn,fetchClass
    */
    protected function execute($sql, ...$args)
    {
        return DbManager::_()->_DbForWrite()->table($this->table())->execute($sql, ...$args);
    }
    //////////
    protected function fetchAll($sql, ...$args)
    {
        return DbManager::_()->_DbForRead()->table($this->table())->fetchAll($sql, ...$args);
    }
    protected function fetch($sql, ...$args)
    {
        return DbManager::_()->_DbForRead()->table($this->table())->fetch($sql, ...$args);
    }
    protected function fetchColumn($sql, ...$args)
    {
        return DbManager::_()->_DbForRead()->table($this->table())->fetchColumn($sql, ...$args);
    }
    protected function fetchObject($sql, ...$args)
    {
        return DbManager::_()->_DbForRead()->setObjectResultClass(static::class)->table($this->table())->fetchObject($sql, ...$args);
    }
    protected function fetchObjectAll($sql, ...$args)
    {
        return DbManager::_()->_DbForRead()->setObjectResultClass(static::class)->table($this->table())->fetchObjectAll($sql, ...$args);
    }
}
