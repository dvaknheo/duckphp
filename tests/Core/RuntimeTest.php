<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\Runtime;
use DuckPhp\Core\App;

class RuntimeTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Runtime::class);
        
        $options = ['is_debug'=>true];
        Runtime::G()->init(['use_output_buffer'=>true],App::G(new App())->init($options));
        Runtime::G()->isRunning();
        Runtime::G()->isInException();
        Runtime::G()->isOutputed();
        
        
        Runtime::G()->run();
        Runtime::G()->clear();

        Runtime::G()->onException(true);
        Runtime::G()->onException(false);
////////////////

    $str ="<b>{abc}</b>";
    $args = ["abc"=>"def"];
    $data = ["abc"=>"def"];
    $var = $data;
    $message = $str; $context =$data;
    Runtime::G()->_IsAjax();
    echo Runtime::G()->_L($str, $args);
    echo Runtime::G()->_L($str);
    echo Runtime::G()->_Hl($str, $args);
    echo Runtime::G()->_Json($data);
    echo Runtime::G()->_H($str);
    $t = [$str,"zz"];
    echo Runtime::G()->_H($t);
    $t = 123;
    echo Runtime::G()->_H($t);
    
    Runtime::G()->_TraceDump();
    Runtime::G()->_VarLog($var);
    Runtime::G()->_DebugLog($message, $context);
    Runtime::G()->_var_dump($args);
    
    $sql = "select * from users";
    echo Runtime::G()->_SqlForPager($sql, 1, 10);
    echo Runtime::G()->_SqlForCountSimply($sql);
    
    $options = ['is_debug'=>false];
    Runtime::G()->init(['use_output_buffer'=>true],App::G(new App())->init($options));
    Runtime::G()->_TraceDump();
    Runtime::G()->_VarLog($var);
    Runtime::G()->_DebugLog($message, $context);
    Runtime::G()->_var_dump($args);
Runtime::G()->run();
        Runtime::G()->clear();
////////////////
        \LibCoverage\LibCoverage::End();

    }
}
