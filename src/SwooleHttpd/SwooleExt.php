<?php
namespace DNMVCS\SwooleHttpd;

use DNMVCS\SwooleHttpd\SwooleSingleton;
use DNMVCS\SwooleHttpd\SwooleHttpd;
use Exception;

class SwooleExt
{
    use SwooleSingleton;
    
    protected $with_http_handler_root=false;
    protected $appClass;
    protected $is_inited=false;
    protected $is_error=false;
    protected $in_fake=false;
    
    public static function _EmptyFunction()
    {
        return;
    }
    public function init($options=[], $context=null)
    {
        if ($this->in_fake) {
            $cid = \Swoole\Coroutine::getuid();
            if ($cid>0) {
                return $this->doFakerInit($options, $context);
            }
        }
        if (PHP_SAPI!=='cli') {
            return;
        }
        if ($this->is_inited) {
            return $this;
        }
        $this->is_inited=true;
        
        $options=$options['swoole'];
        if (empty($options)) {
            return;
        }
        
        $this->appClass=$options['swoolehttpd_app_class']??($context?get_class($context):null);
        
        
        //////////////
        $server=SwooleHttpd::G();
        $classes=($this->appClass)::G()->getStaticComponentClasses();
        $instances=[];
        foreach ($classes as $class) {
            $instances[$class]=$class::G();
        }
        $flag=SwooleHttpd::ReplaceDefaultSingletonHandler();
        if (!$flag) {
            return;
        }
        
        // replace G method again;
        static::G($this);
        SwooleHttpd::G($server);
        foreach ($instances as $class=>$object) {
            $class::G($object);
        }
        //////////////
        ($this->appClass)::G()->addBeforeRunHandler([static::class,'OnRun']);
        $this->with_http_handler_root=$options['with_http_handler_root']??false;
        $options['http_handler']=[$this,'runSwoole'];
        SwooleHttpd::G()->init($options, null);
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
        $this->initApp();
        
        SwooleHttpd::G()->run();
        // OK ,we need not return .
        $this->is_error=true;
        throw new Exception('run break;', 500);
    }
    protected function initApp()
    {
        ($this->appClass)::G()->options['error_404']=[static::class,'_EmptyFunction']; // do not double 404;
        ($this->appClass)::G()->options['use_super_global']=true;
        $callback=DNMVCS_SYSTEM_WRAPPER_INSTALLER;
        $funcs=($callback)();
        ($this->appClass)::G()->system_wrapper_replace($funcs);
        
        if (isset($funcs['set_exception_handler'])) {
            SwooleHttpd::set_exception_handler([$this->appClass,'OnException']);
        }
    }
    public function runSwoole()
    {
        $classes=           ($this->appClass)::G()->getDynamicComponentClasses();
        $exclude_classes=SwooleHttpd::G()->getDynamicComponentClasses();
        SwooleHttpd::G()->forkMasterInstances($classes, $exclude_classes);
        
        $ret=($this->appClass)::G()->run();
        if (!$ret && $this->with_http_handler_root) {
            $classes=($this->appClass)::G()->getStaticComponentClasses();
            SwooleHttpd::G()->forkMasterInstances($classes);
            $this->in_fake=true;
            ($this->appClass)::G($this); //fake object
            return false;
        }
        return true;
    }
    protected function doFakerInit($options=[], $context=null)
    {
        $this->in_fake=false;
        SwooleHttpd::G()->resetInstances();
        $class=$this->appClass;
        $ret=($this->appClass)::G(new $class())->init($options);
        
        return $ret;
    }
}
