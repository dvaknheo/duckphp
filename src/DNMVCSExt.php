<?php
namespace DNMVCS;

class DNMVCSExt
{
    use DNSingleton;
    use DNDI;
    
    const DEFAULT_OPTIONS_EX=[
    
            'use_function_view'=>false,
                'function_view_head'=>'view_header',
                'function_view_foot'=>'view_footer',
            'use_function_dispatch'=>false,
            'use_common_configer'=>false,
                'fullpath_project_share_common'=>'',
            'use_common_autoloader'=>false,
                'fullpath_config_common'=>'',
            'use_strict_db'=>false,
            
            'use_facades'=>false,
            'facades_namespace'=>'Facades',
            'facades_map'=>[],
            
            'use_session_auto_start'=>false,
            'session_auto_start_name'=>'DNSESSION',
            
            'mode_onefile'=>false,
            'mode_onefile_key_for_action'=>null,
            'mode_onefile_key_for_module'=>null,
            
            'mode_dir'=>false,
            'mode_dir_basepath'=>null,
            'mode_dir_index_file'=>'',
            'mode_dir_use_path_info'=>true,
            'mode_dir_key_for_module'=>true,
            'mode_dir_key_for_action'=>true,
            
            'use_db_reuse'=>false,
            'db_reuse_size'=>0,
            'db_reuse_timeout'=>5,
        ];
    public function init($dn)
    {
        $ext_options=$dn->options['ext'];
        
        $options=array_replace_recursive(static::DEFAULT_OPTIONS_EX, $ext_options);
        
        if ($options['use_common_autoloader']) {
            ProjectCommonAutoloader::G()->init($options)->run();
        }
        
        if ($options['use_common_configer']) {
            DNConfiger::G(ProjectCommonConfiger::G())->init($dn->options, $dn);
            $dn->is_dev=DNConfiger::G()->_Setting('is_dev')??$dn->isDev;
        }
        if ($options['use_function_view']) {
            DNView::G(FunctionView::G())->init($dn->options, $dn);
        }
        if ($options['use_strict_db']) {
            DNDBManager::G()->setBeforeGetDBHandler([static::G(),'checkDBPermission']);
        }
        
        if ($options['mode_onefile']) {
            RouteHookOneFileMode::G()->init($options['mode_onefile_key_for_action'], $options['mode_onefile_key_for_module']);
            DNRoute::G()->addRouteHook([RouteHookOneFileMode::G(),'hook']);
        }
        if ($options['mode_dir']) {
            RouteHookDirectoryMode::G()->init($options);
            DNRoute::G()->addRouteHook([RouteHookDirectoryMode::G(),'hook']);
        }
        
        if ($options['use_function_dispatch']) {
            DNRoute::G()->addRouteHook([FunctionDispatcher::G(),'hook']);
        }
        if ($options['use_session_auto_start']) {
            DNMVCS::session_start(['name'=>$options['session_auto_start_name']]);
        }
        
        if ($options['use_facades']) {
            $namespace=$dn->options['namespace']??'';
            FacadesAutoLoader::G()->init($options['facades_namespace'], $options['facades_map'], $namespace)->run();
        }
        if ($options['use_db_reuse']) {
            DBReusePoolProxy::G()->init($options['db_reuse_size'], $db_reuse_timeout=$options['db_reuse_timeout'], DNDBManager::G());
        }
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
}
//mysqldump -uroot -p123456 DnSample -d --opt --skip-dump-date --skip-comments | sed 's/ AUTO_INCREMENT=[0-9]*\b//g' >../data/database.sql
