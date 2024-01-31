<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Core\Console;
use DuckPhp\Db\Db;
use DuckPhp\DuckPhp;
use DuckPhp\Ext\DatabaseInstaller;


class DbInstallerApp  extends DuckPhp
{
    public $options = [
        'console_enable'=>true,
        'ext'=> [
            DatabaseInstaller::class => true,
        ],
    ];
}
class MyDatabaseInstaller extends DatabaseInstaller
{
    
}
class DbInstallerConsole extends Console
{
    public $file_index=99999;
    
    public function readLines($options, $desc, $validators = [], $fp_in = null, $fp_out = null)
    {
        if(!$fp_in){
            $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(DatabaseInstaller::class);
            $file = $path_app."input_{$this->file_index}.txt";
            $fp_in = fopen($file,'r');
            $file = $path_app."output.txt";
            $fp_out = fopen($file,'w');
            $ret = parent::readLines($options, $desc, $validators, $fp_in, $fp_out);
            $this->file_index++;
            fclose($fp_in);
        }else{
            $ret = parent::readLines($options, $desc, $validators, $fp_in, $fp_out);
        }
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
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(DatabaseInstaller::class);
        
        $path_setting = \LibCoverage\LibCoverage::G()->getClassTestPath(Db::class);
        $setting = include $path_setting . 'setting.php';
        $db = $this->makeFromDsn( $setting['database_list'][0], 'mysql');
        
        ////[[[[
        @unlink($path_app.'DuckPhpApps.config.php');
        DbInstallerApp::_()->init([
            'path'=>$path_app,
            'ext_options_file' => 'DuckPhpApps.config.php',
        ]);
        $options = Console::_()->options;
        Console::_(DbInstallerConsole::_())->reInit($options,DbInstallerApp::_());
        
        $str= "{$db['host']}\n{$db['port']}\n{$db['dbname']}\n{$db['username']}\n{$db['password']}\n";
        file_put_contents($path_app."input_0.txt",$str);
        file_put_contents($path_app."input_1.txt",'N');
        
        DbInstallerConsole::_()->file_index=0;
        DatabaseInstaller::_()->callResetDatabase(false);
        DbInstallerConsole::_()->file_index=0;
        DatabaseInstaller::_()->callResetDatabase(false);
        
        DbInstallerConsole::_()->file_index=0;
        
        $str= "BAD{$db['host']}\n{$db['port']}\n{$db['dbname']}\n{$db['username']}\n{$db['password']}\n";
        file_put_contents($path_app."input_0.txt",$str);
        
        $str= "{$db['host']}\n{$db['port']}\n{$db['dbname']}\n{$db['username']}\n{$db['password']}\n";
        file_put_contents($path_app."input_1.txt",$str);
        file_put_contents($path_app."input_2.txt",'Y');
        file_put_contents($path_app."input_3.txt",$str);
        file_put_contents($path_app."input_4.txt",'N');

        DatabaseInstaller::_()->callResetDatabase(true);
        //*/
        ////]]]]
        //\LibCoverage\LibCoverage::G()->cleanDirectory($path_init);
        \LibCoverage\LibCoverage::End(); return;
    }
}
