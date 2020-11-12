<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Ext\Installer;
use DuckPhp\Core\App;

class InstallerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Installer::class);
        $path=\MyCodeCoverage::GetClassTestPath(Installer::class);
        $options=[
            'is_debug'=>true,
            'path'=>$path,
        ];
        Installer::RunQuickly(['help'=>true,]);
        Installer::G(new Installer());
        Installer::RunQuickly($options);
        
        \MyCodeCoverage::G()->end();
    }
}
