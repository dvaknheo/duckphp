<?php
// MAIN FILE
//dvaknheo@github.com
//OK，Lazy
namespace DNMVCS\SwooleHttpd;

use DNMVCS\SwooleHttpd\SwooleSingleton;

use DNMVCS\SwooleHttpd\SimpleWebSocketd;

use DNMVCS\SwooleHttpd\SwooleHttpd_Static;
use DNMVCS\SwooleHttpd\SwooleHttpd_SystemWrapper;
use DNMVCS\SwooleHttpd\SwooleHttpd_SuperGlobal;
use DNMVCS\SwooleHttpd\SwooleHttpd_Singleton;
use DNMVCS\SwooleHttpd\SwooleHttpd_Handler;
//use DNMVCS\SwooleHttpd\SwooleExtServerInterface;

use Swoole\ExitException;
use Swoole\Http\Server as Http_Server;
use Swoole\WebSocket\Server as Websocket_Server;
use Swoole\Runtime;
use Swoole\Coroutine;

class SwooleHttpd //implements SwooleExtServerInterface
{
    const VERSION = '1.1.3';
    use SwooleSingleton;
    
    use SwooleHttpd_SimpleHttpd;
    use SimpleWebSocketd;
    
    use SwooleHttpd_Handler;
    use SwooleHttpd_Glue;
    use SwooleHttpd_SystemWrapper;
    
    use SwooleHttpd_Singleton;
    
    
    const DEFAULT_OPTIONS=[
            'swoole_server'=>null,
            'swoole_server_options'=>[],
            'host'=>'127.0.0.1',
            'port'=>0,
            
            'http_handler'=>null,
            'http_handler_basepath'=>'',
            'http_handler_root'=>null,
            'http_handler_file'=>null,
            'http_exception_handler'=>null,
            'http_404_handler'=>null,
            
            'with_http_handler_root'=>false,
            'with_http_handler_file'=>false,
            
            'enable_fix_index'=>true,
            'enable_path_info'=>true,
            'enable_not_php_file'=>true,
            
            'websocket_open_handler'=>null,
            'websocket_handler'=>null,
            'websocket_exception_handler'=>null,
            'websocket_close_handler'=>null,
            
            'base_class'=>'',
            'silent_mode'=>false,
            'enable_coroutine'=>true,
        ];
    const MAX_PATH_LEVEL=1000;
    public $server=null;
    
    public $http_handler=null;
    public $http_handler_basepath=null;
    public $http_handler_root=null;
    public $http_handler_file=null;
    public $http_exception_handler=null;
    public $http_404_handler=null;
    protected $with_http_handler_root=false;
    protected $with_http_handler_file=false;
    public $enable_fix_index=true;
    public $enable_path_info=true;
    public $enable_not_php_file=true;

    public $silent_mode=false;

    protected $static_root=null;
    protected $auto_clean_autoload=true;
    protected $old_autoloads=[];
    
    public $is_shutdown=false;
    
    public $skip_override=false;
    public static function RunQuickly(array $options=[], callable $after_init=null)
    {
        if (!$after_init) {
            return static::G()->init($options)->run();
        }
        static::G()->init($options);
        ($after_init)();
        return static::G()->run();
    }
    public function set_http_exception_handler($exception_handler)
    {
        $this->http_exception_handler=$exception_handler;
    }
    public function set_http_404_handler($http_404_handler)
    {
        $this->http_404_handler=$http_404_handler;
    }
    public function is_with_http_handler_root()
    {
        return $this->with_http_handler_root;
    }

    public function exit_request($code=0)
    {
        exit($code);
    }
    public static function Throw404()
    {
        throw new Swoole404Exception();
    }
    public static function ThrowOn($flag, $message, $code=0)
    {
        if (!$flag) {
            return;
        }
        throw new SwooleException($message, $code);
    }
    
    protected function fixIndex()
    {
        $index_file='index.php';
        $index_path='/'.$index_file;
        $path_info=static::SG()->_SERVER['PATH_INFO'];
        if (substr($path_info, 0, strlen($index_path))===$index_path) {
            if (strlen($path_info)===strlen($index_path)) {
                static::SG()->_SERVER['PATH_INFO']='';
            } else {
                if ($index_path.'/'===substr($path_info, 0, strlen($index_path)+1)) {
                    static::SG()->_SERVER['PATH_INFO']=substr($path_info, strlen($index_path)+1);
                }
            }
        }
    }
    
    protected function onHttpRun($request, $response)
    {
        $this->old_autoloads = spl_autoload_functions();
        if ($this->http_handler) {
            $this->auto_clean_autoload=false;
            if ($this->enable_fix_index) {
                $this->fixIndex();
            }
            
            $flag=($this->http_handler)();
            if ($flag) {
                return;
            }
            if (!$this->with_http_handler_root && !$this->http_handler_file) {
                static::Throw404();
                return;
            }
            $this->auto_clean_autoload=true;
        }
        if ($this->http_handler_root) {
            list($path, $document_root)=$this->prepareRootMode();
            $flag=$this->runHttpFile($path, $document_root);
            if ($flag) {
                return;
            }
            if (!$this->with_http_handler_file || $this->http_handler) {
                static::Throw404();
                return;
            }
        }
        if ($this->http_handler_file) {
            $path_info=SwooleSuperGlobal::G()->_SERVER['REQUEST_URI'];
            $file=$this->http_handler_basepath.$this->http_handler_file;
            $document_root=dirname($file);
            $this->includeHttpPhpFile($file, $document_root, $path_info);
            return;
        }
    }
    protected function prepareRootMode()
    {
        $http_handler_root=$this->http_handler_basepath.$this->http_handler_root;
        $http_handler_root=rtrim($http_handler_root, '/').'/';
        
        $document_root=$this->static_root?:rtrim($http_handler_root, '/');
        $path=parse_url(SwooleSuperGlobal::G()->_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        return [$path,$document_root];
    }
    
    protected function runHttpFile($path, $document_root)
    {
        if (strpos($path, '/../')!==false || strpos($path, '/./')!==false) {
            return false;
        }
        
        $full_file=$document_root.$path;
        if ($path==='/') {
            $this->includeHttpPhpFile($document_root.'/index.php', $document_root, '');
            return true;
        }
        if (is_file($full_file)) {
            $this->includeHttpFullFile($full_file, $document_root, '');
            return true;
        }
        if (!$this->enable_path_info) {
            if (is_dir($full_file)) {
                $full_file=rtrim($full_file, '/').'/index.php';
                if (is_file($full_file)) {
                    $this->includeHttpFullFile($full_file, $document_root, '');
                    return true;
                }
            }
            return false;
        }
        $max=static::MAX_PATH_LEVEL;
        $offset=0;
        for ($i=0;$i<$max;$i++) {
            $offset=strpos($path, '.php/', $offset);
            if (false===$offset) {
                break;
            }
            $file=substr($path, 0, $offset).'.php';
            $path_info=substr($path, $offset+strlen('.php'));
            $file=$document_root.$file;
            if (is_file($file)) {
                $this->includeHttpPhpFile($file, $document_root, $path_info);
                return true;
            }
            
            $offset++;
        }
        
        $dirs=explode('/', $path);
        $prefix='';
        foreach ($dirs as $block) {
            $prefix.=$block.'/';
            $file=$document_root.$prefix.'index.php';
            if (is_file($file)) {
                $path_info=substr($path, strlen($prefix)-1);
                $this->includeHttpPhpFile($file, $document_root, $path_info);
                return true;
            }
        }
        return false;
    }
    protected function includeHttpFullFile($full_file, $document_root, $path_info='')
    {
        $ext=pathinfo($full_file, PATHINFO_EXTENSION);
        if ($ext==='php') {
            $this->includeHttpPhpFile($full_file, $document_root, $path_info);
            return;
        }
        if (!$this->enable_not_php_file) {
            return;
        }
        $mime=mime_content_type($full_file);
        static::Response()->header('Content-Type', $mime);
        static::Response()->sendfile($full_file);
        return;
    }
    protected function includeHttpPhpFile($file, $document_root, $path_info)
    {
        SwooleSuperGlobal::G()->_SERVER['PATH_INFO']=$path_info;
        SwooleSuperGlobal::G()->_SERVER['DOCUMENT_ROOT']=$document_root;
        SwooleSuperGlobal::G()->_SERVER['SCRIPT_FILENAME']=$file;
        chdir(dirname($file));
        (function ($file) {
            include $file;
        })($file);
    }
    protected function onHttpException($ex)
    {
        if ($ex instanceof ExitException) {
            return;
        }
        if ($ex instanceof Swoole404Exception) {
            static::OnShow404();
            return;
        }
        static::OnException($ex);
    }
    protected function onHttpClean()
    {
        if (!$this->auto_clean_autoload) {
            return;
        }
        $functions = spl_autoload_functions();
        $this->old_autoloads=$this->old_autoloads?:[];
        $functions=is_array($functions)?$functions:[];
        foreach ($functions as $function) {
            if (in_array($function, $this->old_autoloads)) {
                continue;
            }
            spl_autoload_unregister($function);
        }
    }
    protected function check_swoole()
    {
        if (!function_exists('swoole_version')) {
            echo 'DNMVCS swoole mode: PHP Extension swoole needed;';
            exit;
        }
        if (version_compare(swoole_version(), '4.2.0', '<')) {
            echo 'DNMVCS swoole mode: swoole >=4.2.0 needed;';
            exit;
        }
    }
    /////////////////////////
    protected function checkOverride($options)
    {
        if ($this->skip_override) {
            return null;
        }
        $base_class=isset($options['base_class'])?$options['base_class']:self::DEFAULT_OPTIONS['base_class'];
        $base_class=ltrim($base_class, '\\');
        
        if (!$base_class || !class_exists($base_class)) {
            return null;
        }
        if (static::class===$base_class) {
            return null;
        }
        return static::G($base_class::G());
    }
    
    public function init(array $options, $server=null)
    {
        $object=$this->checkOverride($options);
        if ($object) {
            $object->skip_override=true;
            return $object->init($options);
        }
        
        $options=array_merge(self::DEFAULT_OPTIONS, $options);
        
        $this->http_handler=$options['http_handler'];
        $this->http_handler_basepath=$options['http_handler_basepath'];
        $this->http_handler_root=$options['http_handler_root'];
        $this->http_handler_file=$options['http_handler_file'];
        $this->http_exception_handler=$options['http_exception_handler'];
        $this->http_404_handler=$options['http_404_handler'];
        
        $this->with_http_handler_root=$options['with_http_handler_root'];
        $this->with_http_handler_file=$options['with_http_handler_file'];
        
        $this->enable_fix_index=$options['enable_fix_index'];
        $this->enable_path_info=$options['enable_path_info'];
        $this->enable_not_php_file=$options['enable_not_php_file'];
        
        $this->server=$options['swoole_server'];
        
        $this->silent_mode=$options['silent_mode'];
        
        $this->http_handler_basepath=rtrim((string)realpath($this->http_handler_basepath), '/').'/';
        
        if (!$this->server) {
            $this->check_swoole();
            
            if (!$options['port']) {
                echo 'SwooleHttpd: No port ,set the port';
                exit;
            }
            if (!$options['websocket_handler']) {
                $this->server=new Http_Server($options['host'], $options['port']);
            } else {
                echo "SwooleHttpd: use WebSocket\n";
                $this->server=new Websocket_Server($options['host'], $options['port']);
            }
        }
        if ($options['swoole_server_options']) {
            $this->server->set($options['swoole_server_options']);
        }
        
        $this->server->on('request', [$this,'onRequest']);
        if ($this->server->setting['enable_static_handler']??false) {
            $this->static_root=$this->server->setting['document_root'];
        }
        
        $this->websocket_open_handler=$options['websocket_open_handler'];
        $this->websocket_handler=$options['websocket_handler'];
        $this->websocket_exception_handler=$options['websocket_exception_handler'];
        $this->websocket_close_handler=$options['websocket_close_handler'];
        
        if ($this->websocket_handler) {
            $this->server->set(['open_websocket_close_frame'=>true]);
            $this->server->on('mesage', [$this,'onMessage']);
            $this->server->on('open', [$this,'onOpen']);
        }
        if ($options['enable_coroutine']) {
            Runtime::enableCoroutine();
        }
        
        SwooleCoroutineSingleton::ReplaceDefaultSingletonHandler();
        static::G($this);
        //SwooleSuperGlobal::G(); NoNeed;
        
        if (!defined('DNMVCS_SYSTEM_WRAPPER_INSTALLER')) {
            define('DNMVCS_SYSTEM_WRAPPER_INSTALLER', static::class .'::' .'system_wrapper_get_providers');
        }
        if (!defined('DNMVCS_SUPER_GLOBAL_REPALACER')) {
            define('DNMVCS_SUPER_GLOBAL_REPALACER', SwooleSuperGlobal::class .'::' .'G');
        }
        
        return $this;
    }
    public function run()
    {
        if (!$this->silent_mode) {
            fwrite(STDOUT, "[".DATE(DATE_ATOM)."] ".get_class($this)." run at http://".$this->server->host.':'.$this->server->port."/ ...\n");
        }
        $this->server->start();
        if (!$this->silent_mode) {
            fwrite(STDOUT, get_class($this)." run end ".DATE(DATE_ATOM)." ...\n");
        }
    }
}
trait SwooleHttpd_SimpleHttpd
{

    // en...
    public function initHttp($request, $response)
    {
        SwooleContext::G(new SwooleContext())->initHttp($request, $response);
    }
    protected function deferGC()
    {
        Coroutine::defer(
            function () {
                gc_collect_cycles();
            }
        );
    }
    protected function checkShutdown()
    {
        if (!$this->is_shutdown) {
            return;
        }
        throw new \Exception("Shutdowning".date(DATE_ATOM));
    }
    public function onRequest($request, $response)
    {
        $this->deferGC();
        SwooleCoroutineSingleton::EnableCurrentCoSingleton(); // remark ,here has a defer
        
        $this->checkShutdown();
        
        Coroutine::defer(
            function () {
                $InitObLevel=0;
                for ($i=ob_get_level();$i>$InitObLevel;$i--) {
                    ob_end_flush();
                }
                SwooleContext::G()->cleanUp();
            }
        );
        Coroutine::defer(
            function () {
                SwooleContext::G()->onShutdown();
            }
        );
        ob_start(
            function ($str) {
                if (''===$str) {
                    return;
                }
                SwooleContext::G()->response->end($str);
            }
        );
        $this->initHttp($request, $response);
        SwooleSuperGlobal::G(new SwooleSuperGlobal())->mapToGlobal();
        try {
            $this->onHttpRun($request, $response);
        } catch (\Throwable $ex) {
            $this->onHttpException($ex);
        }
        $this->onHttpClean();
    }
}

trait SwooleHttpd_Handler
{
    public static function OnShow404()
    {
        return static::G()->_OnShow404();
    }
    public static function OnException($ex)
    {
        return static::G()->_OnException($ex);
    }
    public function _OnShow404()
    {
        if ($this->http_404_handler) {
            ($this->http_404_handler)();
            return;
        }
        static::header('', true, 404);
        echo "DNMVCS swoole mode: Server 404 \n";
    }
    public function _OnException($ex)
    {
        if ($this->http_exception_handler) {
            ($this->http_exception_handler)($ex);
            return;
        }
        static::header('', true, 500);
        echo "DNMVCS swoole mode: Server Error. \n";
        echo var_export($ex);
    }
}
trait SwooleHttpd_Glue
{
    public static function Server()
    {
        return static::G()->server;
    }
    public static function Request()
    {
        return SwooleContext::G()->request;
    }
    public static function Response()
    {
        return SwooleContext::G()->response;
    }
    public static function Frame()
    {
        return SwooleContext::G()->frame;
    }
    public static function FD()
    {
        return SwooleContext::G()->fd;
    }
    public static function IsClosing()
    {
        return SwooleContext::G()->isWebSocketClosing();
    }
    /////////////
    public static function SG($replacement_object=null)
    {
        return SwooleSuperGlobal::G($replacement_object);
    }
    public static function &GLOBALS($k, $v=null)
    {
        return SwooleSuperGlobal::G()->_GLOBALS($k, $v);
    }
    public static function &STATICS($k, $v=null)
    {
        return SwooleSuperGlobal::G()->_STATICS($k, $v, 1);
    }
    public static function &CLASS_STATICS($class_name, $var_name)
    {
        return SwooleSuperGlobal::G()->_CLASS_STATICS($class_name, $var_name);
    }
}
trait SwooleHttpd_SystemWrapper
{
    public static function header(string $string, bool $replace = true, int $http_status_code =0)
    {
        return SwooleContext::G()->header($string, $replace, $http_status_code);
    }
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain  = '', bool $secure = false, bool $httponly = false)
    {
        return SwooleContext::G()->setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public static function exit_system($code=0)
    {
        return static::G()->exit_request($code);
    }
    public static function set_exception_handler(callable $exception_handler)
    {
        return static::G()->set_http_exception_handler($exception_handler);
    }
    public static function register_shutdown_function(callable $callback, ...$args)
    {
        return SwooleContext::G()->regShutDown(func_get_args());
    }
    
    public static function session_start(array $options=[])
    {
        return SwooleSuperGlobal::G()->session_start($options);
    }
    public static function session_destroy()
    {
        return SwooleSuperGlobal::G()->session_destroy();
    }
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    {
        return SwooleSuperGlobal::G()->session_set_save_handler($handler);
    }
    
    public static function system_wrapper_get_providers():array
    {
        $ret=[
            'header'                =>[static::class,'header'],
            'setcookie'                =>[static::class,'setcookie'],
            'exit_system'            =>[static::class,'exit_system'],
            'set_exception_handler'    =>[static::class,'set_exception_handler'],
            'register_shutdown_function' =>[static::class,'register_shutdown_function'],
        ];
        return $ret;
    }
}
trait SwooleHttpd_Singleton
{
    public static function ReplaceDefaultSingletonHandler()
    {
        return SwooleCoroutineSingleton::ReplaceDefaultSingletonHandler();
    }
    public static function EnableCurrentCoSingleton()
    {
        return SwooleCoroutineSingleton::EnableCurrentCoSingleton();
    }
    public function getStaticComponentClasses()
    {
        return [];
    }
    public function getDynamicComponentClasses()
    {
        $classes=[
            SwooleSuperGlobal::class,
            SwooleContext::class,
        ];
        return $classes;
    }
    //
    public function forkMasterInstances($classes, $exclude_classes=[])
    {
        return SwooleCoroutineSingleton::G()->forkMasterInstances($classes, $exclude_classes);
    }
    public function forkMasterClassesToNewInstances()
    {
        $classes=$this->getDynamicComponentClasses();
        $instances=[];
        foreach ($classes as $class) {
            $instances[$class]=$class::G();
        }
        
        SwooleCoroutineSingleton::G()->forkAllMasterClasses();
        
        foreach ($classes as $class) {
            $class::G($instances[$class]);
        }
    }
}
