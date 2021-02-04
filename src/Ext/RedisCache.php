<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;

class RedisCache extends ComponentBase //implements Psr\SimpleCache\CacheInterface;
{
    public $options = [
        'redis_cache_skip_replace' => false,
        'redis_cache_prefix' => '',
    ];
    protected $context_class;
    //override
    protected function initContext(object $context)
    {
        $this->context_class = get_class($context);
        if (!$this->options['redis_cache_skip_replace']) {
            $this->context_class::Cache($this);
        }
    }
    //////////////////////////////
    protected function redis()
    {
        return $this->context_class::Redis();
    }
    public function get($key, $default = null)
    {
        $ret = $this->redis()->get($this->options['redis_cache_prefix'].$key);
        
        if ($ret !== false) {
            $ret = json_decode($ret, true);
        }
        return $ret;
    }
    public function set($key, $value, $ttl = null)
    {
        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        $ret = $this->redis()->set($this->options['redis_cache_prefix'].$key, $value, $ttl);
        return $ret;
    }
    public function delete($key)
    {
        $key = is_array($key)?$key:[$key];
        foreach ($key as &$v) {
            $v = $this->options['redis_cache_prefix'].$v;
        }
        unset($v);
        
        $ret = $this->redis()->del($key);
        return $ret;
    }
    public function has($key)
    {
        $ret = $this->redis()->exists($this->options['redis_cache_prefix'].$key);
        return $ret;
    }
    /////////
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
