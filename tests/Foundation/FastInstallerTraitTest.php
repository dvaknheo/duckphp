<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\DuckPhp;
use DuckPhp\Ext\FastInstaller;
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
}
class FastInstallerTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(FastInstallerTrait::class);
        $options = [];
        FiApp::_()->init($options);
        FastInstaller::_(MyFastInstaller::_());
        FiApp::_()->command_require();
        FiApp::_()->command_remove();
        
        FiApp::_()->command_install();
        \LibCoverage\LibCoverage::End();
    }
}
