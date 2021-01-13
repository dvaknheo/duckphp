<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\RuntimeState;

class RuntimeStateTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RuntimeState::class);
        
        RuntimeState::G()->init(['use_output_buffer'=>true]);
        RuntimeState::G()->isRunning();
        RuntimeState::G()->reset();
        RuntimeState::G()->clear();
        
        RuntimeState::G()->toggleInException();
        RuntimeState::G()->isInException();
        RuntimeState::G()->isOutputed();
        RuntimeState::G()->toggleOutputed();
        
        RuntimeState::G()->isInited();

        \MyCodeCoverage::G()->end();

    }
}
