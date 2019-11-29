<?php 
namespace tests\DNMVCS\Ext;
use DNMVCS\Ext\SimpleLogger;

class SimpleLoggerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(SimpleLogger::class);
        
        $path_log=\GetClassTestPath(SimpleLogger::class);
        $options=[
            'path'=>'',
            'log_file'=>$path_log.'log.log',
            'log_prefix'=>'DNMVCSLog',
        ];
        $message='test{a}';
        $context=['a'=>'b'];
        SimpleLogger::G()->init($options);
        
        SimpleLogger::G()->emergency($message,  $context);
         $options=[
            'path'=>$path_log,
            'log_file'=>'log2.log',
            'log_prefix'=>'DNMVCSLog',
        ];
        SimpleLogger::G()->init($options);
        SimpleLogger::G()->alert($message,  $context);
        SimpleLogger::G()->critical($message,  $context);
        SimpleLogger::G()->error($message,  $context);
        SimpleLogger::G()->warning($message,  $context);
        SimpleLogger::G()->notice($message,  $context);
        SimpleLogger::G()->info($message,  $context);
        
        SimpleLogger::G()->debug($message,  $context);
        
        file_put_contents($path_log.'log.log','');// clear

        \MyCodeCoverage::G()->end(SimpleLogger::class);
        $this->assertTrue(true);
    }
}
