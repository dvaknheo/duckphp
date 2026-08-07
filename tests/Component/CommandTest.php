<?php 
namespace tests\DuckPhp\Business;

use DuckPhp\Foundation\SingletonTrait;

class CallTargetBusiness
{
    use SingletonTrait;
    public function foo()
    {
        echo 'CALLED_BUSINESS';
    }
}

namespace tests\DuckPhp\Component;
use DuckPhp\Component\Command;

use DuckPhp\Core\Console;
use DuckPhp\Ext\AutoReadLineConsole;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\SuperGlobal;

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
        @unlink($path_app.'log_file_template.log');
        DuckPhp::_()->init([
            'is_debug'=>true,
            'cli_enable'=>true,
            'path'=>$path_app,
            'app' =>[
                CommandApp2::class =>['bc'=>'tre', 'name'=>'@'],
                CommandApp::class =>['a'=>'tre', 'name'=>'@'],
            ],
            'log_file_template'=> 'CommandTest.log',
            'data_file_enable' => true,
            'data_file_bump_allow'=>true,
            'data_file_bump_keys' => ['is_debug'],
            'cmd' =>[
                
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
            '-','call',str_replace('\\','/',Console_Command::class).'@command_foo4','A1'
        ];
        DuckPhp::_()->run();
        // command_call 绝对类名分支：前导 \ → ltrim
        $_SERVER['argv']=[
            '-','call','\\'.str_replace('\\','/',Console_Command::class).'@command_foo4','A1'
        ];
        DuckPhp::_()->run();
        // command_call 相对分支成功路径：{namespace}\Business\{Class}::_()->foo()
        $old_namespace = DuckPhp::_()->options['namespace'];
        DuckPhp::_()->options['namespace'] = 'tests\\DuckPhp\\';
        $_SERVER['argv']=[
            '-','call','CallTargetBusiness@foo'
        ];
        DuckPhp::_()->run();
        DuckPhp::_()->options['namespace'] = $old_namespace;
        
        $_SERVER['argv']=[
            '-','fetch', '--uri=/'
        ];
        DuckPhp::_()->run();
        DuckPhp::_()->options['cli_enable']=true;
        //////////////////////
        $_SERVER['argv']=[
            '-','debug',
        ];
        DuckPhp::_()->run();
        $_SERVER['argv']=[
            '-','debug', '--off'
        ];
        DuckPhp::_()->run();
        DuckPhp::_()->options['data_file_enable'] = false;
        // debug 分支：data_file_enable=false → 进入 else 分支（line 116）
        $_SERVER['argv']=[
            '-','debug', '--off'
        ];
        DuckPhp::_()->run();
        
        $_SERVER['argv']=[
            '-','aa:new2',
        ];
        DuckPhp::_()->run();
        
        echo "------------------------------------\n";
        SuperGlobal::_()->reInit(['superglobal_auto_define'=>true],DuckPhp::_());
        SuperGlobal::_()->_SERVER['argv']=[
            '-','fetch', '--uri=/'
        ];
        DuckPhp::_()->run();
        
        
        DuckPhp::_()->options['cli_enable']=true;
        //////////////////////
        // getCommandsByClassReflection: @command_desc 注解优先，docComment 第一行回退
        $rm = new \ReflectionMethod(Command::class, 'getCommandsByClassReflection');
        if (PHP_VERSION_ID < 80100) {
            $rm->setAccessible(true);
        }
        $descs = $rm->invoke(Command::_(), new \ReflectionClass(Console_Command::class), 'command_');
        $this->assertSame('create new item', $descs['new']);   // @command_desc 优先
        $this->assertSame('desc2', $descs['foo4']);            // 无注解回退第一行
        $this->assertSame('', $descs['help']);                 // 无 doc 注释 → 空字符串
        $this->assertSame('run the server', $descs['run']);    // $(key|fallback) 无翻译 → fallback
        
        // 部分匹配：混排文本中的 $(key|fallback) 单独替换
        $rm2 = new \ReflectionMethod(Command::class, 'translateCommandDesc');
        if (PHP_VERSION_ID < 80100) {
            $rm2->setAccessible(true);
        }
        $cmd = Command::_();
        $this->assertSame('Use foo mode', $rm2->invoke($cmd, 'Use {{command.foo|foo}} mode'));
        $this->assertSame('no placeholder', $rm2->invoke($cmd, 'no placeholder'));
        $this->assertSame('command.foo', $rm2->invoke($cmd, '{{command.foo}}'));  // 无 fallback → 返回 key 本身
        
        // 多语言：设置 lang_handler 后 $(key|fallback) 走翻译
        $old_handler = DuckPhp::_()->options['lang_handler'] ?? null;
        DuckPhp::_()->options['lang_handler'] = function ($str, $args = []) {
            return $str === 'command.run_item' ? '运行服务' : $str;
        };
        $descs = $rm->invoke(Command::_(), new \ReflectionClass(Console_Command::class), 'command_');
        $this->assertSame('运行服务', $descs['run']);          // 翻译命中
        $this->assertSame('Use 运行服务 mode', $rm2->invoke($cmd, 'Use {{command.run_item|foo}} mode')); // 部分匹配翻译
        if ($old_handler === null) {
            unset(DuckPhp::_()->options['lang_handler']);
        } else {
            DuckPhp::_()->options['lang_handler'] = $old_handler;
        }
        //////////////////////
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
    /**
     * @command_desc create new item
     */
    public function command_new(){}
    public function command_help(){}
    /**
     * @command_desc {{command.run_item|run the server}}
     */
    public function command_run(){}
}
class Console_Command2
{
    public function prefix_new2(){}
}
class Console_Command3
{
    public function command_hello()
    {
        echo 'word';
    }
}

class CommandApp extends DuckPhp
{
    public $options=[
        'cli_command_prefix' =>'aa',
        'cmd'=>[
            Console_Command::class=>true,
            CommandApp2::class=>false,
            Console_Command2::class =>'prefix_',
        ],
    ];
}
class CommandApp2 extends DuckPhp
{
    public $options=[
        //'cli_command_class'=>null,
    ];
    
}


