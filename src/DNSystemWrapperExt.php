<?php
namespace DNMVCS;

use DNMVCS\DNSingleton;

//TODO more compact

class DNSystemWrapperExt
{
    use DNSingleton;
    const DEFAULT_OPTIONS=[
        'use_super_global'=>false,
    ];
    public function init($options=[], $context=null)
    {
        $this->use_super_global=$options['use_super_global']??false;
        
        if (!defined('DNMVCS_SYSTEM_WRAPPER_INSTALLER')) {
            return;
        }
        $callback=DNMVCS_SYSTEM_WRAPPER_INSTALLER;
        $funcs=($callback)();
        
        DNMVCS::G()->system_wrapper_replace($funcs);
        
        if (isset($funcs['set_exception_handler'])) {
            DNMVCS::set_exception_handler([DNMVCS::class,'OnException']);
        }
    }
    public function onBeforeRun()
    {
        if ($this->use_super_global) {
            if (defined('DNMVCS_SUPER_GLOBAL_REPALACER')) {
                $func=DNMVCS_SUPER_GLOBAL_REPALACER;
                DNSuperGlobal::G($func());
            }
            DNMVCS::G()->addDynamicClass(DNSuperGlobal::class);
            DNRoute::G()->bindServerData(DNSuperGlobal::G()->_SERVER);
        }
    }
}
