<?php
namespace tests\DuckPhp\FastInstaller;

use DuckPhp\Core\Console;
use DuckPhp\Db\Db;
use DuckPhp\DuckPhp;
use DuckPhp\FastInstaller\RedisInstaller;

class RInstallerConsole extends Console
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
class RedisInstallerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RedisInstaller::class);
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);
        
        $path_setting = \LibCoverage\LibCoverage::G()->getClassTestPath(Db::class);
        $setting = include $path_setting . 'setting.php';
        $db =  $setting['redis_list'][0];
        
        ////[[[[
        @unlink($path_app.'RedisInstallerApps.config.php');
        DuckPhp::_(new DuckPhp())->init([
            'path'=>$path_app,
            'ext_options_file' => 'RedisInstallerApps.config.php',
            'cli_enable'=>true,
            'ext'=> [
                RedisInstaller::class => true,
            ],
            'use_redis'=>true,
        ]);
        $options = Console::_()->options;
        Console::_(RInstallerConsole::_())->reInit($options,DuckPhp::_());

        $str= "{$db['host']}\n{$db['port']}\n{$db['auth']}\n{$db['select']}\n";
        RInstallerConsole::_()->setFileContents([$str,  'N']);
        RedisInstaller::_()->install(false);
        RedisInstaller::_()->install(true); 
       
        RInstallerConsole::_()->setFileContents([$str,  'N']);
        RedisInstaller::_()->install(false);
        
        $bstr= "BAD{$db['host']}\n{$db['port']}\n{$db['dbname']}\n{$db['username']}\n{$db['password']}\n";
        $str= "{$db['host']}\n{$db['port']}\n{$db['dbname']}\n{$db['username']}\n{$db['password']}\n";
        
        RInstallerConsole::_()->setFileContents([$bstr, $str, 'Y',$str,'N']);
        RedisInstaller::_()->install(true);
        
        DuckPhp::Current()->options['use_redis']=false;
        RedisInstaller::_()->install(false);
        ////]]]]
        @unlink($path_app.'RedisInstallerApps.config.php');
        
        \LibCoverage\LibCoverage::End();
    }
}
