<?php
namespace DNMVCS;

trait DNMVCS_RunMode
{
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
