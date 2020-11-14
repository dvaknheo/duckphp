<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Ext\Installer;
use DuckPhp\Core\App;

class InstallerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Installer::class);
        $path = \MyCodeCoverage::GetClassTestPath(Installer::class);
        $time = date('Y-m-d_H_i_s');
        $path = $path . 'test'.$time;
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
        
        \MyCodeCoverage::G()->end();
    }
}
