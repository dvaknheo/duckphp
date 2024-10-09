<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Ext\DuckPhpInstaller;
use DuckPhp\Core\App;
use DuckPhp\Core\Console;
use DuckPhp\HttpServer\HttpServer;
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
class DuckPhpInstallerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(DuckPhpInstaller::class);
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhpInstaller::class);
        $path_init = $path;
        \LibCoverage\LibCoverage::G()->cleanDirectory($path_init);
        $__SERVER = $_SERVER;
        $_SERVER['argv']=[];
        
        $time = date('Y-m-d_H_i_s');
        $path = $path . $time . 'test';
        mkdir($path);
        Console::_(InstallerConsole::_());
        $options=[
            'is_debug'=>true,
            'path'=>$path,
            'verbose'=>true,
        ];
        $_SERVER['argv']=[];
        DuckPhpInstaller::_()->command_help();
        
        $_SERVER['argv']=[
            '-','run', '--http-server=tests/DuckPhp/Ext/Console_HttpServer',
        ];
        DuckPhpInstaller::_()->command_show();
        
        $_SERVER['argv']=[
            '-','new','--help',
        ];
        DuckPhpInstaller::_()->command_new();
        
        $_SERVER['argv']=[
            '-','new','--verbose','--path='.$path,
        ];
        $str= "Abcde\n";
        Console::_()->readLinesCleanFill();
        Console::_()->readLinesFill($str);
        DuckPhpInstaller::_()->command_new();
        Console::_()->readLinesCleanFill();
        Console::_()->readLinesFill($str);
        DuckPhpInstaller::_()->command_new();
        Console::_()->readLinesCleanFill();
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        /*
        DuckPhpInstaller::RunQuickly(['help'=>true,]);
        DuckPhpInstaller::_(new DuckPhpInstaller());
        DuckPhpInstaller::RunQuickly($options);
        DuckPhpInstaller::RunQuickly($options);
        $options['force']=true;
        $options['namespace']='zz';
        $options['verbose']=false;
        DuckPhpInstaller::_(new DuckPhpInstaller());
        DuckPhpInstaller::RunQuickly($options);
        */
        $_SERVER = $__SERVER;
        \LibCoverage\LibCoverage::G()->cleanDirectory($path_init);
        \LibCoverage\LibCoverage::End();
    }

}
class Console_HttpServer extends HttpServer
{
    public function run()
    {
        return true;
    }
}