<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\Runtime;

class RuntimeTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Runtime::class);
        
        Runtime::G()->init(['use_output_buffer'=>true]);
        Runtime::G()->isRunning();
        Runtime::G()->reset();
        Runtime::G()->clear();
        
        Runtime::G()->toggleInException();
        Runtime::G()->isInException();
        Runtime::G()->isOutputed();
        Runtime::G()->toggleOutputed();
        
        Runtime::G()->isInited();

        \LibCoverage\LibCoverage::End();

    }
}
