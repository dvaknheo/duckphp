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
     * override me to add a child app.
     */
    public function command_require()
    {
        echo "Todo: to add a child app autoly.\n";
    }
    /**
     * override me to remove a child app.
     */
    public function command_remove()
    {
        echo "Todo: to remove a child app autoly.\n";
    }
}
