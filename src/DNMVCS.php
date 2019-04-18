<?php
//dvaknheo@github.com
//OK，Lazy
namespace DNMVCS;

use DNMVCS\Ext;

class DNMVCS extends DNCore
{
    const VERSION = '1.1.0';
    
    use DNClassExt;
    use DNDI;
    use DNMVCS_Glue;
    use DNMVCS_SystemWrapper;
    use DNMVCS_Instance;
    use DNMVCS_Misc;
    
    const DEFAULT_OPTIONS_EX=[
            'path_lib'=>'lib',
            'enable_cache_classes_in_cli'=>true,
            
            'use_session_auto_start'=>false,
            'session_auto_start_name'=>'DNSESSION',
            
            'db_setting_key'=>'database_list',
            'database_list'=>[],
            
            'rewrite_map'=>[],
            'route_map'=>[],
            
            'swoole'=>[],
            
            'extentions'=>[
                'DNDBManager'=>[
                    'use_db'=>true,
                    'use_strict_db'=>false,
                    'db_create_handler'=>null,
                    'db_close_handler'=>null,
                    'database_list'=>[],
                ],
                'Ext\DBReusePoolProxy'=>false,
                'Ext\FacadesAutoLoader'=>false,
                'Ext\FunctionView'=>false,
                'Ext\Lazybones'=>false,
                'Ext\ProjectCommonAutoloader'=>false,
                'Ext\ProjectCommonConfiger'=>false,
                'Ext\RouteHookDirectoryMode'=>false,
                'Ext\RouteHookOneFileMode'=>false,
                'Ext\RouteHookMapAndRewrite'=>true,
            ],
            
        ];
    protected $path_lib=null;
    
    protected function initAfterOverride($options)
    {
        if ($this->options['enable_cache_classes_in_cli'] && PHP_SAPI==='cli') {
            DNAutoLoader::G()->cacheClasses();
        }
        DNSwooleExt::G()->init($options['swoole']??[], $this);
        
        parent::initAfterOverride($options);
        DNSystemWrapperExt::G()->init($this->options, $this);
        DNDBManager::G()->init($this->options, $this);
        $this->initExtentions();
        return $this;
    }
    protected function initExtentions()
    {
        foreach ($this->options['extentions'] as $ext =>$options) {
            if ($options===false) {
                continue;
            }
            $options=($options===true)?$this->options:$options;
            $options=is_string($options)?$this->options[$options]:$options;
            if (substr($ext, 0, 1)!=='\\') {
                $ext=__NAMESPACE__.'\\'.$ext;
            }
            $ext::G()->init($options, $this);
        }
        return;
    }

    public function run()
    {
        DNSwooleExt::G()->onBeforeRun();
        DNSystemWrapperExt::G()->onBeforeRun();
        if ($this->options['use_session_auto_start']) {
            DNMVCS::session_start(['name'=>$this->options['session_auto_start_name']]);
        }
        return parent::run();
    }
    ////
    //for other;
    public static function Import($file)
    {
        return static::G()->_Import($file);
    }
    //// Misc Functions
    public function _Import($file)
    {
        if ($this->path_lib===null) {
            $this->path_lib=$this->path.rtrim($this->options['path_lib'], '/').'/';
        }
        $file=rtrim($file, '.php').'.php';
        require_once($this->path_lib.$file);
    }
    public function checkDBPermission()
    {
        if (!DNMVCS::Developing()) {
            return;
        }
        
        $backtrace=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $caller_class='';
        $base_class=get_class(DNMVCS::G());
        foreach ($backtrace as $i=>$v) {
            if ($v['class']===$base_class) {
                $caller_class=$backtrace[$i+1]['class'];
                break;
            }
        }
        $namespace=DNMVCS::G()->options['namespace'];
        $namespace_controller=DNMVCS::G()->options['namespace_controller'];
        $default_controller_class=DNMVCS::G()->options['default_controller_class'];
        $namespace_controller.='\\';
        do {
            if ($caller_class==$default_controller_class) {
                DNMVCS::ThrowOn(true, "DB Can not Call By Controller");
            }
            if (substr($caller_class, 0, strlen($namespace_controller))==$namespace_controller) {
                DNMVCS::ThrowOn(true, "DB Can not Call By Controller");
            }
            if (substr($caller_class, 0, strlen("$namespace\\Service\\"))=="$namespace\\Service\\") {
                DNMVCS::ThrowOn(true, "DB Can not Call By Service");
            }
            if (substr($caller_class, 0-strlen("Service"))=="Service") {
                DNMVCS::ThrowOn(true, "DB Can not Call By Service");
            }
        } while (false);
    }
    //// RunMode
    public static function RunWithoutPathInfo($options=[])
    {
        $default_options=[
            'ext'=>[
                'mode_onefile'=>true,
                'mode_onefile_key_for_action'=>'_r',
            ],
        ];
        $options=array_replace_recursive($default_options, $options);
        return static::G()->init($options)->run();
    }
    public static function RunOneFileMode($options=[], $init_function=null)
    {
        $path=realpath(getcwd().'/');
        $default_options=[
            'path'=>$path,
            'setting_file_basename'=>'',
            'base_class'=>'',
            'ext'=>[
                'mode_onefile'=>true,
                'mode_onefile_key_for_action'=>'act',
                
                'use_function_dispatch'=>true,
                'use_function_view'=>true,
                
                'use_session_auto_start'=>true,
            ]
        ];
        $options=array_replace_recursive($default_options, $options);
        static::G()->init($options);
        if ($init_function) {
            ($init_function)();
        }
        return static::G()->run();
    }
    public static function RunAsServer($dn_options, $server=null)
    {
        $dn_options['swoole']['swoole_server']=$server;
        return static::G()->init($dn_options)->run();
    }
}
trait DNMVCS_Glue
{
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
    
    /////
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
    /////
    public function assignRewrite($key, $value=null)
    {
        return Ext\RouteHookMapAndRewrite::G()->assignRewrite($key, $value);
    }
    public function assignRoute($key, $value=null)
    {
        return Ext\RouteHookMapAndRewrite::G()->assignRewrite($key, $value);
    }
}
trait DNMVCS_SystemWrapper
{
    public $cookie_handler=null;
    public $exception_handler=null;
    public $shutdown_handler=null;

    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain  = '', bool $secure = false, bool $httponly = false)
    {
        return static::G()->_setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public static function set_exception_handler(callable $exception_handler)
    {
        return static::G()->_set_exception_handler($exception_handler);
    }
    public static function register_shutdown_function(callable $callback, ...$args)
    {
        return static::G()->_register_shutdown_function($callback, ...$args);
    }
    public static function session_start(array $options=[])
    {
        return DNSuperGlobal::G()->session_start($options);
    }
    public static function session_destroy()
    {
        return DNSuperGlobal::G()->session_destroy();
    }
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    {
        return DNSuperGlobal::G()->session_set_save_handler($handler);
    }
    public function _setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain  = '', bool $secure = false, bool $httponly = false)
    {
        if ($this->cookie_handler) {
            return ($this->cookie_handler)($key, $value, $expire, $path, $domain, $secure, $httponly);
        }
        return setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public function _exit_system($code=0)
    {
        if ($this->exit_handler) {
            return ($this->exit_handler)($code);
        }
        exit($code);
    }
    public function _set_exception_handler(callable $exception_handler)
    {
        if ($this->exception_handler) {
            return ($this->exception_handler)($exception_handler);
        }
        return set_exception_handler($exception_handler);
    }
    public function _register_shutdown_function(callable $callback, ...$args)
    {
        if ($this->shutdown_handler) {
            return ($this->shutdown_handler)($callback, ...$args);
        }
        return register_shutdown_function($callback, ...$args);
    }
    public function system_wrapper_replace(array $funcs=[])
    {
        if (isset($funcs['header'])) {
            $this->header_handler=$funcs['header'];
        }
        if (isset($funcs['setcookie'])) {
            $this->cookie_handler=$funcs['setcookie'];
        }
        if (isset($funcs['exit_system'])) {
            $this->exit_handler=$funcs['exit_system'];
        }
        if (isset($funcs['set_exception_handler'])) {
            $this->exception_handler=$funcs['set_exception_handler'];
        }
        if (isset($funcs['register_shutdown_function'])) {
            $this->shutdown_handler=$funcs['register_shutdown_function'];
        }
        
        return true;
    }
    public static function system_wrapper_get_providers():array
    {
        $ret=[
            'header'				=>[static::class,'header'],
            'setcookie'				=>[static::class,'setcookie'],
            'exit_system'			=>[static::class,'exit_system'],
            'set_exception_handler'	=>[static::class,'set_exception_handler'],
            'register_shutdown_function' =>[static::class,'register_shutdown_function'],
            
            'super_global' =>[DNSuperGloabl::class,'G'],
        ];
        return $ret;
    }
}
trait DNMVCS_Instance
{
    protected $dynamicClasses=[];
    protected $dynamicClassesInited=false;
    
    public function getBootInstances()
    {
        $ret=[
            DNAutoLoader::class => DNAutoLoader::G(),
            DNMVCS::class => DNMVCS::G(),
        ];
        $ret[static::class]=$this;
        return $ret;
    }
    protected function initDynamicClasses()
    {
        $this->dynamicClasses=[
            DNRoute::class,   	// for bindServerData,and $this->path_info ,and so on
            DNView::class,   	// for assign
        ];
    }
    public function getDynamicClasses()
    {
        if ($this->dynamicClassesInited) {
            $this->dynamicClassesInited=true;
            $this->initDynamicClasses();
        }
        return $this->dynamicClasses;
    }
    public function addDynamicClass($class)
    {
        if ($this->dynamicClassesInited) {
            $this->dynamicClassesInited=true;
            $this->initDynamicClasses();
        }
        return $this->dynamicClasses[]=$class;
    }
}
trait DNMVCS_Misc
{
    public static function RecordsetUrl(&$data, $cols_map=[])
    {
        return static::G()->_RecordsetUrl($data, $cols_map);
    }
    
    public static function RecordsetH(&$data, $cols=[])
    {
        return static::G()->_RecordsetH($data, $cols);
    }
    public function _RecordsetUrl(&$data, $cols_map=[])
    {
        //need more quickly;
        if ($data===[]) {
            return $data;
        }
        if ($cols_map===[]) {
            return $data;
        }
        $keys=array_keys($data[0]);
        array_walk($keys, function (&$val, $k) {
            $val='{'.$val.'}';
        });
        foreach ($data as &$v) {
            foreach ($cols_map as $k=>$r) {
                $values=array_values($v);
                $v[$k]=static::URL(str_replace($keys, $values, $r));
            }
        }
        unset($v);
        return $data;
    }
    public function _RecordsetH(&$data, $cols=[])
    {
        if ($data===[]) {
            return $data;
        }
        $cols=is_array($cols)?$cols:array($cols);
        if ($cols===[]) {
            $cols=array_keys($data[0]);
        }
        foreach ($data as &$v) {
            foreach ($cols as $k) {
                $v[$k]=static::H($v[$k], ENT_QUOTES);
            }
        }
        return $data;
    }
}
