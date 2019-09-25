<?php
namespace tests\DNMVCS\Helper;

use DNMVCS\Helper\ModelHelper;
use DNMVCS\DNMVCS;

class ModelHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ModelHelper::class);
        
        $options=[
            'skip_setting_file'=>true,
        ];
        DNMVCS::G()->init($options);
        try {
        ModelHelper::DB($tag=null);
        } catch(\Throwable $ex) {
        }
        try {
        ModelHelper::DB_W();
        } catch(\Throwable $ex) {
        }
        try {
        ModelHelper::DB_R();
        } catch(\Throwable $ex) {
        }
        
        \MyCodeCoverage::G()->end(ModelHelper::class);
        $this->assertTrue(true);

    }
}
