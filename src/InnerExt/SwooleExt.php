<?php
namespace DNMVCS\InnerExt;

use DNMVCS\Basic\SingletonEx;
use DNMVCS\SwooleHttpd\SwooleHttpd;
use Exception;

class SwooleExt
{
    use SingletonEx;
    
    protected $with_http_handler_root=false;
    protected $appClass;
    protected $is_inited=false;
    public static function Server($server=null)
    {
        return DNSwooleExtServerHolder::G($server);
    }
    public static function App($app=null)
    {
        return DNSwooleExtAppHolder::G($app);
    }
    public static function _EmptyFunction()
    {
        return;
    }
    
    public function setAppClass($class)
    {
        static::App($class::G());
        
        $this->appClass=$class;
    }
    public function init($options=[], $context=null)
    {
        if (PHP_SAPI!=='cli') {
            return;
        }
        if ($this->is_inited) {
            return $this;
        }
        if (!class_exists(SwooleHttpd::class)) {
            return;
        }
        $options=$options['swoole'];
        
        if ($context) {
            $app_class=$context->getOverrideRootClass();
            $this->setAppClass($app_class);
            $context->addBeforeRunHandler([static::class,'OnRun']);
        }
        
        $server_object=SwooleHttpd::G();
        //static::ThrowOn(!class_exists(SwooleHttpd::class), "DNMVCS: You Need SwooleHttpd");
        static::Server($server_object);
        $server=static::Server();
        
        $app=static::App();
        
        $instances=[];
        $classes=$app->getStaticComponentClasses();
        foreach ($classes as $class) {
            $instances[$class]=$class::G();
        }
        $instances[$app_class]=$app_class::G();
        $instances[get_class($app_class::G())]=$context::G();

        
        $flag=$server::ReplaceDefaultSingletonHandler();
        if (!$flag) {
            return;
        }
        // replace G method again;
        
        static::Server($server);
        static::App($app);
        foreach ($instances as $class=>$object) {
            $class::G($object);
        }
        static::G($this);
        //////////////
        
        $this->with_http_handler_root=$options['with_http_handler_root']??false;
        $options['http_handler']=[$this,'runSwoole'];
        
        static::Server()->init($options, null);
        $this->is_inited=true;
        return $this;
    }
    public static function OnRun()
    {
        return static::G()->run();
    }
    public function run()
    {
        if (!$this->is_inited) {
            return;
        }
        $cid = \Swoole\Coroutine::getuid();
        if ($cid>0) {
            return;
        }
        static::App()->options['error_404']=[static::class,'_EmptyFunction'];
        static::App()->options['use_super_global']=true;
        static::Server()->run();
    }
    public function runSwoole()
    {
        $classes=static::App()->getDynamicComponentClasses();
        $exclude_classes=static::Server()->getDynamicClasses();
        static::Server()->forkMasterInstances($classes, $exclude_classes);
        
        $ret=static::App()->run();
        if (!$ret && $this->with_http_handler_root) {
            $classes=static::App()->getStaticComponentClasses();
            $classes[]=get_class(static::App());
            $classes[]=static::App()->getOverrideRootClass();
            
            static::Server()->forkMasterInstances($classes);

            DnSwooleExtReuserHolder::G()->appClass=$this->appClass;
            ([$this->appClass,'G'])(DnSwooleExtReuserHolder::G()); //fake object
            return false;
        }
        return true;
    }
}
class DnSwooleExtReuserHolder
{
    use SingletonEx;

    public $appClass;
    public function init($options=[], $context=null)
    {
        //for 404 re-in;
        $class=$this->appClass;
        if (get_class($class::G())!==static::class) {
            return $this;
        }
        SwooleExt::Server()->resetInstances();
        
        $ret=$class::G()->init($options);
        return $ret;
    }
}
class DNSwooleExtServerHolder
{
    use SingletonEx;
    
    public static function ReplaceDefaultSingletonHandler()
    {
        throw new Exception("You Need DNMVCS\\SwooleHttpd !");
    }
    public function init()
    {
        throw new Exception("Impelement Me!");
    }
    public function run()
    {
        throw new Exception("Impelement Me!");
    }
    public function getDynamicClasses()
    {
        throw new Exception("Impelement Me!");
    }
    public function resetInstances()
    {
        throw new Exception("Impelement Me!");
    }
    public function forkMasterInstances()
    {
        throw new Exception("Impelement Me!");
    }
}
class DNSwooleExtAppHolder
{
    use SingletonEx;
    public function getOverrideRootClass()
    {
        throw new Exception("Impelement Me!");
    }
    public function addBeforeRunHandler()
    {
        throw new Exception("Impelement Me!");
    }
    public function getDynamicComponentClasses()
    {
        throw new Exception("Impelement Me!");
    }
    public function getXComponentClasses()
    {
        throw new Exception("Impelement Me!");
    }
}
