<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace MY\Base;

use DuckPhp\App as SystemApp;

class App extends SystemApp
{
    //@override
    protected $options_project = [
        'error_404' => '_sys/error_404',
        'error_exception' => '_sys/error_exception',
        'error_debug' =>  '_sys/error_debug',
        
        'is_debug' => true, // @DUCKPHP_DELETE
        'skip_setting_file' => false, // @DUCKPHP_DELETE
    ];
    public function __construct()
    {
        parent::__construct();
        $options = [];
        /*
        $options['ext']['DuckPhp\\Ext\\CallableView'] = true;
            $options['callable_view_class']=NULL;
            $options['callable_view_foot']=NULL;
            $options['callable_view_head']=NULL;
            $options['callable_view_prefix']=NULL;
            $options['callable_view_skip_replace']=false;
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\DBReusePoolProxy'] = true;
            $options['before_get_db_handler']=NULL;
            $options['database_list']=NULL;
            $options['db_close_at_output']=true;
            $options['db_close_handler']=NULL;
            $options['db_create_handler']=NULL;
            $options['db_exception_handler']=NULL;
            $options['db_reuse_size']=100;
            $options['db_reuse_timeout']=5;
            $options['use_context_db_setting']=true;
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\FacadesAutoLoader'] = true;
            $options['facades_enable_autoload']=true;
            $options['facades_map']=array (
        );
            $options['facades_namespace']='Facades';
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\JsonRpcExt'] = true;
            $options['jsonrpc_backend']='https://127.0.0.1';
            $options['jsonrpc_check_token_handler']=NULL;
            $options['jsonrpc_enable_autoload']=true;
            $options['jsonrpc_is_debug']=false;
            $options['jsonrpc_namespace']='JsonRpc';
            $options['jsonrpc_service_interface']='';
            $options['jsonrpc_service_namespace']='';
            $options['jsonrpc_wrap_auto_adjust']=true;
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\Misc'] = true;
            $options['path']='';
            $options['path_lib']='lib';
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\PluginForSwooleHttpd'] = true;
            $options['swoole_ext_class']='SwooleHttpd\\SwooleExt';
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RedisManager'] = true;
            $options['enable_simple_cache']=true;
            $options['redis_list']=NULL;
            $options['simple_cache_prefix']='';
            $options['use_context_redis_setting']=true;
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RedisSimpleCache'] = true;
            $options['redis']=NULL;
            $options['redis_cache_prefix']='';
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RouteHookDirectoryMode'] = true;
            $options['mode_dir_basepath']='';
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RouteHookOneFileMode'] = true;
            $options['key_for_action']='_r';
            $options['key_for_module']='';
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RouteHookRewrite'] = true;
            $options['rewrite_map']=array (
        );
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\StrictCheck'] = true;
            $options['namespace_model']='';
            $options['namespace_service']='';
        //*/
        
        $this->options = array_replace_recursive($this->options, $options);
    }
    //@override
    protected function onPrepare()
    {
        var_dump($this->options);
    }
    //@override
    protected function onInit()
    {
        // your code here
    }
    //@override
    protected function onRun()
    {
        // your code here
    }
}
