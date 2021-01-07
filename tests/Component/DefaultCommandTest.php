<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Component\Console;
use DuckPhp\Component\DefaultCommand;
use DuckPhp\HttpServer\HttpServer;
use DuckPhp\Component\Installer;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;

class DefaultCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(DefaultCommand::class);
        
        $_SERVER['argv']=[];
        $options=[];
        Console::G()->init(['cli_enable'=>false],App::G());
        
        Console::G(new Console())->init($options,App::G());
        App::G()->run();
        
        Console::G()->init(['cli_enable'=>true,'cli_mode' => 'hook',],App::G());
        
        Console::G(new Console())->init($options,App::G());
        App::G()->run();
        
        Console::G()->init($options,App::G());
        Console::G()->getCliParameters();
        
        Console::G()->regCliCommandGroup(DefaultCommand_Command::class,"test");
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
        DefaultCommand::G()->command_new();
        DefaultCommand::G()->command_run();
        DefaultCommand::G()->command_help();
        DefaultCommand::G()->command_version();
        

        
        DefaultCommand::G()->command_list();
        
        DefaultCommand::G()->command_fetch();
        DefaultCommand::G()->command_routes();
        DefaultCommand::G()->command_depoly();
        DefaultCommand::G()->command_test();
        
        //*/
        Console::G(new Console())->init([],DefaultCommand_App::G());
        $_SERVER['argv']=[
            '-','list',
        ];
        Console::G()->regCliCommandGroup(DefaultCommand_Command::class,"aa");
        Console::G()->regCliCommandGroup(DefaultCommand_Command2::class,"aa");
        DefaultCommand_App::G()->run();
        $_SERVER['argv']=[
            '-','call',str_replace('\\','/',DefaultCommand_Command2::class).'@command_foo4','A1'
        ];
        DefaultCommand_App::G()->run();
        
        //*/
        \MyCodeCoverage::G()->end();
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

class DefaultCommand_App extends App
{
    /** overrid test*/
    public function command_test()
    {
        return true;
    }
}


class DefaultCommand_Command extends ComponentBase
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
class DefaultCommand_Command2 extends DefaultCommand_Command
{
    /**
     * desc2
    */
    public function command_foo4($a1)
    {
    
    }
}

