<?php
namespace tests\DuckPhp\FastInstaller;

use DuckPhp\Core\Console;
use DuckPhp\DuckPhp;
use DuckPhp\FastInstaller\FastInstaller;
use DuckPhp\Foundation\FastInstallerTrait;
use DuckPhp\Db\Db;

class InstallerConsole extends Console
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
        @mkdir($path_app);
        \LibCoverage\LibCoverage::G()->cleanDirectory($path_app);
        @mkdir($path_app);
        $path_setting = \LibCoverage\LibCoverage::G()->getClassTestPath(Db::class);
        $setting = include $path_setting . 'setting.php';
        $db = $this->makeFromDsn( $setting['database_list'][0], 'mysql');
        $rdb =  $setting['redis_list'][0];
        $__SERVER['argv'] =$_SERVER['argv'];
        
        FiParentApp::_()->init([]);
        
        
        $_SERVER['argv']=['-','install', "--help", ];
        FiParentApp::_()->run();
        $_SERVER['argv']=['-','install', "--dump-sql", ];
        FiParentApp::_()->run();
        
        
        $console_options = Console::_()->options;
        Console::_(InstallerConsole::_(new InstallerConsole))->reInit($console_options, FiParentApp::_());
        
        $str= "{$db['host']}\n{$db['port']}\n{$db['dbname']}\n{$db['username']}\n{$db['password']}\n";
        $str2= "{$rdb['host']}\n{$rdb['port']}\n{$rdb['auth']}\n{$rdb['select']}\n";
        InstallerConsole::_()->setFileContents([$str,  'N',$str2,'N']);
        
        $_SERVER['argv']=['-','install', "--configure", ];
        FiParentApp::_()->run();
        FiParentApp::_()->run();
        //*
        echo "------------------------";
        
        
        $console_options = Console::_()->options;
        Console::_(InstallerConsole::_(new InstallerConsole))->reInit($console_options, FiParentApp2::_(new FiParentApp2()));
        $str= "{$db['host']}\n{$db['port']}\n{$db['dbname']}\n{$db['username']}\n{$db['password']}\n";
        $str2= "{$rdb['host']}\n{$rdb['port']}\n{$rdb['auth']}\n{$rdb['select']}\n";
        InstallerConsole::_()->setFileContents([$str,  'N',$str2,'N']);
        $_SERVER['argv']=['-','install', "--configure", '--force'];
        FiParentApp2::_()->run();
        
        echo "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx\n";
        
        try {
        $console_options = Console::_()->options;
        Console::_(InstallerConsole::_(new InstallerConsole))->reInit($console_options, FiParentApp::_(new FiParentApp()));
        $str= "{$db['host']}\n{$db['port']}\n{$db['dbname']}\n{$db['username']}\n{$db['password']}\n";
        $str2= "{$rdb['host']}\n{$rdb['port']}\n{$rdb['auth']}\n{$rdb['select']}\n";
        InstallerConsole::_()->setFileContents([$str,  'N',$str2,'N']);
        
        $_SERVER['argv']=['-','install', ];
        FiParentApp::_()->run();
        }catch(\Exception $ex){
            var_dump("???");exit;
        }
        
        $_SERVER['argv'] = $__SERVER['argv'];
        //----------------------
        //*/
        //$_SERVER['argv']=['-','install', "--dump-sql", ];
        //FiParentApp::_()->run();
        \LibCoverage\LibCoverage::End();
        
    }
}
class FiParentApp extends DuckPhp
{
    use FastInstallerTrait;
    
    public $options = [
        'is_debug'=>true,
        'ext_options_file'=>'FiParent.config.php',
        'app' => [
            FiChildApp::class => [
                'no_empty'=>true,
            ]
        ],
    ];
    public function __construct()
    {
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(FastInstaller::class);
        $this->options['path'] = $path_app;
        parent::__construct();
    }
}
class FiChildApp extends DuckPhp
{
    use FastInstallerTrait;
    
    public $options = [
        'im child' => true,
        'install_need_redis'=>true,
    ];
    public function __construct()
    {
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(FastInstaller::class);
        $this->options['path'] = $path_app;
        parent::__construct();
    }
}
class FiParentApp2 extends FiParentApp
{
    use FastInstallerTrait;
    
    public $options = [
        'is_debug'=>true,
        'ext_options_file'=>'FiParent2.config.php',
        'app' => [
            FiChildApp2::class => [
                'no_empty'=>true,
            ]
        ],
        'install_need_database'=>false,
    ];
}
class FiChildApp2 extends FiChildApp
{
    public $options = [
        'im child' => true,
        'install_need_database'=>false,
        'install_need_redis'=>false,
    ];
}