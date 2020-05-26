<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\RuntimeState;

class RuntimeStateTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RuntimeState::class);
        
        RuntimeState::G()->isRunning();
        RuntimeState::G()->ReCreateInstance()->init(['use_output_buffer'=>true])->run();
        RuntimeState::G()->clear();
        
        RuntimeState::G()->toggleInException();
        RuntimeState::G()->isInException();
        RuntimeState::G()->isOutputed();
        RuntimeState::G()->toggleOutputed();
        
        RuntimeState::G()->isInited();

        \MyCodeCoverage::G()->end();
        /*
        RuntimeState::G()->isRunning();
        RuntimeState::G()->ReCreateInstance();
        RuntimeState::G()->begin();
        RuntimeState::G()->end();
        RuntimeState::G()->skipNoticeError();
        //*/
    }
}
