<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Component\Console;
use DuckPhp\Component\DuckPhpCommand;
use DuckPhp\HttpServer\HttpServer;
use DuckPhp\Component\Installer;
use DuckPhp\DuckPhp as App;
use DuckPhp\Core\ComponentBase;

class DuckPhpCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(DuckPhpCommand::class);
        
        $_SERVER['argv']=[];
        App::G()->init([])->run();
        
        
        Console::G()->regCommandClass(DuckPhpCommand_Command::class,"test");
        $_SERVER['argv']=[
            '-','test:foo',
        ];
        App::G()->run();
        $_SERVER['argv']=[
            '-','test:foo','arg1','--pa',"--l","a","--az","a","b"
        ];
        App::G()->run();
        $_SERVER['argv']=[
            '-','test:foo','arg1','arg2','--pa',"--l","a","--az","a","b"
        ];
        App::G()->run();
        $_SERVER['argv']=[
            '-','test:foo2','arg1','arg2','--a1',"--a2","a","--a3","a","b"
        ];
        App::G()->run();

        $_SERVER['argv']=[
            '-','test:foo2','--a1=aaa',"--a2","a","--a3","a","b"
        ];
        App::G()->run();
        try{
            $_SERVER['argv']=[
                '-','test:foo3',
            ];
            App::G()->run();
        }catch(\Exception $ex){
            var_dump("Hit!");
        }
        try{
            $_SERVER['argv']=[
                '-','foo',
            ];
            App::G()->run();
        }catch(\Exception $ex){
            var_dump("Hit!");
        }
        try{
            $_SERVER['argv']=[
                '-','foo:foo',
            ];
            App::G()->run();
        }catch(\Exception $ex){
            var_dump("Hit!");
        }
        Installer::G(new Console_Installer());
        HttpServer::G(new Console_HttpServer());
        DuckPhpCommand::G()->command_new();
        DuckPhpCommand::G()->command_run();
        DuckPhpCommand::G()->command_help();
        DuckPhpCommand::G()->command_version();
        

        
        DuckPhpCommand::G()->command_list();
        
        DuckPhpCommand::G()->command_fetch();
        DuckPhpCommand::G()->command_routes();
        DuckPhpCommand::G()->command_depoly();
        DuckPhpCommand::G()->command_test();
        DuckPhpCommand::G(new DuckPhpCommand());
        //*/
        DuckPhpCommand_App::G()->init([]);
        //Console::G(new Console())->init([],DuckPhpCommand_App::G());
        $_SERVER['argv']=[
            '-','list',
        ];
        Console::G()->regCommandClass(DuckPhpCommand_Command::class,"aa");
        Console::G()->regCommandClass(DuckPhpCommand_Command2::class,"aa");
        DuckPhpCommand_App::G()->run();

        DuckPhpCommand_App::G(new DuckPhpCommand_App());
        DuckPhpCommand_App::G()->options['error_404']=function(){debug_print_backtrace(2);};
                $_SERVER['argv']=[
            '-','call',str_replace('\\','/',DuckPhpCommand_Command2::class).'@command_foo4','A1'
        ];
        DuckPhpCommand_App::G()->init([])->run();
        //////////////////
        
        DuckPhpCommand_App::G(new DuckPhpCommand_App());
        DuckPhpCommand_HttpServer::G(new DuckPhpCommand_HttpServer());
        App::G(new App());
        DuckPhpCommand_App::G()->options['error_404']=function(){debug_print_backtrace(2);};
        $_SERVER['argv']=[
            '-','run', '--http-server=tests/DuckPhp/Component/DuckPhpCommand_HttpServer',
        ];
        try{
        DuckPhpCommand_App::G()->init([])->run();
        }catch(\Throwable $ex){
            debug_print_backtrace(2);
        }
        //*/
        \LibCoverage\LibCoverage::End();
    }
}

class Console_Installer extends Installer
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

