<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\RuntimeState;

class RuntimeStateTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RuntimeState::class);
        
        RuntimeState::G()->isRunning();
        RuntimeState::G()->ReCreateInstance();
        RuntimeState::G()->begin();
        RuntimeState::G()->end();
        RuntimeState::G()->skipNoticeError();
        
        //code here
        
        \MyCodeCoverage::G()->end(RuntimeState::class);
        $this->assertTrue(true);
        /*
        RuntimeState::G()->isRunning();
        RuntimeState::G()->ReCreateInstance();
        RuntimeState::G()->begin();
        RuntimeState::G()->end();
        RuntimeState::G()->skipNoticeError();
        //*/
    }
}
