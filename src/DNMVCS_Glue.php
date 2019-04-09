<?php
namespace DNMVCS;

trait DNMVCS_Glue
{
    public function assignRewrite($key, $value=null)
    {
        if (is_array($key)&& $value===null) {
            $this->options['rewrite_map']=array_merge($this->options['rewrite_map'], $key);
        } else {
            $this->options['rewrite_map'][$key]=$value;
        }
    }
    public function assignRoute($key, $value=null)
    {
        if (is_array($key)&& $value===null) {
            $this->options['route_map']=array_merge($this->options['route_map'], $key);
        } else {
            $this->options['route_map'][$key]=$value;
        }
    }
    //////////
    public static function DB($tag=null)
    {
        return DNDBManager::G()->_DB($tag);
    }
    public static function DB_W()
    {
        return DNDBManager::G()->_DB_W();
    }
    public static function DB_R()
    {
        return DNDBManager::G()->_DB_R();
    }
    /////////////

    public static function DI($name, $object=null)
    {
        return DNMVCSExt::G()->_DI($name, $object);
    }
    public static function InSwoole()
    {
        if (PHP_SAPI!=='cli') {
            return false;
        }
        if (!class_exists('Swoole\Coroutine')) {
            return false;
        }
        
        $cid = \Swoole\Coroutine::getuid();
        if ($cid<=0) {
            return false;
        }
        
        return true;
    }
    //////////////
    public static function SG()
    {
        return DNSuperGlobal::G();
    }
    public static function &GLOBALS($k, $v=null)
    {
        return DNSuperGlobal::G()->_GLOBALS($k, $v);
    }
    
    public static function &STATICS($k, $v=null)
    {
        return DNSuperGlobal::G()->_STATICS($k, $v, 1);
    }
    public static function &CLASS_STATICS($class_name, $var_name)
    {
        return DNSuperGlobal::G()->_CLASS_STATICS($class_name, $var_name);
    }
    ///////////////////
}
