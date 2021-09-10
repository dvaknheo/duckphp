<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\DuckPhp;
use DuckPhp\Ext\Installer;
use DuckPhp\Ext\SqlDumper;
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
        ////
        @unlink($path_app.'config/tests__DuckPhp.installed');
        try{
            InstallerApp::Db()->execute('drop table `empty`');
        }catch(\Exception $ex){}
        ////
        
        InstallerApp::G()->isInstalled();
        try{
            InstallerApp::G()->checkInstall();
        }catch(\Exception $ex){}
        var_dump( InstallerApp::G()->install([]));
        InstallerApp::G()->checkInstall();
        @unlink($path_app.'config/tests__DuckPhp.installed');
        
        //@unlink($path_app.'config/tests__Data__Installer.lock');
        ////[[[[
        $namespace = InstallerApp::G()->options['namespace'];
        InstallerApp::G()->options['namespace'] ='';
        InstallerApp::G()->isInstalled();
        InstallerApp::G()->options['namespace'] = $namespace;
        
        
        
        //InstallerApp::G()->checkInstall();
        ////]]
        SqlDumper::G(FakeSqlDumper::G());
        
        Installer::G()->options['install_force']=true;
        Installer::G()->install();
        FakeSqlDumper::$exception_flag = true;
        try{
        Installer::G()->install();
        }catch(\Exception $ex){}
        @unlink($path_app.'config/tests__DuckPhp.installed');
        Installer::G()->dumpSql();
        \LibCoverage\LibCoverage::End();
    }
}
class InstallerApp extends DuckPhp
{
    use InstallableTrait;
}
class FakeSqlDumper extends SqlDumper
{
    public static $exception_flag = false;
    public function install()
    {
        if(static::$exception_flag){
            throw new \Exception("...");
        }
        return "???";
    }
    public function run()
    {
        return;
    }
}

