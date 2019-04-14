<?php
namespace DNMVCS;

use SwooleHttpd\SwooleHttpd;
use Exception;

class DNSwooleExtServerHolder
{
    use DNSingleton;
    
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
    public function getBootInstances()
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
    use DNSingleton;
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
    public function getBootInstances()
    {
        throw new Exception("Impelement Me!");
    }
}
class DnSwooleExtReuserHolder
{
    use DNSingleton;

    public $appClass;
    public function init($options=[], $context=null)
    {
        //for 404 re-in;
        $class=get_class(([$this->appClass,'G'])());
        if ($class!==static::class) {
            return $this;
        }
        DNSwooleExt::Server()->resetInstances();
        
        $ret=([$this->appClass,'G'])()->init($options);
        return $ret;
    }
}
class DNSwooleExt
{
    use DNSingleton;
    
    protected $with_http_handler_root=false;
    protected $appClass;
    protected $is_server_running=false;
    
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
        $app=([$class,'G'])();
        
        static::App($app);
        
        $this->appClass=$class;
    }
    public function init($options=[], $context=null)
    {
        if (PHP_SAPI!=='cli') {
            return;
        }
        if ($context) {
            $app_class=$context->root_class;
            $this->setAppClass($app_class);
            $context->options['error_404']=[static,'_EmptyFunction'];
            $context->options['use_super_global']=true;
        }
        $server_object=SwooleHttpd::G();
        //static::ThrowOn(!class_exists(SwooleHttpd::class), "DNMVCS: You Need SwooleHttpd");
        static::Server($server_object);
        $server=static::Server();
        $app=static::App();
        
        $instances=$app->getBootInstances();
        $flag=([get_class($server),'ReplaceDefaultSingletonHandler'])();
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
    }
    public function onAppBeforeRun()
    {
        if ($this->is_server_running) {
            return;
        };
        $this->is_server_running=true;
        static::Server()->run();
    }
    public function runSwoole()
    {
        $classes=static::App()->getDynamicClasses();
        $exclude_classes=static::Server()->getDynamicClasses();
        static::Server()->forkMasterInstances($classes, $exclude_classes);
        
        $ret=static::App()->run();
        if (!$ret && $this->with_http_handler_root) {
            static::Server()->forkMasterInstances(array_keys(static::App()->getBootInstances()));

            DnSwooleExtReuserHolder::G()->appClass=$this->appClass;
            ([$this->appClass,'G'])(DnSwooleExtReuserHolder::G()); //fake object
            return false;
        }
        return true;
    }
}
