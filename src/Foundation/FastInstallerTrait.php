<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\Ext\FastInstaller;

trait FastInstallerTrait
{
    /**
     * Install. power by DuckPhp\Foundation\FastInstallerTrait
     */
    public function command_install($force = false)
    {
        if (!FastInstaller::_()->isInited()) {
            FastInstaller::_()->init(App::Current()->options, App::Current());
        }
        return FastInstaller::_()->do_command_install($force);
        //SqlDumper::_()->init($this->options,$this)->run();
    }
    /**
     * Config. power by DuckPhp\Foundation\FastInstallerTrait
     */
    public function command_config($force = false)
    {
        if (!FastInstaller::_()->isInited()) {
            FastInstaller::_()->init(App::Current()->options, App::Current());
        }
        return FastInstaller::_()->do_commmand_config($force);
    }
    /**
     * Debug mode . --on, --off. power by DuckPhp\Foundation\FastInstallerTrait
     */
    public function command_debug($disable=false)
    {
        echo "TODO";
    }
}