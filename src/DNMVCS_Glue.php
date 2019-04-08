<?php
namespace DNMVCS;

trait DNMVCS_Glue
{
    public static function ExitJson($ret)
    {
        return static::G()->_ExitJson($ret);
    }
    public static function ExitRedirect($url, $only_in_site=true)
    {
        return static::G()->_ExitRedirect($url, $only_in_site);
    }
    public static function ExitRouteTo($url)
    {
        return static::G()->_ExitRedirect(static::URL($url), true);
    }
    public static function Exit404()
    {
        static::On404();
        static::exit_system();
    }
    ////
    public function assignRewrite($key, $value=null)
    {
        if (is_array($key)&& $value===null) {
            $this->options['rewrite_map']=array_merge($this->options['rewrite_map'], $key);
        } else {
            $this->options['rewrite_map'][$key]=$value;
        }
    }
    public function assignRoute($key, $value=null)
    {
        if (is_array($key)&& $value===null) {
            $this->options['route_map']=array_merge($this->options['route_map'], $key);
        } else {
            $this->options['route_map'][$key]=$value;
        }
    }
    ////
    //////////
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
    /////////////

    public static function DI($name, $object=null)
    {
        return DNMVCSExt::G()->_DI($name, $object);
    }
    public static function InSwoole()
    {
        if (PHP_SAPI!=='cli') {
            return false;
        }
        if (!class_exists('Swoole\Coroutine')) {
            return false;
        }
        
        $cid = \Swoole\Coroutine::getuid();
        if ($cid<=0) {
            return false;
        }
        
        return true;
    }
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
    ///////////////////
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
