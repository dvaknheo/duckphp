<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\StrictCheck;
use DNMVCS\DNMVCS;

class StrictCheckTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(StrictCheck::class);
        
        
        $options=[

            'platform' => 'BJ',
            'is_debug' => true,
            'skip_setting_file' => true,
            'reload_for_flags' => false,
            'error_exception' => NULL,
            'error_500' => NULL,
            'error_404' => NULL,
            'error_debug' => NULL,
            'skip_view_notice_error' => true,
            'use_super_global' => true,
        ];
        $options['ext']=[
            'noclass'=>true,
        ];
        DNMVCS::G()->init($options);
        
                StrictCheck::G()->init([], DNMVCS::G());
                $trace_level=1;
                $parent_class=StrictCheck::class;
                try{
StrictCheck::G()->checkStrictParentCaller($trace_level, $parent_class);
        }catch(\Throwable $ex){
        }
        \MyCodeCoverage::G()->end(StrictCheck::class);
        $this->assertTrue(true);
        /*
        StrictCheck::G()->init($options=[], $context=null);
        StrictCheck::G()->initContext($options=[], $context=null);
        StrictCheck::G()->getCallerByLevel($level);
        StrictCheck::G()->checkEnv();
        StrictCheck::G()->checkStrictComponent($component_name, $trace_level);
        StrictCheck::G()->checkStrictModel($trace_level);
        StrictCheck::G()->checkStrictService($trace_level);
        StrictCheck::G()->checkStrictParentCaller($trace_level, $parent_class);
        //*/
    }
}
