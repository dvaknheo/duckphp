<?php
namespace DNMVCS\SwooleHttpd;

use DNMVCS\SwooleHttpd\SwooleSingleton;
use DNMVCS\SwooleHttpd\SwooleHttpd;
use Exception;

class SwooleExt
{
    use SwooleSingleton;
    
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
        
        $options=$options['swoole']??[];
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

        $options['http_handler']=[$this,'runSwoole'];
        SwooleHttpd::G()->init($options, null);

        $this->initApp();
        ($this->appClass)::G()->addBeforeRunHandler([static::class,'OnRun']);
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
        
        SwooleHttpd::G()->run();
        // OK ,we need not return .
        $this->is_error=true;
        throw new Exception('run break;', 500);
    }
    protected function initApp()
    {
        if (is_callable([($this->appClass)::G(),'onSwooleHttpdInit'])) {
            ($this->appClass)::G()->onSwooleHttpdInit(SwooleHttpd::G());
            return;
        }
        ($this->appClass)::G()->system_wrapper_replace(SwooleHttpd::G()->system_wrapper_get_providers());
        SwooleHttpd::G()->http_404_handler=[$this->appClass,'On404'];
        SwooleHttpd::set_exception_handler([$this->appClass,'OnException']);
    }
    public function runSwoole()
    {
        $classes=           ($this->appClass)::G()->getDynamicComponentClasses();
        $exclude_classes=SwooleHttpd::G()->getDynamicComponentClasses();
        SwooleHttpd::G()->forkMasterInstances($classes, $exclude_classes);
        
        $ret=($this->appClass)::G()->run();
        if ($ret) {
            return true;
        }
        if (SwooleHttpd::G()->is_with_http_handler_root()) {
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
