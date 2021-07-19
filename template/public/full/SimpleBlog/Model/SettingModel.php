<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Model;

class SettingModel extends BaseModel
{
    protected $table_name = 'Settings';
    public function get($key)
    {
        $sql = "SELECT v FROM 'TABLE' WHERE k=?";
        $sql = $this->prepare($sql);
        $ret = BaseModel::Db()->fetchColumn($sql, $key);
        return $ret;
    }
    public function set($key, $value)
    {
        $sql = "INSERT INTO 'TABLE' (k,v) VALUES(?,?) ON DUPLICATE KEY UPDATE  v=?";
        $sql = $this->prepare($sql);
        $ret = BaseModel::Db()->execute($sql, $key, $value, $value);
    }
}
