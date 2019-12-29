<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace UUU\Model;

use UUU\Base\ModelHelper as M;

class SettingModel extends BaseModel
{
    public function get($key)
    {
        $sql = "SELECT v FROM Settings WHERE k=?";
        $ret = M::DB()->fetchColumn($sql, $key);
        return $ret;
    }
    public function set($key, $value)
    {
        $sql = "INSERT INTO Settings (k,v) VALUES(?,?) ON DUPLICATE KEY UPDATE  v=?";
        $ret = M::DB()->execute($sql, $key, $value, $value);
    }
}
