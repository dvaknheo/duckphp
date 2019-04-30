<?php
// MAIN FILE
//dvaknheo@github.com
//OK，Lazy
namespace DNMVCS;

use DNMVCS\Basic\ClassExt;
use DNMVCS\InnerExt\StrictCheck;
use DNMVCS\InnerExt\RouteHookRewrite;
use DNMVCS\InnerExt\RouteHookRouteMap;

use DNMVCS\Glue\GlueDBManager;
use DNMVCS\Glue\GlueSuperGlobal;
use DNMVCS\Glue\GlueForController;


use DNMVCS\Core\App;

class DNMVCS extends App
{
    const VERSION = '1.1.0-dev';
    
    use ClassExt;
    
    use DNMVCS_SystemWrapper;
    use DNMVCS_Misc;

    use GlueDBManager;
    use GlueSuperGlobal;
    use GlueForController;
    use DNMVCS_Glue;
    
    const DEFAULT_OPTIONS_EX=[
            'path_lib'=>'lib',
            
            'db_setting_key'=>'database_list',
            'database_list'=>[],
            
            'rewrite_map'=>[],
            'route_map'=>[],
            'swoole'=>[],
            
            'ext'=>[
                'InnerExt\SwooleExt'=>true,
                'InnerExt\DBManager'=>[
                    'use_db'=>true,
                    'use_strict_db'=>true,
                    'db_create_handler'=>null,
                    'db_close_handler'=>null,
                    'database_list'=>[],
                ],
                'InnerExt\StrictCheck'=>true,
                'InnerExt\SystemWrapperExt'=>true,
                'InnerExt\RouteHookRewrite'=>true,
                'InnerExt\RouteHookRouteMap'=>true,
                'InnerExt\DIExt'=>true,
                
                'Ext\Lazybones'=>false,
                'Ext\DBReusePoolProxy'=>false,
                'Ext\FacadesAutoLoader'=>false,
                'Ext\FunctionView'=>false,
                'Ext\ProjectCommonAutoloader'=>false,
                'Ext\ProjectCommonConfiger'=>false,
                'Ext\RouteHookDirectoryMode'=>false,
                'Ext\RouteHookOneFileMode'=>false,
            ],
            
        ];
    //// RunMode
    public static function RunWithoutPathInfo($options=[])
    {
        $default_options=[
            'ext'=>[
                'Ext\RouteHookOneFileMode'=>[
                    'mode_onefile'=>true,
                    'mode_onefile_key_for_action'=>'_',
                ],
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
            'ext'=>[
                'Ext\RouteHookOneFileMode'=>[
                    'mode_onefile'=>true,
                    'mode_onefile_key_for_action'=>'act',
                     'use_function_dispatch'=>true,
                     'use_function_view'=>true,
                    
                    'use_session_auto_start'=>true,
                ],
            ],
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
trait DNMVCS_SystemWrapper
{
    public $cookie_handler=null;
    public $exception_handler=null;
    public $shutdown_handler=null;
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
    public static function system_wrapper_get_providers():array
    {
        $ret=[
            'header'                =>[static::class,'header'],
            'setcookie'             =>[static::class,'setcookie'],
            'exit_system'           =>[static::class,'exit_system'],
            'set_exception_handler' =>[static::class,'set_exception_handler'],
            'register_shutdown_function' =>[static::class,'register_shutdown_function'],
        ];
        return $ret;
    }
}
trait DNMVCS_Misc
{
    protected $path_lib=null;
    /////////////////////
    public function _Import($file)
    {
        if ($this->path_lib===null) {
            $this->path_lib=$this->path.rtrim($this->options['path_lib'], '/').'/';
        }
        $file=rtrim($file, '.php').'.php';
        require_once($this->path_lib.$file);
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
trait DNMVCS_Glue
{
    public function assignRewrite($key, $value=null)
    {
        return RouteHookRewrite::G()->assignRewrite($key, $value);
    }
    public function assignRoute($key, $value=null)
    {
        return RouteHookRouteMap::G()->assignRoute($key, $value);
    }
    public function getRewrites()
    {
        return RouteHookRewrite::G()->getRewrites();
    }
    public function getRoutes()
    {
        return RouteHookRouteMap::G()->getRoutes();
    }
    /////
    public static function CheckStrictDB($tag)
    {
        //3 = DB,_DB,CheckStrictDB
        return static::G()->checkStrictComponent('DB', 3);
    }
    public function checkStrictComponent($component_name, $trace_level)
    {
        return StrictCheck::G()->checkStrictComponent($component_name, $trace_level+1);
    }
    public function checkStrictService($trace_level=2)
    {
        return StrictCheck::G()->checkStrictService($trace_level+1);
    }
    public function checkStrictModel($trace_level=2)
    {
        return StrictCheck::G()->checkStrictModel($trace_level+1);
    }
}
