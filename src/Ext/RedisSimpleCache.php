<?php
namespace DNMVCS\Ext;

use DNMVCS\Core\SingletonEx;

class RedisSimpleCache //extends Psr\SimpleCache\CacheInterface;
{
    use SingletonEx;
    
    public $redis=null;
    public $prefix='';
    
    public function init(array $options, $context=null)
    {
        //
    }
    public function initWithServer($redis,$prefix)
    {
        $this->redis=$redis;
        $this->prefix=$prefix;
    }
    public function get($key, $default = null)
    {
        if(!$this->redis){ return $default;}
        $ret=$this->redis->get($this->prefix.$key);
        return $ret;
    }
    public function set($key, $value, $ttl = null)
    {
        if(!$this->redis){ return false;}
        $ret=$this->redis->set($this->prefix.$key,$value,$ttl);
        return $ret;
    }
    public function delete($key)
    {
        if(!$this->redis || !$this->redis->isConnected()){ return false;}
        $key=is_array($key)?$key:[$key];
        foreach($key as &$v){
            $v=$this->prefix.$v;
        }
        unset($v);
        
        $ret=$this->redis->del($key);
        return $ret;
    }
    public function has($key)
    {
        if(!$this->redis){ return false;}
        $ret=$this->redis->exists($this->prefix.$key);
        return $ret;
    }
    public function clear()
    {
        //NO Impelment this;
        return;
    }
    public function getMultiple($keys, $default = null)
    {
        $ret=[];
        foreach($keys as $v){
            $ret[$v]=$this->get($v,$default);
        }
        return $ret;
    }
    public function setMultiple($values, $ttl = null)
    {
        foreach($values as $k=>$v){
            $ret[$v]=$this->set($k,$v,$ttl);
        }
        return true;
    }
    public function deleteMultiple($keys)
    {
        return $this->delete($keys);
    }
}