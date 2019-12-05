<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\SingletonEx;

class RedisSimpleCache //extends Psr\SimpleCache\CacheInterface;
{
    use SingletonEx;
    
    public $options = [
    ];
    public $redis = null;
    public $prefix = '';
    
    public function __construct()
    {
    }
    public function init(array $options, object $context = null)
    {
        $this->options = array_intersect_key(array_replace_recursive($this->options, $options) ?? [], $this->options);
        $this->redis = $options['redis'] ?? null;
        $this->prefix = $options['prefix'] ?? '';
    }
    public function get($key, $default = null)
    {
        if (!$this->redis || !$this->redis->isConnected()) {
            return $default;
        }
        $ret = $this->redis->get($this->prefix.$key);
        
        if ($ret !== false) {
            $ret = json_decode($ret, true);
        }
        return $ret;
    }
    public function set($key, $value, $ttl = null)
    {
        if (!$this->redis || !$this->redis->isConnected()) {
            return false;
        }
        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        $ret = $this->redis->set($this->prefix.$key, $value, $ttl);
        return $ret;
    }
    public function delete($key)
    {
        if (!$this->redis || !$this->redis->isConnected()) {
            return false;
        }
        $key = is_array($key)?$key:[$key];
        foreach ($key as &$v) {
            $v = $this->prefix.$v;
        }
        unset($v);
        
        $ret = $this->redis->del($key);
        return $ret;
    }
    public function has($key)
    {
        if (!$this->redis || !$this->redis->isConnected()) {
            return false;
        }
        $ret = $this->redis->exists($this->prefix.$key);
        return $ret;
    }
    public function clear()
    {
        //NO Impelment this;
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
