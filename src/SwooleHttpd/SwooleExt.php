<?php
namespace DNMVCS\SwooleHttpd;

use DNMVCS\SwooleHttpd\SwooleSingleton;
use DNMVCS\SwooleHttpd\SwooleHttpd;
use Exception;
use Swoole\Coroutine;

class SwooleExt
{
    use SwooleSingleton;
    
    protected $appClass;
    protected $is_inited=false;
    protected $is_error=false;
    protected $in_fake=false;
    
    public function init($options=[], $context=null)
    {
        if (PHP_SAPI!=='cli') {
            return $this;
        }
        if ($this->is_inited) {
            return $this;
        }
        $this->is_inited=true;
        
        $this->appClass=$options['swoolehttpd_app_class']??($context?get_class($context):null);
        
        if (!class_exists(Coroutine::class)) {
            return $this;
        }
        $cid=Coroutine::getuid();
        if ($cid>0) {
            ($this->appClass)::G()->onSwooleHttpdInit(SwooleHttpd::G(), true, null);
            return;
        }
        
        $options=$options['swoole']??[];
        if (empty($options)) {
            return $this;
        }
        
        
        $this->replaceInstances();
        
        $options['http_handler']=[$this,'runSwoole'];
        SwooleHttpd::G()->init($options, null);

        ($this->appClass)::G()->onSwooleHttpdInit(SwooleHttpd::G(), false, [static::class,'OnRun']);
        return $this;
    }
    protected function replaceInstances()
    {
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
            $class=(string)$class;
            $class::G($object);
        }
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
        $cid=Coroutine::getuid();
        if ($cid>0) {
            return;
        }
        
        SwooleHttpd::G()->run();

        // OK ,we need not return .
        $this->is_error=true;
        throw new Exception('run break;', 500);
    }
    public function runSwoole()
    {
        $classes=  ($this->appClass)::G()->getDynamicComponentClasses();
        $exclude_classes=SwooleHttpd::G()->getDynamicComponentClasses();
        SwooleHttpd::G()->forkMasterInstances($classes, $exclude_classes);
        
        $ret=($this->appClass)::G()->run();
        if ($ret) {
            return true;
        }
        if (SwooleHttpd::G()->is_with_http_handler_root()) {
            //SwooleHttpd::G()->forkMasterInstances([get_class(($this->appClass)::G())]);
            SwooleHttpd::G()->forkMasterClassesToNewInstances();
            return false;
        }
        return true; //应该 return false ?
    }
}
