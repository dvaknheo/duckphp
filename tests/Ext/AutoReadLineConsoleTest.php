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
        Console::_()->fill(["myname\n"]);
        
        $desc= <<<EOT
name:[{name}]

EOT;
        $options =[
            'name'=>'default',
        ];
        $data = Console::_()->readLines($options, $desc, []);
        
        Console::_()->toggleLog($flag = true);
        Console::_()->cleanFill();
        Console::_()->fill(["myname\n"]);
        $data = Console::_()->readLines($options, $desc, []);
        Console::_()->getLog();
        
        ////[[[[
        $desc = <<<EOT
input host and port
host[{host}]
port[{port}]
areyousure[{ok}]

done;

EOT;
        $options=[
            //'host'=>'127.0.0.1',
            'port'=>'80',
        ];
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(Console::class);
        $input = fopen($path.'input.txt','r');
        $output = fopen($path.'output.txt','w');
        
        $ret=Console::_()->readLines($options,$desc,[],$input,$output);
        fclose($input);
        fclose($output);
        
        @unlink($path.'output.txt');
        ////]]]]
        
        $_SERVER = $__SERVER;
        \LibCoverage\LibCoverage::End(); return;

    }
}
