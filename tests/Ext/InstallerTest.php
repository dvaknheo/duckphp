<?php
namespace tests\DuckPhpExt;

use DuckPhp\DuckPhp;
use DuckPhp\Ext\Installer;
use DuckPhp\Ext\InstallableTrait;

class InstallerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Installer::class);

        $path_app = \LibCoverage\LibCoverage::G()->getClassTestPath(Installer::class);
        require_once($path_app.'Model/EmptyModel.php');
        InstallerApp::G()->init([
            'path' => $path_app,
        ]);
        @unlink($path_app.'config/tests__Data__Installer.lock');
        InstallerApp::G()->isInstalled();
        try{
            InstallerApp::G()->checkInstall();
        }catch(\Exception $ex){}
        InstallerApp::G()->install([]);
        
        @unlink($path_app.'config/tests__Data__Installer.lock');
        
        \LibCoverage\LibCoverage::End();
    }
}
class InstallerApp extends DuckPhp
{
    use InstallableTrait;
}

