<?php
namespace DNMVCS\SwooleHttpd;

use DNMVCS\SwooleHttpd\SwooleSingleton;
use DNMVCS\SwooleHttpd\SwooleSessionHandler;
use Swoole\Coroutine;

class SwooleSuperGlobal
{
    use SwooleSingleton;
    
    public $_GET;
    public $_POST;
    public $_REQUEST;
    public $_SERVER=[];
    public $_ENV;
    public $_COOKIE=[];
    public $_SESSION;
    public $_FILES=[];
    
    public $GLOBALS=[];
    public $STATICS=[];
    public $CLASS_STATICS=[];

    protected $session_handler=null;
    protected $session_id=null;
    protected $session_name='';
    protected $sessionOptions=[];
    
    protected $is_session_started=false;
    
    public $is_inited=false;
    public function __construct()
    {
        $this->init();
    }
    public function init()
    {
        $cid=Coroutine::getuid();
        if ($cid<=0) {
            return;
        }
        
        if ($this->is_inited) {
            return $this;
        }
        $this->is_inited=true;
        
        $request=SwooleHttpd::Request();
        if (!$request) {
            return;
        }
        
        $this->_GET=$request->get??[];
        $this->_POST=$request->post??[];
        $this->_COOKIE=$request->cookie??[];
        $this->_REQUEST=array_merge($request->get??[], $request->post??[]);
        $this->_ENV=&$_ENV;
        
        $this->_SERVER=$_SERVER;
        if (isset($this->_SERVER['argv'])) {
            $this->_SERVER['cli_argv']=$this->_SERVER['argv'];
            unset($this->_SERVER['argv']);
        }
        if (isset($this->_SERVER['argc'])) {
            $this->_SERVER['cli_argc']=$this->_SERVER['argc'];
            unset($this->_SERVER['argc']);
        }
        foreach ($request->header as $k=>$v) {
            $k='HTTP_'.str_replace('-', '_', strtoupper($k));
            $this->_SERVER[$k]=$v;
        }
        foreach ($request->server as $k=>$v) {
            $this->_SERVER[strtoupper($k)]=$v;
        }
        $this->_SERVER['cli_script_filename']=$this->_SERVER['SCRIPT_FILENAME']??'';
        
        $this->_FILES=$request->files;
        
        // fixed swoole system bug
        if (!empty($this->_GET)) {
            $this->_SERVER['REQUEST_URI'].='?'.http_build_query($this->_GET);
        }
        
        return $this;
    }
    public function mapToGlobal()
    {
        $_GET       =&$this->_GET;
        $_POST      =&$this->_POST;
        $_REQUEST   =&$this->_REQUEST;
        $_SERVER    =&$this->_SERVER;
        // $_ENV       =&$this->_ENV; no need
        $_COOKIE    =&$this->_COOKIE;
        $_SESSION   =&$this->_SESSION;
        $_FILES     =&$this->_FILES;
    }

    public function &_GLOBALS($k, $v=null)
    {
        if (!isset($this->GLOBALS[$k])) {
            $this->GLOBALS[$k]=$v;
        }
        return $this->GLOBALS[$k];
    }
    public function &_STATICS($name, $value=null, $parent=0)
    {
        $t=debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, $parent+2)[$parent+1]??[]; //todo Coroutine trace ?
        $k='';
        $k.=isset($t['object'])?'object_'.spl_object_hash($t['object']):'';
        $k.=$t['class']??'';
        $k.=$t['type']??'';
        $k.=$t['function']??'';
        $k.=$k?'$':'';
        $k.=$name;
        
        if (!isset($this->STATICS[$k])) {
            $this->STATICS[$k]=$value;
        }
        return $this->STATICS[$k];
    }
    public function &_CLASS_STATICS($class_name, $var_name)
    {
        $k=$class_name.'::$'.$var_name;
        if (!isset($this->CLASS_STATICS[$k])) {
            $ref=new \ReflectionClass($class_name);
            $reflectedProperty = $ref->getProperty($var_name);
            $reflectedProperty->setAccessible(true);
            $this->CLASS_STATICS[$k]=$reflectedProperty->getValue();
        }
        return $this->CLASS_STATICS[$k];
    }
    ////////////////////////////
    public function session_set_save_handler($handler)
    {
        return $this->session_handler=$handler;
    }
    protected function getSessionOption($key)
    {
        return $this->sessionOptions[$key]??ini_get('session.'.$key);
    }
    protected function getSessionId()
    {
        $session_name=$this->getSessionOption('name');
        
        $cookies=SwooleHttpd::Request()->cookie??[];
        $session_id=$cookies[$session_name]??null;
        if ($session_id===null || ! preg_match('/[a-zA-Z0-9,-]+/', $session_id)) {
            $session_id=$this->create_sid();
        }
        
        SwooleHttpd::setcookie(
            $session_name,
            $session_id,
            $this->getSessionOption('cookie_lifetime')?time()+$this->getSessionOption('cookie_lifetime'):0,
            $this->getSessionOption('cookie_path'),
            $this->getSessionOption('cookie_domain'),
            $this->getSessionOption('cookie_secure'),
            $this->getSessionOption('cookie_httponly')
        );
        return $session_id;
    }
    protected function deleteSessionId()
    {
        $session_name=$this->getSessionOption('name');
        SwooleHttpd::setcookie($session_name, '');
        $this->session_id=null;
    }
    protected function registWriteClose()
    {
        //SwooleHttpd::register_shutdown_function([$this,'writeClose']);
        $self=$this;
        Coroutine::defer(
            function () use ($self) {
                $self->writeClose();
            }
        );
    }
    public function session_start(array $options=[])
    {
        if (!$this->session_handler) {
            $this->session_handler=SwooleSessionHandler::G();
        }
        $this->is_session_started=true;
        $this->sessionOptions=$options;
        $this->registWriteClose();
        $session_name=$this->getSessionOption('name');
        $session_save_path=session_save_path();
        $this->session_id=$this->session_id??$this->getSessionId();
        
        if ($this->getSessionOption('gc_probability') > mt_rand(0, $this->getSessionOption('gc_divisor'))) {
            $this->session_handler->gc($this->getSessionOption('gc_maxlifetime'));
        }
        $this->session_handler->open($session_save_path, $session_name);
        $raw=$this->session_handler->read($this->session_id);
        $this->_SESSION=unserialize($raw);
        if (!$this->_SESSION) {
            $this->_SESSION=[];
        }
    }
    public function session_id($session_id=null)
    {
        if (isset($session_id)) {
            $this->session_id=$session_id;
        }
        return $this->session_id;
    }
    public function session_destroy()
    {
        $this->session_handler->destroy($this->session_id);
        $this->_SESSION=[];
        $this->deleteSessionId();
        $this->is_session_started=false;
    }
    public function writeClose()
    {
        if (!$this->is_session_started) {
            return;
        }
        $this->session_handler->write($this->session_id, serialize($this->_SESSION));
        $this->_SESSION=[];
    }
    public function create_sid()
    {
        $cid=Coroutine::getuid();
        return md5(microtime().' '.$cid.' '.mt_rand());
    }
}
