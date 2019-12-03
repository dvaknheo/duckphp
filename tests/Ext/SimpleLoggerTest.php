<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Ext\SimpleLogger;
use DuckPhp\App as DuckPhp;
class SimpleLoggerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(SimpleLogger::class);
        
        $path_log=\GetClassTestPath(SimpleLogger::class);
        $options=[
            'path'=>'',
            'log_file'=>$path_log.'log.log',
            'log_prefix'=>'DuckPhpLog',
        ];
        $message='test{a}';
        $context=['a'=>'b'];
        
        $dn_options=[
            'skip_setting_file'=>true,
        ];
        DuckPhp::G()->init($dn_options);
        SimpleLogger::G()->init($options,DuckPhp::G());
        
        SimpleLogger::G()->emergency($message,  $context);
         $options=[
            'path'=>$path_log,
            'log_file'=>'log2.log',
            'log_prefix'=>'DuckPhpLog',
        ];
        SimpleLogger::G()->init($options);
        SimpleLogger::G()->alert($message,  $context);
        SimpleLogger::G()->critical($message,  $context);
        SimpleLogger::G()->error($message,  $context);
        SimpleLogger::G()->warning($message,  $context);
        SimpleLogger::G()->notice($message,  $context);
        SimpleLogger::G()->info($message,  $context);
        
        SimpleLogger::G()->debug($message,  $context);
        DuckPhp::Logger()->info("zzzzz");
        file_put_contents($path_log.'log.log','');// clear

        \MyCodeCoverage::G()->end(SimpleLogger::class);
        $this->assertTrue(true);
    }
}
