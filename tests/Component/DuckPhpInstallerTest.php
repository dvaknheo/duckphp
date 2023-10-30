<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Component\DuckPhpInstaller;
use DuckPhp\Core\App;

class DuckPhpInstallerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(DuckPhpInstaller::class);
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhpInstaller::class);
        $path_init = $path;
        \LibCoverage\LibCoverage::G()->cleanDirectory($path_init);
        
        $time = date('Y-m-d_H_i_s');
        $path = $path . $time . 'test';
        mkdir($path);
        
        $options=[
            'is_debug'=>true,
            'path'=>$path,
            'verbose'=>true,
        ];
        DuckPhpInstaller::RunQuickly(['help'=>true,]);
        DuckPhpInstaller::_(new DuckPhpInstaller());
        DuckPhpInstaller::RunQuickly($options);
        DuckPhpInstaller::RunQuickly($options);
        $options['force']=true;
        $options['namespace']='zz';
        $options['verbose']=false;
        DuckPhpInstaller::_(new DuckPhpInstaller());
        DuckPhpInstaller::RunQuickly($options);
        \LibCoverage\LibCoverage::G()->cleanDirectory($path_init);
        \LibCoverage\LibCoverage::End();
    }

}
