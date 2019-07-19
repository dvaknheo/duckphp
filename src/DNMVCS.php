<?php
// MAIN FILE
//dvaknheo@github.com
//OK，Lazy
namespace DNMVCS;

use DNMVCS\ClassExt;
use DNMVCS\SuperGlobal;
use DNMVCS\Ext\StrictCheck;
use DNMVCS\Ext\DBManager;
use DNMVCS\Ext\RouteHookRewrite;
use DNMVCS\Ext\RouteHookRouteMap;
use DNMVCS\Ext\Pager;

use DNMVCS\Core\App;

class DNMVCS extends App
{
    const VERSION = '1.1.0-rc1';
    use ClassExt;
    
    use DNMVCS_Glue;
    use DNMVCS_SystemWrapper;
    use DNMVCS_Misc;
    
    const DEFAULT_OPTIONS_EX=[
            'path_lib'=>'lib',
            'use_super_global'=>false,
            'db_setting_key'=>'database_list',
            'database_list'=>[],
            
            'rewrite_map'=>[],
            'route_map'=>[],
            'swoole'=>[],
            
            'ext'=>[
                'SwooleHttpd\SwooleExt'=>true,
                'Ext\DBManager'=>[
                    'db_create_handler'=>null,
                    'db_close_handler'=>null,
                    'before_get_db_handler'=>[null,'CheckStrictDB'],
                    //'use_context_database'=>true,
                    //'use_database_list'=>true,
                    'database_list'=>[],
                ],
                'Ext\StrictCheck'=>true,
                
                'Ext\RouteHookRewrite'=>true,
                'Ext\RouteHookRouteMap'=>true,
                
                'Ext\DIExt'=>true,
                
                'Ext\Lazybones'=>false,
                'Ext\DBReusePoolProxy'=>false,
                'Ext\FacadesAutoLoader'=>false,
                
                'Ext\RouteHookDirectoryMode'=>false,
                'Ext\RouteHookOneFileMode'=>false,
                
                'Ext\ProjectCommonAutoloader'=>false,
                'Ext\ProjectCommonConfiger'=>false,
                'Ext\FunctionView'=>false,
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
            'skip_setting_file'=>true,
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
    protected function onRun()
    {
        if (defined('DNMVCS_SUPER_GLOBAL_REPALACER')) {
            $func=DNMVCS_SUPER_GLOBAL_REPALACER;
            SuperGlobal::G($func());
            $this->bindServerData(SuperGlobal::G()->_SERVER);
            
            return;
        }
        if ($this->options['use_super_global']??false) {
            $this->bindServerData(SuperGlobal::G()->_SERVER);
            
            return;
        }
    }
    //@override
    public function getDynamicComponentClasses()
    {
        $ret=parent::getDynamicComponentClasses();
        if (!in_array(SuperGlobal::class, $ret)) {
            $ret[]=SuperGlobal::class;
        }
        return $ret;
    }
}
trait DNMVCS_Glue
{
    //////////////
    public static function SG()
    {
        return SuperGlobal::G();
    }
    public static function &GLOBALS($k, $v=null)
    {
        return SuperGlobal::G()->_GLOBALS($k, $v);
    }
    
    public static function &STATICS($k, $v=null)
    {
        return SuperGlobal::G()->_STATICS($k, $v, 1);
    }
    public static function &CLASS_STATICS($class_name, $var_name)
    {
        return SuperGlobal::G()->_CLASS_STATICS($class_name, $var_name);
    }
    /////
    public static function DB($tag=null)
    {
        return DBManager::G()->_DB($tag);
    }
    public static function DB_W()
    {
        return DBManager::G()->_DB_W();
    }
    public static function DB_R()
    {
        return DBManager::G()->_DB_R();
    }
    public static function Pager()
    {
        return Pager::G();
    }
    /////
    public function assignRewrite($key, $value=null)
    {
        return RouteHookRewrite::G()->assignRewrite($key, $value);
    }
    public function getRewrites()
    {
        return RouteHookRewrite::G()->getRewrites();
    }
    public function assignRoute($key, $value=null)
    {
        return RouteHookRouteMap::G()->assignRoute($key, $value);
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
trait DNMVCS_SystemWrapper
{
    public static function session_start(array $options=[])
    {
        return SuperGlobal::G()->session_start($options);
    }
    public function session_id($session_id=null)
    {
        return SuperGlobal::G()->session_id($session_id);
    }
    public static function session_destroy()
    {
        return SuperGlobal::G()->session_destroy();
    }
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    {
        return SuperGlobal::G()->session_set_save_handler($handler);
    }
}
trait DNMVCS_Misc
{
    protected $path_lib=null;

    public static function Import($file)
    {
        return static::G()->_Import($file);
    }
    public static function RecordsetUrl(&$data, $cols_map=[])
    {
        return static::G()->_RecordsetUrl($data, $cols_map);
    }
    
    public static function RecordsetH(&$data, $cols=[])
    {
        return static::G()->_RecordsetH($data, $cols);
    }
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
