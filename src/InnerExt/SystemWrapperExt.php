<?php
namespace DNMVCS\InnerExt;

use DNMVCS\Basic\SingletonEx;
use DNMVCS\Basic\SuperGlobal;
use DNMVCS\Core\App;

class SystemWrapperExt
{
    use SingletonEx;
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
        
        if ($context) {
            $context->system_wrapper_replace($funcs);
            if (isset($funcs['set_exception_handler'])) {
                $context::set_exception_handler([get_class($context),'OnException']);
            }
            $context->addBeforeRunHandler([static::class,'OnRun']);
        }
    }
    public static function OnRun()
    {
        return static::G()->run();
    }
    public function run()
    {
        if (defined('DNMVCS_SUPER_GLOBAL_REPALACER')) {
            $func=DNMVCS_SUPER_GLOBAL_REPALACER;
            SuperGlobal::G($func());
        }
        if (defined('DNMVCS_SUPER_GLOBAL_REPALACER') || $this->use_super_global) {
            App::G()->addDynamicComponentClass(SuperGlobal::class);
            App::G()->bindServerData(SuperGlobal::G()->_SERVER);
        }
    }
}
