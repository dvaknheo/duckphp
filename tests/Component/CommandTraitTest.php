<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Component\Command;
use DuckPhp\Component\CommandTrait;
use DuckPhp\Component\DuckPhpInstaller;

use DuckPhp\Core\Console;
use DuckPhp\Core\ComponentBase;

use DuckPhp\HttpServer\HttpServer;
use DuckPhp\DuckPhp;


class CommandTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(CommandTrait::class);
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);
        $_SERVER['argv']=[];
        DuckPhp::_()->init(['cli_enable'=>true,'path'=>$path_app,
             'ext_options_file' => 'CommandTrait.config.php',
        ])->run();
        
        $_SERVER['argv']=[
            '-','version',
        ];
        DuckPhp::_()->run();
        
        $_SERVER['argv']=[
            '-','new',
        ];
        DuckPhpInstaller::_(Console_Installer::_());
        DuckPhp::_()->run();
        
        $_SERVER['argv']=[
            '-','run', '--http-server=tests/DuckPhp/Component/Console_HttpServer',
        ];
        DuckPhp::_()->run();
        
        $_SERVER['argv']=[
            '-','routes',
        ];
        DuckPhp::_()->run();
        
        $_SERVER['argv']=[
            '-','call',str_replace('\\','/',Console_Command::class).'@command_foo4','A1'
        ];
        DuckPhp::_()->run();
        
        $_SERVER['argv']=[
            '-','fetch', '--uri=/'
        ];
        DuckPhp::_()->run();
        DuckPhp::_()->options['cli_enable']=true;
        
        $_SERVER['argv']=[
            '-','debug',
        ];
        DuckPhp::_()->run();
        $_SERVER['argv']=[
            '-','debug', '--off'
        ];
        DuckPhp::_()->run();
        
        
        
        @unlink($path_app.'CommandTrait.config.php');
        \LibCoverage\LibCoverage::End();return;
    }
}

class Console_Installer extends DuckPhpInstaller
{
    public function run()
    {
        return true;
    }
}
class Console_HttpServer extends HttpServer
{
    public function run()
    {
        return true;
    }
}
class Console_Command
{
    /**
     * desc2
    */
    public function command_foo4($a1)
    {
    
    }
}

