<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Component\Installer;
use DuckPhp\Core\App;

class InstallerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Installer::class);
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(Installer::class);
        \LibCoverage\LibCoverage::G()->cleanDirectory($path);
        
        $time = date('Y-m-d_H_i_s');
        $path = $path . $time . 'test';
        mkdir($path);
        
        $options=[
            'is_debug'=>true,
            'path'=>$path,
            'verbose'=>true,
        ];
        Installer::RunQuickly(['help'=>true,]);
        Installer::G(new Installer());
        Installer::RunQuickly($options);
        Installer::RunQuickly($options);
        $options['force']=true;
        $options['namespace']='zz';
        $options['verbose']=false;
        Installer::G(new Installer());
        Installer::RunQuickly($options);
        
        \LibCoverage\LibCoverage::End();
    }

}
