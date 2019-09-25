<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\SuperGlobal;

class SuperGlobalTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(SuperGlobal::class);
        
        //code here
        SuperGlobal::G()->init();
        SuperGlobal::G()->_GLOBALS('k', 'v');

        \MyCodeCoverage::G()->end();
        $this->assertTrue(true);
        /*
        SuperGlobal::G()->__construct();
        SuperGlobal::G()->init();
        SuperGlobal::G()->session_start(array $options=[]);
        SuperGlobal::G()->session_id($session_id);
        SuperGlobal::G()->session_destroy();
        SuperGlobal::G()->session_set_save_handler($handler);
        SuperGlobal::G()->_GLOBALS($k, $v=null);
        SuperGlobal::G()->_STATICS($name, $value=null, $parent=0);
        SuperGlobal::G()->_CLASS_STATICS($class_name, $var_name);
        //*/
    }
}
