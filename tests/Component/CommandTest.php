<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Component\Command;

use DuckPhp\Core\Console;
use DuckPhp\Ext\AutoReadLineConsole;
use DuckPhp\Core\ComponentBase;

use DuckPhp\HttpServer\HttpServer;
use DuckPhp\DuckPhp;

class tAutoReadLineConsole extends Console
{
    public $file_index=99999;
    public $datas = [];
    public function setFileContents($datas)
    {
        $this->datas =$datas;
        $this->file_index = 0;
    }
    public function readLines($options, $desc, $validators = [], $fp_in = null, $fp_out = null)
    {
        if($fp_in){
            return parent::readLines($options, $desc, $validators, $fp_in, $fp_out);
        }
        $str = $this->datas[$this->file_index];
        $fp_in = fopen('php://memory','r+');
        fputs($fp_in, $str);
        fseek($fp_in,0);
        $fp_out = fopen('php://temp','w');
        $ret = parent::readLines($options, $desc, $validators, $fp_in, $fp_out);
        $this->file_index++;
        fclose($fp_out);
        fclose($fp_in);
        
        return $ret;
    }
    
}

class CommandTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Command::class);
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);
        $__SERVER = $_SERVER;
        $_SERVER['argv']=[];

        DuckPhp::_()->init([
            'is_debug'=>true,
            'cli_enable'=>true,
            'path'=>$path_app,
            'ext_options_file' => 'Command.config.php',
            'app' =>[
                CommandApp2::class =>['bc'=>'tre'],
                CommandApp::class =>['a'=>'tre'],
            ],
        ])->run();
        
        $_SERVER['argv']=[
            '-','version',
        ];
        DuckPhp::_()->run();
        
        /*
        $_SERVER['argv']=[
            '-','new',
        ];
        $options = Console::_()->options;
        Console::_(tAutoReadLineConsole::_())->reInit($options,DuckPhp::_());
        DuckPhpInstaller::_(Console_Installer::_());
        $str= "Xns\n";
        tAutoReadLineConsole::_()->setFileContents([$str]);
        DuckPhp::_()->run();
        */
        
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
        
        $_SERVER['argv']=[
            '-','aa:new2',
        ];
        DuckPhp::_()->run();
        /*
        $_SERVER['argv']=[
            '-','new', '--namespace'
        ];
        DuckPhp::_()->run();
        $_SERVER['argv']=[
            '-','new'
        ];
        DuckPhp::_()->run();
        */
        @unlink($path_app.'Command.config.php');
        
        $_SERVER = $__SERVER;
        \LibCoverage\LibCoverage::End();return;
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
    public function command_new(){}
    public function command_help(){}
    public function command_run(){}
}
class Console_Command2
{
    public function prefix_new2(){}
}
class CommandApp extends DuckPhp
{
    public $options=[
        'cli_command_prefix' =>'aa',
        'cli_command_classes'=>[Console_Command::class,[Console_Command2::class,'prefix_']],
    ];
}
class CommandApp2 extends DuckPhp
{
    public $options=[
        //'cli_command_class'=>null,
    ];
    
}


