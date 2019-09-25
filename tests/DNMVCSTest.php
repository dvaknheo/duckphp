<?php
namespace tests\DNMVCS;

use DNMVCS\DNMVCS;

class DNMVCSTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(DNMVCS::class);
        
        //code here
        //$handler=null;
        //DNMVCS::G()->addBeforeRunHandler($handler);
        DNMVCS::G()->getStaticComponentClasses();
        DNMVCS::G()->getDynamicComponentClasses();
        
        \MyCodeCoverage::G()->end(DNMVCS::class);
        $this->assertTrue(true);
        /*
        DNMVCS::G()->onSwooleHttpdInit($SwooleHttpd, $inCoroutine=false);
        DNMVCS::G()->addBeforeRunHandler($handler);
        DNMVCS::G()->getStaticComponentClasses();
        DNMVCS::G()->getDynamicComponentClasses();
        DNMVCS::G()->DB($tag=null);
        DNMVCS::G()->DB_W();
        DNMVCS::G()->DB_R();
        DNMVCS::G()->setDBHandler($db_create_handler, $db_close_handler=null, $db_excption_handler=null);
        DNMVCS::G()->Pager();
        DNMVCS::G()->assignRewrite($key, $value=null);
        DNMVCS::G()->getRewrites();
        DNMVCS::G()->assignRoute($key, $value=null);
        DNMVCS::G()->getRoutes();
        DNMVCS::G()->CheckStrictDB();
        DNMVCS::G()->checkStrictComponent($component_name, $trace_level);
        DNMVCS::G()->checkStrictService($trace_level=2);
        DNMVCS::G()->checkStrictModel($trace_level=2);
        DNMVCS::G()->Import($file);
        DNMVCS::G()->RecordsetUrl($data, $cols_map=[]);
        DNMVCS::G()->RecordsetH($data, $cols=[]);
        DNMVCS::G()->callAPI($class, $method, $input);
        DNMVCS::G()->MapToService($serviceClass, $input);
        DNMVCS::G()->explodeService($object, $namespace="MY\\Service\\");
        //*/
    }
}
