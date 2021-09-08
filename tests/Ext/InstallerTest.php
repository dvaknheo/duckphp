<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Ext\Installer;
use tests_Data_Installer\System\App;

class InstallerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Installer::class);

        $path_app = \LibCoverage\LibCoverage::G()->getClassTestPath(Installer::class);
        require_once($path_app.'System/App.php');
        require_once($path_app.'Model/EmptyModel.php');
        App::G()->init([
            'path' => $path_app,
        ]);
        @unlink($path_app.'config/tests__Data__Installer.lock');
        App::G()->isInstalled();
        try{
        App::G()->checkInstall();
        }catch(\Exception $ex){}
        App::G()->install([]);
        
        @unlink($path_app.'config/tests__Data__Installer.lock');
        
        \LibCoverage\LibCoverage::End();
    }
}
