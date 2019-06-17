<?php
namespace DNMVCS\InnerExt;

use DNMVCS\SwooleHttpd\SingletonEx;
use DNMVCS\SwooleHttpd\SwooleHttpd;
use Exception;

class SwooleExt
{
    use SingletonEx;
    
    protected $with_http_handler_root=false;
    protected $serverClass;
    protected $appClass;
    protected $is_inited=false;
    protected $is_error=false;
    public static function _EmptyFunction()
    {
        return;
    }
    public function setAppClass($class)
    {
        $this->appClass=$class;
    }
    public function setServerClass($class)
    {
        $this->serverClass=$class;
    }
    public function init($options=[], $context=null)
    {
        if ($this->with_http_handler_root) {
            //fakeobject
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
        
        $this->serverClass=$options['swoolehttpd_server_class']??SwooleHttpd::class;
        $this->appClass=$options['swoolehttpd_app_class']??($context?get_class($context):null);
        
        
        //////////////
        $server=($this->serverClass)::G();
        $classes=($this->appClass)::G()->getStaticComponentClasses();
        $instances=[];
        foreach ($classes as $class) {
            $instances[$class]=$class::G();
        }
        $flag=($this->serverClass)::ReplaceDefaultSingletonHandler();
        if (!$flag) {
            return;
        }
        
        // replace G method again;
        static::G($this);
        ($this->serverClass)::G($server);
        foreach ($instances as $class=>$object) {
            $class::G($object);
        }
        //////////////
        ($this->appClass)::G()->addBeforeRunHandler([static::class,'OnRun']);
        $this->with_http_handler_root=$options['with_http_handler_root']??false;
        $options['http_handler']=[$this,'runSwoole'];
        ($this->serverClass)::G()->init($options, null);
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
        ($this->appClass)::G()->options['error_404']=[static::class,'_EmptyFunction'];
        ($this->appClass)::G()->options['use_super_global']=true;
        ($this->serverClass)::G()->run();
        // OK ,we need not return .
        $this->is_error=true;
        throw new Exception('run break;',500);
    }
    public function runSwoole()
    {
        $classes=           ($this->appClass)::G()->getDynamicComponentClasses();
        $exclude_classes=($this->serverClass)::G()->getDynamicComponentClasses();
        ($this->serverClass)::G()->forkMasterInstances($classes, $exclude_classes);
        
        $ret=($this->appClass)::G()->run();
        if (!$ret && $this->with_http_handler_root) {
            $classes=($this->appClass)::G()->getStaticComponentClasses();
            ($this->serverClass)::G()->forkMasterInstances($classes);
            ($this->appClass)::G($this); //fake object
            return false;
        }
        return true;
    }
    protected function doFakerInit($options=[], $context=null)
    {
        ($this->serverClass)::G()->resetInstances();
        $class=$this->appClass;
        $ret=($this->appClass)::G(new $class())->init($options);
        return $ret;
    }
}
