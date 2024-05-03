<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\FastInstaller\FastInstaller;

trait FastInstallerTrait
{
    /**
     * Install. power by DuckPhp\Foundation\FastInstallerTrait
     */
    public function command_install()
    {
        //if (!FastInstaller::_()->isInited()) {
        //    FastInstaller::_()->init($this->options, App::Current());
        //}
        return FastInstaller::_()->doCommandInstall();
    }
    /**
     * override me to add a child app.
     */
    public function command_require()
    {
        return FastInstaller::_()->doCommandRequire();
    }
    /**
     * override me to update
     */
    public function command_update()
    {
        return FastInstaller::_()->doCommandUpdate();
    }
    /**
     * override me to remove a child app.
     */
    public function command_remove()
    {
        return FastInstaller::_()->doCommandRemove();
    }
}
