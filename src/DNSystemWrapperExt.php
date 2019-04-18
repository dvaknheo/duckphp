<?php
namespace DNMVCS;

use DNMVCS\DNSingleton;

class DNSystemWrapperExt
{
    use DNSingleton;
    const DEFAULT_OPTIONS=[
        'mode_dir_index_file'=>'',
        'mode_dir_use_path_info'=>true,
        'mode_dir_key_for_module'=>true,
        'mode_dir_key_for_action'=>true,
    ];
    public function init($options=[], $context=null)
    {
        if (!defined('DNMVCS_SYSTEM_WRAPPER_INSTALLER')) {
            return;
        }
        $callback=DNMVCS_SYSTEM_WRAPPER_INSTALLER;
        $funcs=($callback)();
        
        $context->system_wrapper_replace($funcs);
        
        if (isset($funcs['set_exception_handler'])) {
            $class=get_class($this);
            $class::set_exception_handler([$class,'OnException']); //install excpetion again;
        }
    }
    public function onBeforeRun()
    {
        if ($this->options['use_super_global']??false || defined('DNMVCS_SUPER_GLOBAL_REPALACER')) {
            if (defined('DNMVCS_SUPER_GLOBAL_REPALACER')) {
                $func=DNMVCS_SUPER_GLOBAL_REPALACER;
                DNSuperGlobal::G($func());
            }
            $this->addDynamicClass(DNSuperGlobal::class);
            DNRoute::G()->bindServerData(DNSuperGlobal::G()->_SERVER);
        }
    }
}
