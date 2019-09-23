<?php
namespace DNMVCS\SwooleHttpd;

use DNMVCS\SwooleHttpd\SwooleSingleton;
use Swoole\Coroutine;

class SwooleCoroutineSingleton
{
    use SwooleSingleton;
    protected static $_instances=[];
    protected static $cid_map=[];
    
    public static function ReplaceDefaultSingletonHandler()
    {
        if (defined('DNMVCS_SINGLETONEX_REPALACER')) {
            return false;
        }
        define('DNMVCS_SINGLETONEX_REPALACER', self::class . '::'.'SingletonInstance');
        return true;
    }
    public static function SingletonInstance($class, $object)
    {
        $cid = Coroutine::getuid();
        $cid=($cid<=0)?0:$cid;
        $cid=$cid_map[$cid]??$cid;
        
        if ($object===null) {
            $me=self::$_instances[$cid][$class]??null;
            if ($me!==null) {
                return $me;
            }
            if ($cid!==0) {
                $me=self::$_instances[0][$class]??null;
                if ($me!==null) {
                    return $me;
                }
            }
            
            $me=new $class();
            if (isset(self::$_instances[$cid])) {
                self::$_instances[$cid][$class]=$me;
            } else {
                self::$_instances[0][$class]=$me;
            }
            return $me;
        }
        self::$_instances[$cid][$class]=$object;
        return $object;
    }
    ///////////////
    public static function GetInstance($cid, $class)
    {
        return self::$_instances[$cid][$class]??null;
    }
    public static function SetInstance($cid, $class, $object)
    {
        self::$_instances[$cid][$class]=$object;
    }
    public static function DumpString()
    {
        return static::G()->_DumpString();
    }
    
    public static function EnableCurrentCoSingleton($cid=null)
    {
        if ($cid===0) {
            return;
        }
        if ($cid!==null) {
            $current_cid = Coroutine::getuid();
            self::$cid_map[$cid]=$current_cid;
            Coroutine::defer(
                function () use ($cid) {
                    unset(self::$cid_map[$cid]);
                }
            );
            return;
        }
        $cid = Coroutine::getuid();
        if ($cid<=0) {
            return;
        }
        if (isset(self::$_instances[$cid])) {
            return;
        }
        self::$_instances[$cid]=[];
        Coroutine::defer(
            function () {
                $cid = Coroutine::getuid();
                if ($cid<=0) {
                    return;
                }
                unset(self::$_instances[$cid]);
            }
        );
    }
    public function forkMasterInstances($classes, $exclude_classes=[])
    {
        $cid = Coroutine::getuid();
        if ($cid<=0) {
            return;
        }
        $cid=self::$cid_map[$cid]??$cid;
        
        foreach ($classes as $class) {
            if (!isset(self::$_instances[0][$class])) {
                $real_class=$class;
                if (in_array($real_class, $exclude_classes)) {
                    continue;
                }
                self::$_instances[$cid][$class]=new $class();
                
                continue;
            }
            $real_class=get_class(self::$_instances[0][$class]);
            if (in_array($real_class, $exclude_classes)) {
                self::$_instances[$cid][$class]=self::$_instances[$cid][$real_class];
                continue;
            }
            $object=self::$_instances[0][$real_class];
            self::$_instances[$cid][$real_class]=clone $object;
            if ($class!==$real_class) {
                self::$_instances[$cid][$class]=self::$_instances[$cid][$real_class];
            }
        }
    }
    
    public function forkAllMasterClasses()
    {
        $cid = Coroutine::getuid();
        foreach (self::$_instances[0] as $class =>$object) {
            if (!isset($object)) {
                continue;
            }
            self::$_instances[$cid][$class]=new $class();
        }
    }
    ///////////////////////
    public function _DumpString()
    {
        $cid = Coroutine::getuid();
        $ret="==== SwooleCoroutineSingleton List Current cid [{$cid}] ==== ;\n";
        foreach (self::$_instances as $cid=>$v) {
            foreach ($v as $cid_class=>$object) {
                $hash=$object?md5(spl_object_hash($object)):'';
                $class=$object?get_class($object):'';
                $class=$cid_class===$class?'':$class;
                $ret.="[$hash]$cid $cid_class($class)\n";
            }
        }
        return "{{$ret}}\n";
    }
    public static function Dump()
    {
        fwrite(STDERR, static::DumpString());
    }
}
