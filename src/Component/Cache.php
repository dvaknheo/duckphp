<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\ComponentBase;

class Cache extends ComponentBase //implements Psr\SimpleCache\CacheInterface;
{
    public function get($key, $default = null)
    {
        return $default;
    }
    public function set($key, $value, $ttl = null)
    {
        return false;
    }
    public function delete($key)
    {
        return false;
    }
    public function has($key)
    {
        return false;
    }
    public function clear()
    {
        return;
    }
    public function getMultiple($keys, $default = null)
    {
        $ret = [];
        foreach ($keys as $v) {
            $ret[$v] = $this->get($v, $default);
        }
        return $ret;
    }
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $k => $v) {
            $ret[$v] = $this->set($k, $v, $ttl);
        }
        return true;
    }
    public function deleteMultiple($keys)
    {
        return $this->delete($keys);
    }
}
