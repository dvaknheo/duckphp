<?php
namespace tests\DuckPhp\FastInstaller;

use DuckPhp\Core\Console;
use DuckPhp\Component\CommandTrait;
use DuckPhp\DuckPhp;
use DuckPhp\FastInstaller\FastInstaller;
use DuckPhp\Foundation\FastInstallerTrait;
use DuckPhp\Db\Db;
use DuckPhp\Core\PhaseContainer;

class InstallerConsole extends Console
{

    public function readLines($options, $desc, $validators = [], $fp_in = null, $fp_out = null)
    {
        if(empty($this->data)){
            $fp_in = fopen('php://temp','r');
        }
        $fp_out = fopen('php://temp','w');
        $data = parent::readLines($options, $desc, [],$fp_in,$fp_out);
        fclose($fp_out);
        if(empty($this->data)){
            fclose($fp_in);
        }
        return $data;
    }
}
class FastInstallerTest extends \PHPUnit\Framework\TestCase
{
    private function makeFromDsn($options,$driver)
    {
        if (!isset($options['dsn'])) {
            return $options;
        }
        $dsn = $options['dsn'];
        $data = substr($dsn, strlen($driver)+1);
        $a = explode(';', trim($data, ';'));
        
        $t = array_map(function ($v) {
            return explode("=", $v);
        }, $a);
        $new = array_column($t, 1, 0);
        $new = array_map('trim', $new);
        $new = array_map('stripslashes', $new);
        $options = array_merge($options, $new);
        return $options;
    }
    
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(FastInstaller::class);
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(FastInstaller::class);
        $__SERVER = $_SERVER;
        
        var_dump($path_app);

        @mkdir($path_app);
        \LibCoverage\LibCoverage::G()->cleanDirectory($path_app);
        @mkdir($path_app);
        $path_setting = \LibCoverage\LibCoverage::G()->getClassTestPath(Db::class);
        $setting = include $path_setting . 'setting.php';
        $db = $this->makeFromDsn( $setting['database_list'][0], 'mysql');
        $rdb =  $setting['redis_list'][0];

        FiParentApp::_()->init([]);
$old_phase = FiParentApp::Phase();
        $_SERVER['argv']=['-','install', "--help", ];
        FiParentApp::_()->run();
        


        $_SERVER['argv']=['-','dumpsql'];
        FiParentApp::_()->run();
        
        
        $_SERVER['argv']=['-','update'];
        FiParentApp::_()->run();
        
        $_SERVER['argv']=['-','remove'];
        FiParentApp::_()->run();
        /////////////////////////////////////////

        $FastInstallerTest = str_replace('\\','/',FastInstallerTest::class);
        $_SERVER['argv']=['-','require',$FastInstallerTest];

        FiParentApp::_()->options['ext_options_file_enable']=false;
        FiParentApp::_()->run();

        FiParentApp::_()->options['ext_options_file_enable']=true;
        
        $old_phase = FiParentApp::Phase();
        $_SERVER['argv']=['-','FiChildApp:require', 'noexists'];
        FiParentApp::_()->run();


        FiParentApp::Phase($old_phase);
        
echo "--------------------------\n";

        $FiChildApp = str_replace('\\','/',FiChildApp::class);
        $_SERVER['argv']=['-','require',$FiChildApp , '--dry'];
        
        $console_options = Console::_()->options;
        Console::_(InstallerConsole::_(new InstallerConsole))->reInit($console_options, FiParentApp::_());
       
        FiParentApp::_()->run();
        $_SERVER['argv']=['-','require',$FiChildApp];
        $console_options = Console::_()->options;
        Console::_(InstallerConsole::_(new InstallerConsole))->reInit($console_options, FiParentApp::_());
        FiParentApp::_()->run();
        
        $_SERVER['argv']=['-','install','--force'];
        $console_options = Console::_()->options;
        Console::_(InstallerConsole::_(new InstallerConsole))->reInit($console_options, FiParentApp::_());
        FiParentApp::_()->run();
        
        $_SERVER['argv']=['-','require',$FiChildApp ,'--dry'];
        FiParentApp::_()->run();
        
        $FiChildApp2 = str_replace('\\','/',FiChildApp2::class);
        $_SERVER['argv']=['-','require',$FiChildApp2,'--dry'];
        FiParentApp::_()->options['ext_options_file_enable'] = false;
        FiParentApp::_()->run();
        FiParentApp::_()->options['ext_options_file_enable']=true;
        FiParentApp::_()->run();

        //////////////////////////////
PhaseContainer::RestAllContainerForTesting();

        @mkdir($path_app.'/public');
         @mkdir($path_app.'/res');
        @mkdir($path_app.'/res2');
        file_put_contents($path_app.'/res2/'.'abc.txt',DATE(DATE_ATOM));
        
        $FiChildAppRes = str_replace('\\','/',FiChildAppRes::class);

        $console_options = Console::_()->options;
        Console::_(InstallerConsole::_(new InstallerConsole))->reInit($console_options, FiParentApp::_());
        Console::_()->readLinesCleanFill();
        Console::_()->readLinesFill("t1\ny\n");
        $_SERVER['argv']=['-','require',$FiChildAppRes, '--force', '--verbose'];
        FiParentApp::_()->options['ext_options_file_enable']=true;
        FiParentApp::_()->run();

        Console::_()->readLinesCleanFill();
echo "->>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n";
        
        //Console::_()->readLinesFill("n\n");
        $_SERVER['argv']=['-','require',$FiChildAppRes, '--dry'];
        
        var_dump(FiParentApp::_()->options['app']);
        
        FiParentApp::_()->run();
        echo "--------------------------------\n";
        
        ///////////////
        $console_options = Console::_()->options;
        
        
PhaseContainer::RestAllContainerForTesting();
    //\DuckPhp\Core\App::$root_instance =null;
        
        
        FiParentApp::_()->init(['app'=>[
            FiChildApp::class => false,
            FiChildAppFailed::class => [
                    'cli_command_with_fast_installer'=>true,

                'no_empty'=>true,
                'controller_url_prefix'=>'ax',
                'install_input_desc'=>'install_input_desc_FiChildAppFailed',
            ],
            
        ]]);
                $console_options = Console::_()->options;

        $_SERVER['argv']=['-','install'];     
        Console::_(InstallerConsole::_(new InstallerConsole))->reInit($console_options, FiParentApp::_());
        
        FiParentApp::_()->run();
        Console::_()->readLinesCleanFill();
        Console::_(InstallerConsole::_(new InstallerConsole))->reInit($console_options, FiParentApp::_());
        $_SERVER['argv']=['-','install','--force','--verbose'];
        FiParentApp::_()->run();
        Console::_()->readLinesCleanFill();
        
        
        FastInstaller::_()->getCurrentInput();
        
        FastInstaller::_()->forceFail();
        FastInstaller::_()->command_install();
        ////
        Console::_()->readLinesCleanFill();
        Console::_()->readLinesFill("myres\n");
        FastInstaller::_()->command_dump_res();
        
        //echo "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx\n";
        
        
        $console_options = Console::_()->options;
        Console::_(InstallerConsole::_(new InstallerConsole))->reInit($console_options, FiParentApp::_());
        Console::_()->readLinesCleanFill();
        Console::_()->readLinesFill("N\n");
        FastInstaller::_()->doInstall();




//}
        $_SERVER = $__SERVER;
        
        //\LibCoverage\LibCoverage::G()->cleanDirectory($path_app);
        \LibCoverage\LibCoverage::End(); return;

    }
    protected function cleanData()
    {
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(FastInstaller::class);
        @unlink($path_app.'FiParent.config.php');
        @rmdir($path_app);
    }
}
class FiParentApp extends DuckPhp
{
    
    public $options = [
        'is_debug'=>true,
        'ext_options_file_enable'=> true,
        'name'=>'@',
       
        'app' => [
            FiChildApp::class => [
        'name'=>'@',

                'no_empty'=>true,
                'install_input_desc'=>'install_input_desc_FiChildApp',
            ]
        ],
        'cmd' => [FastInstaller::class =>true,],
        
        'install_callback' => [__CLASS__, 'OnInstall'],
    ];
    public function __construct()
    {
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(FastInstaller::class);
        $this->options['path'] = $path_app;
        @mkdir($path_app.'runtime');
        parent::__construct();
    }
    public static function OnInstall()
    {
        //hit
    }
    public function onInstalled()
    {
        //hit
    }
}
class FiChildApp extends DuckPhp
{
    public $options = [
        'name'=>'@',
        'im child' => true,
        'cli_command_with_fast_installer'=>true,
        'cmd' => [FastInstaller::class =>true,],
    ];
    public function __construct()
    {
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(FastInstaller::class);
        $this->options['path'] = $path_app;
        parent::__construct();
    }
}
class FiChildApp2 extends FiChildApp
{
    public $options = [
    'name'=>'@',
    ];
}
class FiChildAppRes extends FiChildApp
{
    public $options = [
    'name'=>'@',
    'path_resource' => 'res2',
    ];
}


class FiChildAppFailed extends FiChildApp
{
    public $options = [
        'name'=>'@',
        'im child' => true,
        'install_callback' => [__CLASS__, 'OnInstallX'],
        'cmd' => [FastInstaller::class =>true,],
    ];
    
    public $force_fail = true;
    public static function OnInstallX()
    {
        return static::_()->_OnInstall();
    }
    public function _OnInstall()
    {
        if ($this->force_fail){
            FastInstaller::_()->forceFail();
            throw new \Exception('force_fail');
        }
    }
}