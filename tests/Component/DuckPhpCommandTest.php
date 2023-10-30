<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Core\Console;
use DuckPhp\Component\DuckPhpCommand;
use DuckPhp\HttpServer\HttpServer;
use DuckPhp\Component\DuckPhpInstaller;
use DuckPhp\DuckPhp as App;
use DuckPhp\Core\ComponentBase;

class DuckPhpCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(DuckPhpCommand::class);
        
        $_SERVER['argv']=[];
        App::_()->init(['console_enable'=>true])->run();
        
        
        Console::_()->regCommandClass(DuckPhpCommand_Command::class,"test");
        $_SERVER['argv']=[
            '-','test:foo',
        ];
        App::_()->run();
        $_SERVER['argv']=[
            '-','test:foo','arg1','--pa',"--l","a","--az","a","b"
        ];
        App::_()->run();
        $_SERVER['argv']=[
            '-','test:foo','arg1','arg2','--pa',"--l","a","--az","a","b"
        ];
        App::_()->run();
        $_SERVER['argv']=[
            '-','test:foo2','arg1','arg2','--a1',"--a2","a","--a3","a","b"
        ];
        App::_()->run();

        $_SERVER['argv']=[
            '-','test:foo2','--a1=aaa',"--a2","a","--a3","a","b"
        ];
        App::_()->run();
        try{
            $_SERVER['argv']=[
                '-','test:foo3',
            ];
            App::_()->run();
        }catch(\Exception $ex){
            var_dump("Hit!");
        }
        try{
            $_SERVER['argv']=[
                '-','foo',
            ];
            App::_()->run();
        }catch(\Exception $ex){
            var_dump("Hit!");
        }
        try{
            $_SERVER['argv']=[
                '-','foo:foo',
            ];
            App::_()->run();
        }catch(\Exception $ex){
            var_dump("Hit!");
        }
        DuckPhpInstaller::_(new Console_Installer());
        HttpServer::_(new Console_HttpServer());
        DuckPhpCommand::_()->command_new();
        DuckPhpCommand::_()->command_run();
        DuckPhpCommand::_()->command_help();
        DuckPhpCommand::_()->command_version();
        

        
        DuckPhpCommand::_()->command_list();
        
        DuckPhpCommand::_()->command_fetch();
        DuckPhpCommand::_()->command_routes();
        DuckPhpCommand::_()->command_depoly();
        DuckPhpCommand::_()->command_test();
        DuckPhpCommand::_(new DuckPhpCommand());
        //*/
        DuckPhpCommand_App::_()->init([]);
        //Console::_(new Console())->init([],DuckPhpCommand_App::_());
        $_SERVER['argv']=[
            '-','list',
        ];
        Console::_()->regCommandClass(DuckPhpCommand_Command::class,"aa");
        Console::_()->regCommandClass(DuckPhpCommand_Command2::class,"aa");
        DuckPhpCommand_App::_()->run();

        DuckPhpCommand_App::_(new DuckPhpCommand_App());
        DuckPhpCommand_App::_()->options['error_404']=function(){debug_print_backtrace(2);};
                $_SERVER['argv']=[
            '-','call',str_replace('\\','/',DuckPhpCommand_Command2::class).'@command_foo4','A1'
        ];
        DuckPhpCommand_App::_()->init(['console_enable'=>true])->run();
        //////////////////
        
        DuckPhpCommand_App::_(new DuckPhpCommand_App());
        DuckPhpCommand_HttpServer::_(new DuckPhpCommand_HttpServer());
        App::_(new App());
        DuckPhpCommand_App::_()->options['error_404']=function(){debug_print_backtrace(2);};
        $_SERVER['argv']=[
            '-','run', '--http-server=tests/DuckPhp/Component/DuckPhpCommand_HttpServer',
        ];
        try{
        DuckPhpCommand_App::_()->init(['console_enable'=>true])->run();
        }catch(\Throwable $ex){
            debug_print_backtrace(2);
        }
        //*/
        \LibCoverage\LibCoverage::End();
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

class DuckPhpCommand_App extends App
{
    public $options =[
    ];
    /** overrid test*/
    public function command_test()
    {
        return true;
    }
}


class DuckPhpCommand_Command extends ComponentBase
{
    public function command_foo()
    {
    }
    /**
     * desc1
    */
    public function command_foo2($a1,$a2,$a3,$a4='aa')
    {
    
    }
    /**
     * desc2
    */
    public function command_foo3($a1)
    {
    
    }
}
class DuckPhpCommand_Command2 extends DuckPhpCommand_Command
{
    /**
     * desc2
    */
    public function command_foo4($a1)
    {
    
    }
}
class DuckPhpCommand_HttpServer extends HttpServer
{
    public function run()
    {
        var_dump("OK");
    }
}

