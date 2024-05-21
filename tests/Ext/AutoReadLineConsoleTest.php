<?php
namespace tests\DuckPhp\FastInstaller;

use DuckPhp\Ext\AutoReadLineConsole;
use DuckPhp\Core\Console;

class AutoReadLineConsoleTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(AutoReadLineConsole::class);
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(FastInstaller::class);
        $__SERVER = $_SERVER;
    
    
        Console::_(AutoReadLineConsole::_());
        Console::_()->autoFill(["myname\n"]);
        $desc= <<<EOT
name:[{name}]

EOT;
        $options =[
            'name'=>'default',
        ];
        $data = Console::_()->readLines($options, $desc, []);
        $_SERVER = $__SERVER;
        \LibCoverage\LibCoverage::End(); return;

    }
}
