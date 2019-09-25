<?php
namespace tests\DNMVCS\Base;

use DNMVCS\Base\SessionedController;

class SessionedControllerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(SessionedController::class);
        
        new SessionedController();
        
        \MyCodeCoverage::G()->end(SessionedController::class);
        $this->assertTrue(true);
        /*
        SessionedController::G()->__construct();
        //*/
    }
}
