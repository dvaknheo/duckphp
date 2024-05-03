<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\DuckPhp;
use DuckPhp\FastInstaller\FastInstaller;
use DuckPhp\Foundation\FastInstallerTrait;
class FiApp extends DuckPhp
{
    use FastInstallerTrait;
}
class MyFastInstaller extends FastInstaller
{
    public function doCommandInstall()
    {
        return;
    }
    public function doCommandRequire()
    {
        return;
    }
    public function doCommandUpdate()
    {
        return;
    }
    public function doCommandRemove()
    {
        return;
    }
}
class FastInstallerTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(FastInstallerTrait::class);
        $options = [
            'cli_command_class'=>FiApp::class,
        ];
        FiApp::_()->init($options);
        FastInstaller::_(MyFastInstaller::_());
        FiApp::_()->command_require();
        FiApp::_()->command_remove();
        FiApp::_()->command_install();
        FiApp::_()->command_update();
        \LibCoverage\LibCoverage::End();
    }
}
