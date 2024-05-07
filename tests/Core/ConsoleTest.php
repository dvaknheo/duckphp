<?php 
namespace tests\DuckPhp\Core;
use DuckPhp\Core\Console;
use DuckPhp\Component\DefaultCommand;
use DuckPhp\HttpServer\HttpServer;
use DuckPhp\Component\Installer;
use DuckPhp\DuckPhp as DuckPhp;
use DuckPhp\Core\ComponentBase;

class ConsoleTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Console::class);
        
        $_SERVER['argv']=[];
        DuckPhp::_()->init(['cli_enable'=>true, 'is_debug'=>true])->run();
        Console::DoRun();
        Console::_()->app();
        Console::_()->getCliParameters();
        Console::_()->regCommandClass('test',DuckPhp::class,[Console_Command::class]);
        //var_dump(Console::_()->options);exit;
        $_SERVER['argv']=[
            '-','test:foo',
        ];
        DuckPhp::_()->run();
        $_SERVER['argv']=[
            '-','test:foo','arg1','--pa',"--l","a","--az","a","b"
        ];
        DuckPhp::_()->run();
        $_SERVER['argv']=[
            '-','test:foo','arg1','arg2','--pa',"--l","a","--az","a","b"
        ];
        DuckPhp::_()->run();
        $_SERVER['argv']=[
            '-','test:foo2','arg1','arg2','--a1',"--a2","a","--a3","a","b"
        ];
        DuckPhp::_()->run();

        $_SERVER['argv']=[
            '-','test:foo2','--a1=aaa',"--a2","a","--a3","a","b"
        ];
        DuckPhp::_()->run();
        try{
            $_SERVER['argv']=[
                '-','test:foo3',
            ];
            DuckPhp::_()->options['skip_exception_check']=true;
            DuckPhp::_()->run();
        }catch(\Exception $ex){
            var_dump("Hit!");
            DuckPhp::_()->options['skip_exception_check']=false;
        }
        try{
            $_SERVER['argv']=[
                '-','foo',
            ];
            DuckPhp::_()->options['skip_exception_check']=true;
            DuckPhp::_()->run();
        }catch(\Exception $ex){
            var_dump("Hit!");
            DuckPhp::_()->options['skip_exception_check']=false;
        }
        try{
            $_SERVER['argv']=[
                '-','foo:foo',
            ];
            DuckPhp::_()->options['skip_exception_check']=true;
            DuckPhp::_()->run();
        }catch(\Exception $ex){
            var_dump("Hit!");
            DuckPhp::_()->options['skip_exception_check']=false;
        }
        
        
        //*/
        Console::_(new Console())->init([],Console_App::_());
        Console_App::_()->init(['cli_enable'=>true,'is_debug'=>true]);
        $_SERVER['argv']=[
            '-','help',  // ------>changed
        ];
        Console::_()->regCommandClass('', Console_App::class,[Console_Command2::class,[Console_Command4::class,'prefix_']]);
        Console_App::_()->run();
        $_SERVER['argv']=[
            '-','call',str_replace('\\','/',Console_Command2::class).'@command_foo4','A1'
        ];
        Console_App::_()->run();
        $_SERVER['argv']=[
            '-','callprefix'
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
class Console_App extends DuckPhp
{
    public $options=[
        'cli_command_class' => null,
    ];
    /** overrid test*/
    public function command_test()
    {
        return true;
    }
}


class Console_Command extends DuckPhp
{
    public $options=[
        'cli_command_namespace' => 'test',
        'cli_command_class' => null,
    ];
    public function command_foo()
    {
        var_dump("foo!");
    }
    /**
     * desc1
    */
    public function command_foo2($a1,$a2,$a3,$a4='aa')
    {
        var_dump(func_get_args());
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
class Console_Command4 extends Console_Command
{
    /**
     * desc2
    */
    public function prefix_callprefix()
    {
    
    }
}
class ConsoleParent extends Console
{

}