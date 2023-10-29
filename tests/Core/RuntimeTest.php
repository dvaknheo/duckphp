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
    $options = ['is_debug'=>false];
    Runtime::G()->init(['use_output_buffer'=>true],App::G(new App())->init($options));
Runtime::G()->run();
        Runtime::G()->clear();
        \LibCoverage\LibCoverage::End();

    }
}
