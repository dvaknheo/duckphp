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
        Runtime::_()->init(['use_output_buffer'=>true],App::_(new App())->init($options));
        Runtime::_()->isRunning();
        Runtime::_()->isInException();
        Runtime::_()->isOutputed();
        
        
        Runtime::_()->run();
        Runtime::_()->clear();

        Runtime::_()->onException(true);
        Runtime::_()->onException(false);
    $options = ['is_debug'=>false];
    Runtime::_()->init(['use_output_buffer'=>true],App::_(new App())->init($options));
Runtime::_()->run();
        Runtime::_()->clear();
        \LibCoverage\LibCoverage::End();

    }
}
