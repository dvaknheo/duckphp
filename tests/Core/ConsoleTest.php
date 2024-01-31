<?php 
namespace tests\DuckPhp\Core;
use DuckPhp\Core\Console;
use DuckPhp\Component\DefaultCommand;
use DuckPhp\HttpServer\HttpServer;
use DuckPhp\Component\Installer;
use DuckPhp\DuckPhp as App;
use DuckPhp\Core\ComponentBase;

class ConsoleTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Console::class);
        
        $_SERVER['argv']=[];
        App::_()->init(['cli_enable'=>true, 'is_debug'=>true])->run();
        Console::DoRun();
        Console::_()->app();
        Console::_()->getCliParameters();
        
        
        Console::_()->regCommandClass(Console_Command::class,"test");
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
            App::_()->options['skip_exception_check']=true;
            App::_()->run();
        }catch(\Exception $ex){
            var_dump("Hit!");
            App::_()->options['skip_exception_check']=false;
        }
        try{
            $_SERVER['argv']=[
                '-','foo',
            ];
            App::_()->options['skip_exception_check']=true;
            App::_()->run();
        }catch(\Exception $ex){
            var_dump("Hit!");
            App::_()->options['skip_exception_check']=false;
        }
        try{
            $_SERVER['argv']=[
                '-','foo:foo',
            ];
            App::_()->options['skip_exception_check']=true;
            App::_()->run();
        }catch(\Exception $ex){
            var_dump("Hit!");
            App::_()->options['skip_exception_check']=false;
        }
        
        
        //*/
        Console::_(new Console())->init([],Console_App::_());
        Console_App::_()->init(['cli_enable'=>true,'is_debug'=>true]);
        $_SERVER['argv']=[
            '-','help',  // ------>changed
        ];
        Console::_()->regCommandClass(Console_Command::class,"aa");
        Console::_()->regCommandClass(Console_Command2::class,"aa");
        Console_App::_()->run();
        $_SERVER['argv']=[
            '-','call',str_replace('\\','/',Console_Command2::class).'@command_foo4','A1'
        ];
        Console_App::_()->run();
        
        
        try{
            $_SERVER['argv']=[
                '-','test',
            ];
           Console::_(new Console())->init(['cli_default_command_class'=>''])->run();
        }catch(\Exception $ex){
            var_dump("Hit!");
        }
        
        //*/
        
        $t=\LibCoverage\LibCoverage::G();
        define('__SINGLETONEX_REPALACER',ConsoleParent::class.'::CreateObject');
        \LibCoverage\LibCoverage::G($t);
        ConsoleParent::_()->isInited();
        echo "zzzzzzzzzzzzzzzzzzzzzzzz";
        
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
        
        $ret=ConsoleParent::_()->readLines($options,$desc,[],$input,$output);
        fclose($input);
        fclose($output);
        \LibCoverage\LibCoverage::End();
    }
}
class Console_App extends App
{
    /** overrid test*/
    public function command_test()
    {
        return true;
    }
}


class Console_Command extends App
{
    public function command_foo()
    {
        var_dump("foo!");
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
class Console_Command2 extends Console_Command
{
    /**
     * desc2
    */
    public function command_foo4($a1)
    {
    
    }
}
class ConsoleParent extends Console
{
    public static function CreateObject($class, $object)
    {
        static $_instance;
        $_instance=$_instance??[];
        $_instance[$class]=$object?:($_instance[$class]??($_instance[$class]??new $class));
        return $_instance[$class];
    }
}