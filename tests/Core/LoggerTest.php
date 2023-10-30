<?php 
namespace tests\DuckPhp\Core;
use DuckPhp\Core\Logger;
use DuckPhp\Core\App as DuckPhp;
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
        ];
        DuckPhp::_()->init($dn_options);
        

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
        DuckPhp::Logger()->info("zzzzz");
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
        
        \LibCoverage\LibCoverage::End();
    }
}
