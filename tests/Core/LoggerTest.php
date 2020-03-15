<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Core\Logger;
use DuckPhp\Core\App as DuckPhp;
class LoggerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Logger::class);
        
        $path_log=\GetClassTestPath(Logger::class);
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
        Logger::G()->reset()->init($options,DuckPhp::G());
        Logger::G()->emergency($message,  $context);
        $options=[
            'path'=>$path_log,
            'log_file'=>'log2.log',
            'log_prefix'=>'DuckPhpLog',
        ];
        Logger::G()->init($options);
        Logger::G()->alert($message,  $context);
        Logger::G()->critical($message,  $context);
        Logger::G()->error($message,  $context);
        Logger::G()->warning($message,  $context);
        Logger::G()->notice($message,  $context);
        Logger::G()->info($message,  $context);
        
        Logger::G()->debug($message,  $context);
        DuckPhp::Logger()->info("zzzzz");
        file_put_contents($path_log.'log.log','');// clear
        
        \MyCodeCoverage::G()->end(Logger::class);
        $this->assertTrue(true);
    }
}
