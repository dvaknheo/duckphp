<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\InstallableTrait;

use DuckPhp\DuckPhp;
use DuckPhp\Ext\Installer;

class InstallableTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(InstallableTrait::class);
        
        $path_app = \LibCoverage\LibCoverage::G()->getClassTestPath(InstallableTrait::class);
        InstallableApp::G()->init([
            'path' => $path_app,
        ]);
        @unlink($path_app.'config/tests-duck_php.lock');
        InstallableApp::G()->isInstalled();
        try{
            InstallableApp::G()->checkInstall();
        }catch(\Exception $ex){}
        InstallableApp::G()->install([]);
        
        
        ///////////////////////////////
        //InstallableApp::G()->checkInstall();
        
        
        try{
            InstallableApp::G()->options["tests-duck_php_installed"] = true;
        }catch(\Exception $ex){
        }
        InstallableApp::G()->options["tests-duck_php_installed"] = true;
        InstallableApp::G()->isInstalled();
        
        @unlink($path_app.'config/tests-duck_php.lock');
        
        \LibCoverage\LibCoverage::End();
    }
}


class InstallableApp extends DuckPhp
{
    use InstallableTrait;
}
