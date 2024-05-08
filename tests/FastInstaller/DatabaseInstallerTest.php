<?php
namespace tests\DuckPhp\FastInstaller;

use DuckPhp\Core\Console;
use DuckPhp\Db\Db;
use DuckPhp\DuckPhp;
use DuckPhp\FastInstaller\DatabaseInstaller;

class DbInstallerConsole extends Console
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
class DatabaseInstallerTest extends \PHPUnit\Framework\TestCase
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
        \LibCoverage\LibCoverage::Begin(DatabaseInstaller::class);
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);
        
        $path_setting = \LibCoverage\LibCoverage::G()->getClassTestPath(Db::class);
        $setting = include $path_setting . 'setting.php';
        $db = $this->makeFromDsn( $setting['database_list'][0], 'mysql');
        
        ////[[[[
        @unlink($path_app.'DatabaseInstallerApps.config.php');
        DuckPhp::_(new DuckPhp())->init([
            'path'=>$path_app,
            'ext_options_file' => 'DatabaseInstallerApps.config.php',
            'cli_enable'=>true,
            'ext'=> [
                DatabaseInstaller::class => true,
            ],
            'database_driver'=>'mysql',
        ]);
        $options = Console::_()->options;
        Console::_(DbInstallerConsole::_())->reInit($options,DuckPhp::_());
        
        $str= "{$db['host']}\n{$db['port']}\n{$db['dbname']}\n{$db['username']}\n{$db['password']}\n";
        DbInstallerConsole::_()->setFileContents([$str,  'N']);
        DatabaseInstaller::_()->install(false);
       
        DbInstallerConsole::_()->setFileContents([$str,  'N']);
        DatabaseInstaller::_()->install(false);
        
        //*
        $bstr= "BAD{$db['host']}\n{$db['port']}\n{$db['dbname']}\n{$db['username']}\n{$db['password']}\n";
        $str= "{$db['host']}\n{$db['port']}\n{$db['dbname']}\n{$db['username']}\n{$db['password']}\n";
        
        DbInstallerConsole::_()->setFileContents([$bstr, $str, 'Y',$str,'N']);
        DatabaseInstaller::_()->install(true);
        
        DuckPhp::_()->options['database_driver']='';
        DatabaseInstaller::_()->install(true);
        
        ////]]]]
        @unlink($path_app.'DatabaseInstallerApps.config.php');
        \LibCoverage\LibCoverage::End(); return;
    }
}
