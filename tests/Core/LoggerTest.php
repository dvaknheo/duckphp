<?php 
namespace tests\DuckPhp\Core;
use DuckPhp\Core\Logger;
use DuckPhp\DuckPhp;
class LoggerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Logger::class);
        
        $path_log=\LibCoverage\LibCoverage::G()->getClassTestPath(Logger::class);
        \LibCoverage\LibCoverage::G()->cleanDirectory($path_log);
        

        $options=[
            'path_log' => $path_log,
            'log_prefix'=>'DuckPhpLog',
        ];
        $message='test{a}';
        $context=['a'=>'b'];
        
        $dn_options=[
            'path' => $path_log,
            'path_log' => $path_log,
            'log_prefix' => 'AppOptionsPrefix',
            'log_file_template' => 'appprobe_%Y.log',
        ];
        DuckPhp::_()->init($dn_options);
        
        // App 选项要真的传进 Logger：曾用 EXT_SKIP_INIT 装配（只 ::_() 取实例、不 init），
        // 于是应用选项里的 path_log/log_prefix/log_file_template 全被忽略
        $this->assertSame('AppOptionsPrefix', Logger::_()->options['log_prefix']);
        $this->assertSame('appprobe_%Y.log', Logger::_()->options['log_file_template']);
        $this->assertSame($path_log, Logger::_()->options['path_log']);
        
        Logger::_()->init($options,DuckPhp::_());
        
        Logger::_()->emergency($message,  $context);
        $options=[
            'path'=>$path_log,
            'log_file'=>'log2.log',
            'log_prefix'=>'DuckPhpLog',
        ];
        Logger::_()->init($options);
        Logger::_()->alert($message,  $context);
        Logger::_()->critical($message,  $context);
        Logger::_()->error($message,  $context);
        Logger::_()->warning($message,  $context);
        Logger::_()->notice($message,  $context);
        Logger::_()->info($message,  $context);
        
        Logger::_()->debug($message,  $context);
        //////////
        
        $options=[];
        $options['log_file_template']=$path_log.'x.log';
        Logger::_(new Logger())->init($options)->info($message,  $context);
        
        $options=[];
        $options['path']=$path_log;
        $options['path_log']='./';
        Logger::_(new Logger())->init($options)->info($message,  $context);
         $options=[];
        $options['path']=$path_log;
        $options['path_log']=$path_log;
        Logger::_(new Logger())->init($options)->info($message,  $context);
        \LibCoverage\LibCoverage::G()->cleanDirectory($path_log);
        \LibCoverage\LibCoverage::End();
    }
}
