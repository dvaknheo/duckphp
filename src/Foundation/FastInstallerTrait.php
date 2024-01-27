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
    public function command_install()
    {
        if (!FastInstaller::_()->isInited()) {
            FastInstaller::_()->init($this->options, $this);
        }
        return FastInstaller::_()->doCommandInstall();
    }
    /**
     * Debug mode . --on, --off. power by DuckPhp\Foundation\FastInstallerTrait
     */
    public function command_debug()
    {
        if (!FastInstaller::_()->isInited()) {
            FastInstaller::_()->init($this->options, $this);
        }
        FastInstaller::_()->doDebug();
    }
}